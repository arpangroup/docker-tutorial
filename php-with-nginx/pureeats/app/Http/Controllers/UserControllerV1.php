<?php
namespace App\Http\Controllers;

use App\AcceptDelivery;
use App\Address;
use App\Email;
use App\EmailOtp;
use App\SmsOtp;
use App\User;
use App\LoginSession;
use App\UserRequest;
use AuthStatus;
use Carbon\Carbon;
use http\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Ixudra\Curl\Facades\Curl;
use JWTAuth;
use JWTAuthException;
use Spatie\Permission\Models\Role;
use App\Sms;
use App\PushToken;

use ErrorCode;
use App\Exceptions\AuthenticationException;
use App\Exceptions\ValidationException;
use Throwable;
use function Matrix\add;

class UserControllerV1 extends Controller
{


    /**
     * Return CODE_SEND response after validating the request
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws AuthenticationException
     */
    public function sendOTP(Request $request){
        // Step1: Check how many time request for login, if tried more then 5 times, automatically block the user for the day
        // think some better approach for the blocking logic
        $ip = $request->ip();
        Log::info("IP: ". $ip);
        if($this->isMaximumAttempt($ip)) {
            Log::error("Maximum attempt reached for " .$ip);
            throw new AuthenticationException(ErrorCode::MAXIMUM_ATTEMPT_REACH, "Maximum attempt reached, user is temporarily blocked");
        }


        $phone = $request->phone;
        $emailId = $request->email;

        if($phone == null && $emailId == null) throw new AuthenticationException(ErrorCode::INVALID_REQUEST_BODY, "Invalid request, phone or email should not be null");
        if($phone != null &&  strlen($phone) != 10) throw new AuthenticationException(ErrorCode::INVALID_REQUEST_BODY, "Invalid phone number, phone number must be of 10 digits");
        if($emailId != null &&  strlen($emailId) < 5) throw new AuthenticationException(ErrorCode::INVALID_REQUEST_BODY, "Invalid email");

        if ($phone != null){
            $user = User::where('phone', $phone)->get()->first();
        }elseif ($emailId != null){
            $user = User::where('email', $emailId)->get()->first();
        }else{
            Log::error("user is null");
            $user = null;
        }

        if($user != null){
            Log::info("user: ". $user->id);

             // Step2: Check user is blocked or not
            if($user->is_active == 0) {
                Log::error("User is blocked");
                throw new AuthenticationException(ErrorCode::ACCOUNT_BLOCKED, "User blocked");
            }


            // Step3: send the OTP
            Log::info("Sending OTP for login....");
            $otpResponse = $phone != null ? $this->sendPhoneOTP($phone) : $this->sendEmailOTP($emailId);
            //Log::info("OTP_RESPONSE: ", $otpResponse);

            return response()->json([
                'success' => true,
                'message' => "OTP send successfully to " . ($phone != null ? $phone : $emailId). ' for login',
                'next_link' => '/login-using-otp',
                'status' => AuthStatus::CODE_SEND,
                'code'=> '200',
            ]);

        }else{// user not exist, please register first
            // Step3: send the OTP
            Log::info("Sending OTP for registration....");
            $otpResponse = $phone != null ? $this->sendPhoneOTP($phone) : $this->sendEmailOTP($emailId);
            //Log::info("OTP_RESPONSE: ", $otpResponse);
            return response()->json([
                'success' => true,
                'message' => "OTP send successfully to " . ($phone != null ? $phone : $emailId) . ' for registration',
                'next_link' => '/register',
                'status' => AuthStatus::REGISTRATION_REQUIRED,
                'code'=> '200',
            ]);
        }

    }

