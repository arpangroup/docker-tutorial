<?php

namespace App;

use App\SmsOtp;
use App\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Ixudra\Curl\Facades\Curl;
use JWTAuth;
use JWTAuthException;
use Session;
use Twilio\Rest\Client;

class Email
{

    public function processEmailAction($actionType, $email, $otp = null, $message = null)
    {
        try {
            $response = $this->sendEmail($actionType, $email, $otp, $message);

        } catch (Exception $e) {
            $response = [
                'success' => false,
                'type' => 'EMAIL',
            ];
        }

        return $response;

    }

    /**
     * @param $actionType
     * @param $email
     * @param $otp
     * @param $message
     * @return mixed
     */
    private function sendEmail($actionType, $email, $otp, $message)
    {
        //$authkey = config('settings.msg91AuthKey');
        //$sender_id = config('settings.msg91SenderId');

        switch ($actionType) {

            case 'OTP':
                $otp = rand(11111, 99999);
                $message = config('settings.otpMessage') . ' ' . $otp;
                $this->saveOtp($email, $otp);
                break;

            case 'VERIFY':
                $response = $this->verifyOtp($email, $otp);
                return $response;
                break;

            case 'OD_NOTIFY':
                // Do Nothing Just Send
                break;

        }

        return [
            'success' =>true,
        ];

    }


    private function saveOtp($email, $otp){
        Log::info("#########ARPAN....: ".$email);
        Log::info("OTP: ".$otp);
        $otpTable = EmailOtp::where('email', $email)->first();

        if ($otpTable) {
            //email exists, just update the otp
            $otpTable->otp = $otp;
            $otpTable->save();
            # code...
        } else {
            //create new entry
            $otpTable = new EmailOtp();
            $otpTable->email = $email;
            $otpTable->otp = $otp;
            $otpTable->save();
        }
        // dd($phone);
    }


    public function verifyOtp($email, $otp)
    {
        // dd($otp);
        $otpTable = EmailOtp::where('email', $email)->first();

        if ($otpTable) {
            if ($otpTable->otp == $otp) {

                $response = [
                    'valid_otp' => true,
                ];

            } else {
                $response = [
                    'valid_otp' => false,
                ];
            }
        } else {
            $response = [
                'valid_otp' => false,
            ];

        }
        return $response;
    }

}