    /**
     * Return AUTHENTICATED or VERIFIED
     * @param Request $request
     */
    public function verifyOTP(Request $request){//loginUsingOTP
        $phone = $request->phone;
        $emailId = $request->email;
        $otp = $request->otp;
        $pushToken = $request->push_token;
        $deviceInfo = $request->meta != null;


        if ($otp == null) throw new AuthenticationException(ErrorCode::INVALID_REQUEST_BODY, "Invalid request, otp should not be null");
        if($phone == null && $emailId == null) throw new AuthenticationException(ErrorCode::INVALID_REQUEST_BODY, "Invalid request, phone or email should not be null");
        if($phone != null &&  strlen($phone) != 10) throw new AuthenticationException(ErrorCode::INVALID_REQUEST_BODY, "Invalid phone number, phone number must be of 10 digits");
        if($emailId != null &&  strlen($emailId) < 5) throw new AuthenticationException(ErrorCode::INVALID_REQUEST_BODY, "Invalid email");

        if ($phone != null){// verify using phoneOTP
            $user = User::where('phone', $phone)->get()->first();
        }else if ($emailId != null){// verify using emailOTP
            $user = User::where('email', $emailId)->get()->first();
        }else{
            Log::error("user is null");
            $user = null;
        }


        if($user != null){
            Log::info("USER: ". $user->id);
            // Step1: validate user has proper role access or not
            if(!$user->hasRole('Customer')) {
                Log::error("user does not have customer role");
                throw new AuthenticationException(ErrorCode::BAD_REQUEST, "Invalid Role ");
            }

            // Step2: validate user is active or not
            if($user->is_active == 0) {
                Log::error("User is blocked");
                throw new AuthenticationException(ErrorCode::ACCOUNT_BLOCKED, "User blocked");
            }

            // Step3: validate the OTP
            Log::info('Calling VerifyOTP.....: ');
            $sms = new Sms();
            $email= new Email();
            $verifyResponse = $phone != null ? $sms->verifyOtp($phone, $otp) : $email->verifyOtp($emailId, $otp);
            if($verifyResponse['valid_otp'] != true) throw new AuthenticationException(ErrorCode::INVALID_OTP, "Invalid OTP ");

            $user->password = \Hash::make($request->otp);
            $user->save();

            Log::info('Saving push token......');
            $this->savePushToken($user->id, $request->push_token);

            Log::info("Saving device info......");
            $this->saveDeviceInfo($user->id, $deviceInfo);

            Log::info("Fetching default address of the user......");
            $defaultAddress = null;
            if ($user->default_address_id !== 0) {
                $defaultAddress = \App\Address::where('id', $user->default_address_id)->get()->first();
            }

            Log::info('Fetching running orders of the user.....');
            $running_orders = \App\Order::where('user_id', $user->id)
                //->whereIn('orderstatus_id', ['1', '2', '3', '4', '7', '8'])
                ->whereNotIn('orderstatus_id', ['5', '6'])
                ->with('orderitems', 'restaurant')
                ->get();

            $response = [
                'success' => true,
                'status' => AuthStatus::AUTHENTICATED,
                'message' => "login success",
                'next_link' => '/home',
                'code' => '200',
                'data' => [
                    'id' => $user->id,
                    'auth_token' => $user->auth_token,
                    'name' => $user->name,
                    'photo' => $user->photo,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at,
                    'phone' => $user->phone,
                    'default_address_id' => $user->default_address_id,
                    'default_address' => $defaultAddress,
                    //'delivery_pin' => $user->delivery_pin,
                    'wallet_balance' => $user->balanceFloat,
                    'running_orders' => $running_orders,
                ],
            ];
            return response()->json($response);

        }else{// user is null
            Log::info('user not found....checking whether OTP is valid or not');
            Log::info('although OTP is verified, but user is not available in the system. so registration required');

            // Step3: validate the OTP
            Log::info('Calling VerifyOTP.....: ');
            $sms = new Sms();
            $email= new Email();
            $verifyResponse = $phone != null ? $sms->verifyOtp($phone, $otp) : $email->verifyOtp($emailId, $otp);
            if($verifyResponse['valid_otp'] == true) {
                Log::info('OTP verified ==> return response as VERIFIED ==> user need to signUp/register');
                return response()->json([
                    'success' => true,
                    'status' => AuthStatus::VERIFIED,
                    'message' => "verification success, user need to register",
                    'code' => '200'
                ]);
            }else{
                Log::error('user not found for '.$request->phone);
                throw new AuthenticationException(ErrorCode::PHONE_NOT_EXIST, "user not found for " .$request->phone);
            }

        }


    }

//    private function getAuthProvider(Request $request) : string{
//        $provider = isset($request['auth_provider']) ? $request['auth_provider'] : null;
//        if ($provider === 'email' || $provider === 'phone'){
//            return $provider;
//        }else if($provider == null){
//            throw new ValidationException(ErrorCode::INVALID_AUTH_PROVIDER, "auth_provider is missing");
//        }else{
//            throw new ValidationException(ErrorCode::INVALID_AUTH_PROVIDER, "Invalid auth_provider " .$provider.", correct value should be email or phone");
//        }
//    }



    private function getToken($email, $password) {
        $token = null;
        //$credentials = $request->only('email', 'password');
        try {
            if (!$token = JWTAuth::attempt(['email' => $email, 'password' => $password])) {
                return response()->json([
                    'response' => 'error',
                    'message' => 'Password or email is invalid..',
                    'token' => $token,
                ]);
            }
        } catch (JWTAuthException $e) {
            return response()->json([
                'response' => 'error',
                'message' => 'Token creation failed',
            ]);
        }
        return $token;
    }

    public function register(Request $request){
        $otp = isset($request['otp']) ? $request['otp'] : null;
        $phone = isset($request['phone']) ? $request['phone'] : null;
        $email = isset($request['email']) ? $request['email'] : null;
        $name = isset($request['name']) ? $request['name'] : null;
        $referralCode = isset($request['referral_code']) ? $request['referral_code'] : null;
        $pushToken =  isset($request['pushToken']) ? $request['pushToken'] : null;
        $address = null;
        $password = "password";
        $assignableRole = "Customer";
        $deviceInfo = null;
        try{
            if ($request->default_address != null && $request->default_address['latitude'] != null && $request->default_address['longitude'] != null) {
                Log::info('ADDRESS: '.json_encode($request->default_address));
                $address = new Address();
                $address->latitude = $request->default_address['latitude'];
                $address->longitude = $request->default_address['longitude'];
                $address->address = isset($request->default_address['address']) ? $request->default_address['address'] : "" ;
                $address->house = isset($request->default_address['house']) ? $request->default_address['house'] : "";
                $address->tag = isset($request->default_address['tag']) ? $request->default_address['tag'] : "";
            }
        }catch (Throwable $th) {
            Log::error('ERROR inside register() during default address insertion: ' .json_decode($th->getMessage()));
            $address = null;
        }

        try {
            $deviceInfo = $request->meta != null ? json_encode($request->meta) : null;
        }catch (Throwable $th) {
            Log::error('ERROR inside register() during device_info parsing');
            $deviceInfo = null;
        }


        $this->validateRegistrationRequest($phone, $email, $name, $referralCode);

        $payload = [
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
            'password' => \Hash::make($password),
            'delivery_pin' => strtoupper(str_random(5)),
            'auth_token' => '',
        ];

        $checkEmail = User::where('email', $email)
            ->where('is_email_verified', '1')
            ->first();

        if ($checkEmail) {
            // current email is already exist and verified, means a valid user already exist
            // so no need to newly register, just return the user
            // ************************ Login Alert  ***************************************
            throw new ValidationException(ErrorCode::INVALID_REQUEST_BODY, "An account with this email already exist. Please login to verify your account");
        }

        // if already verified then dont send the verification OTP, directly register the user
        Log::info('checking id user is already verified or not');
        $isVerified = false;
        if ($otp != null){ //i.e, user might be verified
            Log::info('Calling VerifyOTP.....: ');
            $sms = new Sms();
            $emailSender= new Email();
            $verifyResponse = $phone != null ? $sms->verifyOtp($phone, $otp) : $emailSender->verifyOtp($email, $otp);
            if($verifyResponse['valid_otp'] != true) {// invalid OTP
                Log::info('invalid otp');
                throw new AuthenticationException(ErrorCode::INVALID_OTP, "Invalid OTP ".$otp);
            }else{// otp is valid
                $isVerified = true;
            }
        }
        try{
            $userObj = new User();
            $userObj->name = $name;
            $userObj->phone = $phone;
            $userObj->email = $email;
            $userObj->auth_token = '';
            $userObj->password = \Hash::make($password);
            // $userObj->is_active = $isVerified ? 1 : 0;
//
//            $isUserCreated = $user->save();
//            return $isUserCreated;
//            $user = new User($payload);
            if ($userObj->save()) {
                //return "SAVED";
                Log::info('Generating Token....');
                $token = self::getToken($email, $password);
                Log::info('TOKEN: ' .$token);
                if (!is_string($token)) {
                    //return response()->json(['success' => false, 'data' => 'Token generation failed'], 201);
                    Log::error('token generation failed');
                    throw new ValidationException(ErrorCode::TOKEN_GENERATION_FAILED, "Token generation failed");
                }

                Log::info('updating auth_token......');
                if ($phone != null){
                    $user = User::where('phone', $phone)->get()->first();
                }else{
                    $user = User::where('email', $request->phone)->get()->first();
                }
                $user->auth_token = $token; // update user token


                // Add address if address present
                Log::info('Check if user provide address in registration request');
                if ($address != null){
                    try{
                        Log::info('user provide address in registration request');
                        $address->user_id = $user->id;
                        Log::info('saving address....');
                        $address->save();
                        $address = Address::where('user_id', $user->id)->first();
                        Log::info('setting default_address_id as '.$address->id .' for user '.$user->id);
                        $user->default_address_id = $address->id;
                    }catch (Throwable $th){
                        Log::error('ERROR inside register() during default address insertion: ' .json_decode($th->getMessage()));
                    }
                }

                Log::info('Check if user provide referral_code in registration request');
                if ($referralCode != null){
                    Log::info('ReferralCode: '.$referralCode);
                    $user->referral_code = $referralCode;
                }

                Log::info('Check if user provide device_info in registration request');
                if ($deviceInfo != null){
                    Log::info('DeviceInfo: ');
                    $user->meta = json_encode($deviceInfo);

                    $loginSession =  LoginSession::where('user_id', $user->id)->get()->first();
                    if(!$loginSession){
                        $loginSession = new LoginSession();
                        $loginSession->user_id = $user->id;
                    }
                    $loginSession->login_at = Carbon::now();
                    $loginSession->mac_address = isset($request->meta['MAC']) ? $request->meta['MAC'] : null;
                    $loginSession->ip_address = isset($request->meta['wifiIP']) ? $request->meta['wifiIP'] : null;
                    $loginSession->manufacturer = isset($request->meta['manufacturer']) ? $request->meta['manufacturer'] : null;
                    $loginSession->model = isset($request->meta['model']) ? $request->meta['model'] : null;
                    $loginSession->sdk = isset($request->meta['sdk']) ? $request->meta['sdk'] : null;
                    $loginSession->brand = isset($request->meta['brand']) ? $request->meta['brand'] : null;
                    $loginSession->device_info = json_encode($deviceInfo);
                    $loginSession->save();
                }

                Log::info('Check if user provide fire_base push token in registration request');
                if($pushToken){
                    $this->savePushToken($user->id, $pushToken);
                    Log::info('Push Token Saved successfully');
                }

                Log::info('save the user......');
                $user->save();
                Log::info('Assigned Role: '.$assignableRole);
                $user->assignRole($assignableRole);


                Log::info('checking if user is already verified or not');
                if($isVerified){
                    return [
                        'success' => true,
                        'status' => AuthStatus::AUTHENTICATED,
                        'message' => "login success",
                        'next_link' => '/home',
                        'code' => '200',//success
                        'data' => [
                            'id' => $user->id,
                            'auth_token' => $token,
                            'name' => $user->name,
                            'email' => $user->email,
                            'phone' => $user->phone,
                            'default_address_id' => $user->default_address_id,
                            'default_address' => $address,
                            'delivery_pin' => $user->delivery_pin,
                            'wallet_balance' => $user->balanceFloat,
                        ],
                        'running_order' => null,
                    ];
                }else{// dont return the auth token and user details, as user is not verified
                    return [
                        'success' => true,
                        'status' =>  AuthStatus::CODE_SEND,
                        'message' => "registration success, please login first",
                        'next_link' => '/login-using-otp',
                        'code' => '201',//CREATED
                    ];
                }
                
            }else{
                throw new AuthenticationException(ErrorCode::REGISTRATION_NOT_POSSIBLE, "Registration not possible");
            }
        }catch (Throwable $th){
            Log::error('ERROR inside register()');
            //Log::error('ERROR: ' .json_decode($th->getMessage()));
            $response = ['success' => false, 'message' => 'Couldnt register user.'];
            //return response()->json($response, 400);
            return $th->getMessage();
        }
    }



    private function validateRegistrationRequest($phone, $email, $name = null, $referralCode=null){
        Log::info('Inside validateRegisterUser().........');
        if ($phone == null && $email == null) {
            Log::error('phone and email both are null');
            throw new ValidationException(ErrorCode::INVALID_REQUEST_BODY, "email or phone should not be null");
        }
        if($name == null){
            Log::error('name is null');
            throw new ValidationException(ErrorCode::INVALID_REQUEST_BODY, "name should not be null");
        }
        if(strlen($name) <3){
            Log::error('name is less then 3 character long');
            throw new ValidationException(ErrorCode::INVALID_REQUEST_BODY, "name should be minimum 3 characters long");
        }
        if(strlen($name) > 50){
            Log::error('name is more then 50 character long');
            throw new ValidationException(ErrorCode::INVALID_REQUEST_BODY, "name should be maximum 50 characters long");
        }

        // request can be come through Phone/Email VERIFIED request or Fresh RegistrationRequest
        // i.e., phone/email number is already verified with OTP, but the phone/email number is not registered,
        // in that case phone should not be null
        if ($phone != null){// request is already VERIFIED
            Log::error('phone is not null, so the request may already be VERIFIED');
            // this checking not required for VERIFIED request, but need to check for API testing
            // because in API testing user might be provide incorrect phone number
            if (strlen($phone) != 10){
                Log::error('phone number is not 10 digit length');
                throw new ValidationException(ErrorCode::INVALID_REQUEST_BODY, "Invalid phone number");
            }

            // check uniqueness of the phone number
            $checkPhone = User::where('phone', $phone)->first();
            if ($checkPhone){
                Log::alert('user trying to register with phone number, but the phone number' .$phone .' already exist');
                throw new ValidationException(ErrorCode::USER_EXIST, "An account with this phone number already exist. Please login to verify your account.");
            }
        }


        // Check referral code
        if($referralCode){
            // validate the referral code from referral code table
        }


        // validate email, email should never be null for any registration request
        if ($email == null){
            throw new ValidationException(ErrorCode::INVALID_REQUEST_BODY, "email should not be null");
        }else{
            // we will validate email in main code, because email is mandatory and need to be check for duplicate
            // if duplicate verified email found, then we might not not register the user, instead we may use the existing user for authenticating
            // Check email is unique or not
//            $checkEmail = User::where('email', $request->email)
//                ->where('is_email_verified', '1')
//                ->first();
        }

    }






    private function savePushToken($userId, $push_token){
        Log::info('savePushToken....');
        try{
            $pushToken = PushToken::where('user_id', $userId)->first();

            if ($pushToken) {
                //update the existing token
                $pushToken->token = $push_token;
                $pushToken->save();
            } else {
                //create new token for user
                $pushToken = new PushToken();
                $pushToken->token = $push_token;
                $pushToken->user_id = $userId;
                $pushToken->save();
            }
            //$success = $push_token;
            //return response()->json($success);
        }catch (Throwable $th){
            Log::error('ERROR inside savePushToken()');
            Log::error('ERROR: ' .json_decode($th->getMessage()));
        }
    }


    private function saveDeviceInfo($userId, $deviceInfo){
        Log::info('saveDeviceInfo....');
        try{
            if($deviceInfo != null){
                $loginSession =  LoginSession::where('user_id', $userId)->get()->first();
                if(!$loginSession){
                    $loginSession = new LoginSession();
                    $loginSession->user_id = $userId;
                }
                $loginSession->device_info = $deviceInfo;
                $loginSession->save();
            }
        }catch (Throwable $th) {
            Log::error('ERROR inside login() during meta record insertion');
            Log::error('ERROR: ' .json_decode($th->getMessage()));
        }
    }


    private function sendPhoneOTP($phone){
        Log::info("Sending SMS....");
        $sms = new Sms();
        return $sms->processSmsAction('OTP', $phone);
    }
    private function sendEmailOTP($emailId){
        Log::info("Sending email....");
        $email = new Email();
        return $email->processEmailAction('OTP', $emailId);
    }



    private function isMaximumAttempt($ip): bool
    {
        return false;
    }

};
