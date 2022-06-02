<?php

namespace App\Http\Controllers;

use App\Addon;
use App\AddonCategory;
use App\Item;
use App\ItemCategory;
use App\Order;
use App\AcceptDelivery;
use App\PushNotify;
use App\Restaurant;
use App\RestaurantEarning;
use App\RestaurantPayout;
use App\Sms;
use App\User;
use Auth;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Image;
use Ixudra\Curl\Facades\Curl;
use JWTAuth;
use JWTAuthException;
use Spatie\Permission\Models\Role;
use App\PushToken;
use App\Coupon;

use Illuminate\Support\Facades\Log;

class RestaurantOwnerApiController extends Controller
{



    private function getCouponAmount($coupon, $orderAmount){
        $discountAmount = 0;
        if ($coupon) {
            $discountAmount = $coupon->discount;

            if ($coupon->discount_type == 'PERCENTAGE') {
                $discountAmount =  ($orderAmount * $coupon->discount) / 100;
            }
            if ($coupon->discount_type == 'AMOUNT') {
                $discountAmount = $coupon->discount;
            }

            $uptoAmount = $coupon->upto_amount;
            if($discountAmount > $uptoAmount) {
                $discountAmount = $uptoAmount;
            }
        }
        return $discountAmount;

    }

    private function getItemTotal($order){
        $orderTotal = 0;

        foreach($order->orderitems as $item){
            $orderTotal += ($item->price * $item->quantity);
        }
        return $orderTotal;

    }


    
    /**
     * @param $user_id
     * @param $push_token
     */
    private function saveToken($user_id, $push_token)
    {
        $pushToken = PushToken::where('user_id', $user_id)->first();

        if ($pushToken) {
            //update the existing token
            $pushToken->token = $push_token;
            $pushToken->save();
        } else {
            //create new token for user
            $pushToken = new PushToken();
            $pushToken->token = $push_token;
            $pushToken->user_id = $user_id;
            $pushToken->save();
        }
        $success = $push_token;
        return response()->json($success);
    }

    
    /**
     * @param $phone
     * @param $password
     * @return mixed
     */
    private function getTokenFromPhoneAndPassword($phone, $password)
    {
        $token = null;
        //$credentials = $request->only('email', 'password');
        try {
            if (!$token = JWTAuth::attempt(['phone' => $phone, 'password' => $password])) {
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


    public function loginUsingOtp(Request $request){
        Log::info('#############################################################');
        Log::info($request->path());
        Log::info(json_encode($request->headers->all()));
        Log::info('#############################################################');
        $phone = $request->phone;
        $otp = $request->otp;
        $pushToken = null;
        if($request->push_token) $pushToken = $request->push_token;
        

        if($phone == null || $otp == null){            
            $response = [
                'success' => false,
                "message" => "Invalid request"
            ];
            return response()->json($response);
        }else{
            //  First check phone number is valid  or not
            $user = \App\User::where('phone', $phone)->get()->first();
            if ($user && $user->hasRole('Store Owner')) {
                $sms = new Sms();
                $verifyResponse = $sms->verifyOtp($phone, $otp);
                
                $restaurants = $user->restaurants;
                if($restaurants){

                    if($verifyResponse['valid_otp'] == true){            
                        $user->password = \Hash::make($otp);
                        $user->save();   
    
                        
                        if($pushToken != null){
                            $this->saveToken($user->id, $pushToken);
                        }
    
                        $token = self::getTokenFromPhoneAndPassword($phone, $otp);
                        $user->auth_token = $token;  
                        // if (isset($request->address['latitude'])) {
                        //     $address = new Address();
                        //     $address->user_id = $user->id;
                        //     $address->latitude = $request->address['latitude'];
                        //     $address->longitude = $request->address['longitude'];
                        //     $address->address = $request->address['address'];
                        //     $address->house = $request->address['house'];
                        //     $address->tag = $request->address['tag'];
                        //     $address->save();
                        //     $user->default_address_id = $address->id;
                        // }
                        $user->save();
                        if ($user->default_address_id !== 0) {
                           //$default_address = \App\Address::where('id', $user->default_address_id)->get(['address', 'house', 'latitude', 'longitude', 'tag'])->first();
                            $default_address = \App\Address::where('id', $user->default_address_id)->get()->first();
                        } else {
                            $default_address = null;
                        }
    
                        $running_order = null;
    
                                        
                        $response = [
                            'success' => true,
                            'message' => "login success",
                            'data' => [
                                'id' => $user->id,
                                'auth_token' => $user->auth_token,
                                'push_token' => $pushToken,
                                'name' => $user->name,
                                'photo' => $user->photo,
                                'email' => $user->email,
                                'email_verified_at' => $user->email_verified_at,
                                'phone' => $user->phone,
                                'default_address_id' => $user->default_address_id,
                                'default_address' => $default_address,
                                'delivery_pin' => $user->delivery_pin,
                                'wallet_balance' => $user->balanceFloat,
                                'photo' => $user->photo,                  
                                'restaurants' => $restaurants,
                            ],      
                        ];
                        return response()->json($response);
    
                        
                    }else{
                        $response = [
                            'success' => false,
                            "message" => "Invalid OTP"
                        ];
                        return response()->json($response);
                    }
                }else{
                    $response = [
                        'success' => false,
                        "message" => "No attached restaurants"
                    ];
                    return response()->json($response);
                }

                
            }else{
                // User not exist, show error message
                $response = [
                    'success' => false,
                    "message" => "Invalid phone number"
                ];
                return response()->json($response);
            }
        }

    }


    public function dashboard($user_id)
    {
        $user = User::where('id', $user_id)->first();
        if($user == null) return response()->json(array("success"=>false, "message"=>"Invalid user_id"));

        $restaurant = $user->restaurants;

        $restaurantIds = $user->restaurants->pluck('id')->toArray();
        if($restaurantIds == null) return response()->json(array("success"=>false, "message"=>"No resturant found for this user"));  
        $firstRestaurant = $restaurantIds[0];

        //$restaurant = Restaurant::where('id', $firstRestaurant)->first();



        $newOrders = Order::where('restaurant_id', $firstRestaurant)
            ->where('orderstatus_id', '1')
            ->orderBy('id', 'DESC')
            ->with('restaurant')
            ->get();
            

        $newOrdersIds = $newOrders->pluck('id')->toArray();

        $acceptedOrders = Order::where('restaurant_id', $firstRestaurant)
            ->whereIn('orderstatus_id', ['2', '3', '7', '8', '9'])
            ->orderBy('id', 'DESC')
            ->get();

        $allOrders = Order::where('restaurant_id', $firstRestaurant)
            //->with('restaurant', 'restaurant.location', 'user')
            ->with('restaurant', 'user')
            ->with('orderitems')        
            ->with(array('user' => function ($query) {
                $query->select('id', 'name', 'phone');
            }))
            ->get();
         

            
        $ordersCount = count($allOrders);

        $orderItemsCount = 0;
        foreach ($allOrders as $order) {
            $orderItemsCount += count($order->orderitems);

            /*======================================================================================*/        
            $itemTotal =  $this->getItemTotal($order);
            $discountAmount = "0";
            $coupon = Coupon::where('code', strtoupper($order->coupon_name))->first();        
            if ($coupon) {                
                $discountAmount = $this->getCouponAmount($coupon, $itemTotal);    
            }
            $order["discount_amount"] = $discountAmount;
            $order["item_total"] = $itemTotal;
            /*======================================================================================*/
        }

        $allCompletedOrders = Order::where('restaurant_id', $firstRestaurant)
            ->where('orderstatus_id', '5')
            ->with('orderitems')
            ->get();

        $totalEarning = 0;
        settype($var, 'float');

        foreach ($allCompletedOrders as $completedOrder) {
            $totalEarning += $completedOrder->total;
        }

        /*===========================================NEW[13/09/2020]=========================================*/        
        $from = date('2018-01-01');
        $to = date('2018-05-02');
        $now = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime("-1 days"));
        $oneWeek   =  date('Y-m-d', strtotime("-7 days"));        
        $oneMonth  =  date('Y-m-d', strtotime("-30 days"));

        $date = new \DateTime();
        $date->modify('-3 hours');
        $formatted_date = $date->format('Y-m-d H:i:s');
        $lb = \DB::table('myTable')->where('created_at', '>',$formatted_date);

        
        $todaysAllCompletedOrders = Order::where('restaurant_id', $firstRestaurant)
            ->where('orderstatus_id', '5')
            //->where('created_at', '>=', $now)
            ->whereDate('created_at', Carbon::today())
            ->with('orderitems', 'restaurant')
            ->get();

        $yesterdaysAllCompletedOrders = Order::where('restaurant_id', $firstRestaurant)
            ->where('orderstatus_id', '5')
            //->where('created_at', '>=', $yesterday)
            ->whereDate('created_at', Carbon::yesterday())
            ->with('orderitems')
            ->get();

        // dd(Carbon::today());
        // 2020-10-21 00:00:00.0 Asia/Kolkata (+05:30)
        // 2020-10-22 00:00:00.0 Asia/Kolkata (+05:30)


        $oneWeekAllCompletedOrders = Order::where('restaurant_id', $firstRestaurant)
            ->where('orderstatus_id', '5')
            //->where('created_at', '>=', $oneWeek)
            ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->with('orderitems')
            ->get();

        $oneMonthAllCompletedOrders = Order::where('restaurant_id', $firstRestaurant)
            ->where('orderstatus_id', '5')
            //->where('created_at', '>=', $oneMonth)
            ->whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->with('orderitems')
            ->get();

        //........................Cancelled Orders.............................
        $todaysAllCancelledOrders = Order::where('restaurant_id', $firstRestaurant)
            ->where('orderstatus_id', '6')
            //->where('created_at', '>=', $now)
            ->whereDate('created_at', Carbon::today())
            ->with('orderitems')
            ->get();
            
        $yesterdaysAllCancelledOrders = Order::where('restaurant_id', $firstRestaurant)
            ->where('orderstatus_id', '6')
            //->where('created_at', '>=', $yesterday)
            ->where('created_at', '=', Carbon::yesterday())
            ->with('orderitems')
            ->get();
            
        $oneWeeksAllCancelledOrders = Order::where('restaurant_id', $firstRestaurant)
            ->where('orderstatus_id', '6')
            //->where('created_at', '>=', $oneWeek)
            ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->with('orderitems')
            ->get();
            
        $oneMonthsAllCancelledOrders = Order::where('restaurant_id', $firstRestaurant)
            ->where('orderstatus_id', '6')
            //->where('created_at', '>=', $oneMonth)
            ->whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->with('orderitems')
            ->get();
        //.........................................................................

        //Earning:
        $todaysTotalEarning = 0;
        $yesterdaysTotalEarning = 0;
        $oneWeekTotalEarning = 0;
        $oneMonthTotalEarning = 0;
        //Loss:
        $todaysTotalLoss = 0;
        $yesterdaysTotalLoss = 0;
        $oneWeekTotalLoss = 0;
        $oneMonthTotalLoss = 0;
        settype($var, 'float');

        foreach ($todaysAllCompletedOrders as $earning) {
            
            /*======================================================================================*/        
            $itemTotal =  $this->getItemTotal($earning);
            $discountAmount = "0";
            $coupon = Coupon::where('code', strtoupper($earning->coupon_name))->first();        
            if ($coupon) {                
                $discountAmount = $this->getCouponAmount($coupon, $itemTotal);    
            }
            $earning["discount_amount"] = $discountAmount;
            $earning["item_total"] = $itemTotal;
            //$earning = $itemTotal - $discountAmount;
            $earning = $itemTotal;

            $commission = $order->restaurant->commission_rate;
            $commissionToCompany = ($earning * $commission) / 100; 
            $earning -= $commissionToCompany;
            
            //$todaysTotalEarning += $earning->total; 
            $todaysTotalEarning += $earning; 
            /*======================================================================================*/
        }

        foreach ($yesterdaysAllCompletedOrders as $earning) {
            //$yesterdaysTotalEarning += $earning->total;
            $itemTotal =  $this->getItemTotal($earning);
            $discountAmount = "0";
            $coupon = Coupon::where('code', strtoupper($earning->coupon_name))->first();        
            if ($coupon) {                
                $discountAmount = $this->getCouponAmount($coupon, $itemTotal);    
            }
            $earning["discount_amount"] = $discountAmount;
            $earning["item_total"] = $itemTotal;
            //$earning = $itemTotal - $discountAmount;
            $earning = $itemTotal;


            $commission = $order->restaurant->commission_rate;
            $commissionToCompany = ($earning * $commission) / 100; 
            $earning -= $commissionToCompany;
            
            $yesterdaysTotalEarning += $earning; 
        }

        foreach ($oneWeekAllCompletedOrders as $earning) {
            //$oneWeekTotalEarning += $earning->total; 
            $itemTotal =  $this->getItemTotal($earning);
            $discountAmount = "0";
            $coupon = Coupon::where('code', strtoupper($earning->coupon_name))->first();        
            if ($coupon) {                
                $discountAmount = $this->getCouponAmount($coupon, $itemTotal);    
            }
            $earning["discount_amount"] = $discountAmount;
            $earning["item_total"] = $itemTotal;
            //$earning = $itemTotal - $discountAmount;
            $earning = $itemTotal;
               

            $commission = $order->restaurant->commission_rate;
            $commissionToCompany = ($earning * $commission) / 100; 
            $earning -= $commissionToCompany;
            
            $oneWeekTotalEarning += $earning; 
        }

        foreach ($oneMonthAllCompletedOrders as $earning) {
            //$oneMonthTotalEarning += $earning->total;
            $itemTotal =  $this->getItemTotal($earning);
            $discountAmount = "0";
            $coupon = Coupon::where('code', strtoupper($earning->coupon_name))->first();        
            if ($coupon) {                
                $discountAmount = $this->getCouponAmount($coupon, $itemTotal);    
            }
            $earning["discount_amount"] = $discountAmount;
            $earning["item_total"] = $itemTotal;
            //$earning = $itemTotal - $discountAmount;
            $earning = $itemTotal;


            $commission = $order->restaurant->commission_rate;
            $commissionToCompany = ($earning * $commission) / 100; 
            $earning -= $commissionToCompany;
            
            $oneMonthTotalEarning += $earning; 
        }

        //Loss:
        foreach ($todaysAllCancelledOrders as $loss) {
            //$todaysTotalLoss += $loss->total;}
            $itemTotal =  $this->getItemTotal($loss);
            $discountAmount = "0";
            $coupon = Coupon::where('code', strtoupper($loss->coupon_name))->first();        
            if ($coupon) {                
                $discountAmount = $this->getCouponAmount($coupon, $itemTotal);    
            }
            $loss["discount_amount"] = $discountAmount;
            $loss["item_total"] = $itemTotal;
            //$loss = $itemTotal - $discountAmount;
            $loss = $itemTotal;

            $commission = $order->restaurant->commission_rate;
            $commissionToCompany = ($loss * $commission) / 100; 
            $loss -= $commissionToCompany;
            
            $todaysTotalLoss += $loss; 
        }

        foreach ($yesterdaysAllCancelledOrders as $loss) {
            //$yesterdaysTotalLoss += $loss->total;
            $itemTotal =  $this->getItemTotal($loss);
            $discountAmount = "0";
            $coupon = Coupon::where('code', strtoupper($loss->coupon_name))->first();        
            if ($coupon) {                
                $discountAmount = $this->getCouponAmount($coupon, $itemTotal);    
            }
            $loss["discount_amount"] = $discountAmount;
            $loss["item_total"] = $itemTotal;
            //$loss = $itemTotal - $discountAmount;
            $loss = $itemTotal;


            $commission = $order->restaurant->commission_rate;
            $commissionToCompany = ($loss * $commission) / 100; 
            $loss -= $commissionToCompany;
            
            $yesterdaysTotalLoss += $loss; 
        }
        
        foreach ($oneWeeksAllCancelledOrders as $loss) {
            //$oneWeekTotalLoss += $loss->total;
            $itemTotal =  $this->getItemTotal($loss);
            $discountAmount = "0";
            $coupon = Coupon::where('code', strtoupper($loss->coupon_name))->first();        
            if ($coupon) {                
                $discountAmount = $this->getCouponAmount($coupon, $itemTotal);    
            }
            $loss["discount_amount"] = $discountAmount;
            $loss["item_total"] = $itemTotal;
            //$loss = $itemTotal - $discountAmount;
            $loss = $itemTotal;


            $commission = $order->restaurant->commission_rate;
            $commissionToCompany = ($loss * $commission) / 100; 
            $loss -= $commissionToCompany;
            
            $oneWeekTotalLoss += $loss; 
        }

        foreach ($oneMonthsAllCancelledOrders as $loss) {
            //$oneMonthTotalLoss += $loss->total;
            $itemTotal =  $this->getItemTotal($loss);
            $discountAmount = "0";
            $coupon = Coupon::where('code', strtoupper($loss->coupon_name))->first();        
            if ($coupon) {                
                $discountAmount = $this->getCouponAmount($coupon, $itemTotal);    
            }
            $loss["discount_amount"] = $discountAmount;
            $loss["item_total"] = $itemTotal;
            //$loss = $itemTotal - $discountAmount;
            $loss = $itemTotal;


            $commission = $order->restaurant->commission_rate;
            $commissionToCompany = ($loss * $commission) / 100; 
            $loss -= $commissionToCompany;
            
            $oneMonthTotalLoss += $loss;
        }

        /*===================================================================================================*/

        return response()->json(array(
            'restaurantsCount' => count($user->restaurants),
            'ordersCount' => $ordersCount,
            'orderItemsCount' => $orderItemsCount,
            //Earnings:
            'todaysTotalEarning' => $todaysTotalEarning,
            'yesterdaysTotalEarning' => $yesterdaysTotalEarning,
            'oneWeekTotalEarning'  => $oneWeekTotalEarning,
            'oneMonthTotalEarning'  =>  $oneMonthTotalEarning,
            'totalEarning' => $totalEarning,
            //EarningOrders:
            'todaysAllCompletedOrders' => $todaysAllCompletedOrders,
            'yesterdaysAllCompletedOrders' => $yesterdaysAllCompletedOrders,
            'oneWeekAllCompletedOrders' => $oneWeekAllCompletedOrders,
            'oneMonthAllCompletedOrders' => $oneMonthAllCompletedOrders,
            'newOrders' => $newOrders,
            'newOrdersIds' => $newOrdersIds,
            'acceptedOrders' => $acceptedOrders,
            'allOrders' => $allOrders,
            //Loss:
            'todaysTotalLoss' => $todaysTotalLoss,
            'yesterdaysTotalLoss' => $yesterdaysTotalLoss,
            'oneWeekTotalLoss'  => $oneWeekTotalLoss,
            'oneMonthTotalLoss'  =>  $oneMonthTotalLoss,
            //LossesOrders:
            'todaysAllCancelledOrders' => $todaysAllCancelledOrders,
            'yesterdaysAllCancelledOrders' => $yesterdaysAllCancelledOrders,
            'oneWeeksAllCancelledOrders' => $oneWeeksAllCancelledOrders,
            'oneMonthsAllCancelledOrders' => $oneMonthsAllCancelledOrders
        ));
    }


    public function dashboardOld($user_id)
    {
        $user = User::where('id', $user_id)->first();
        if($user == null) return response()->json(array("success"=>false, "message"=>"Invalid user_id"));

        $restaurant = $user->restaurants;

        $restaurantIds = $user->restaurants->pluck('id')->toArray();
        // dd($restaurantIds);

        $newOrders = Order::whereIn('restaurant_id', $restaurantIds)
            ->where('orderstatus_id', '1')
            ->orderBy('id', 'DESC')
            ->with('restaurant')
            ->get();

        $newOrdersIds = $newOrders->pluck('id')->toArray();

        $acceptedOrders = Order::whereIn('restaurant_id', $restaurantIds)
            ->whereIn('orderstatus_id', ['2', '3', '7', '8', '9'])
            ->orderBy('id', 'DESC')
            ->get();

        $allOrders = Order::whereIn('restaurant_id', $restaurantIds)
            ->with('orderitems')
            ->get();
        $ordersCount = count($allOrders);

        $orderItemsCount = 0;
        foreach ($allOrders as $order) {
            $orderItemsCount += count($order->orderitems);
        }

        $allCompletedOrders = Order::whereIn('restaurant_id', $restaurantIds)
            ->where('orderstatus_id', '5')
            ->with('orderitems')
            ->get();

        $totalEarning = 0;
        settype($var, 'float');

        foreach ($allCompletedOrders as $completedOrder) {
            $totalEarning += $completedOrder->total;
        }

        return response()->json(array(
            'restaurantsCount' => count($user->restaurants),
            'ordersCount' => $ordersCount,
            'orderItemsCount' => $orderItemsCount,
            'totalEarning' => $totalEarning,
            'newOrders' => $newOrders,
            'newOrdersIds' => $newOrdersIds,
            'acceptedOrders' => $acceptedOrders,
            'allOrders' => $allOrders
        ));
    }


    /**
     * @param Request $request
     */
    public function getNewOrdersForApi(Request $request)
    {
        $newOrders = [
            "msg"=>"Hello World"
        ];

        return response()->json($newOrders);
    }


    /**
     * @param $user_id
     */
    public function orders($user_id)
    {
        $user = User::where('id', $user_id)->first();
        if($user == null) return response()->json(array("success"=>false, "message"=>"Invalid Resturant user_id"));
        
        $restaurantIds = $user->restaurants->pluck('id')->toArray();

        $orders = Order::orderBy('id', 'DESC')
            ->whereIn('restaurant_id', $restaurantIds)
            ->with('accept_delivery.user', 'restaurant')
            ->paginate('20');

        $count = $orders->total();
        // dd($orders);

        $response = [
            'orders' => $orders,
            'count' => $count,            
        ];

        return response()->json($response);
    }




    /**
     * @param Request $request
     */
    public function getRunningOrders(Request $request)
    {
        $firstRestaurant = $request->restaurant_id;
        $listedOrderIds = $request->listed_order_ids;

        if ($listedOrderIds) {
            $acceptedOrders = Order::where('restaurant_id', $firstRestaurant)
            ->whereIn('id', $listedOrderIds)
            ->orderBy('id', 'DESC')
            //->with('restaurant', 'restaurant.location', 'user', 'orderitems')
            ->get();


            foreach ($acceptedOrders as $running_order) {
                $delivery_details = null;
                //3=>DELIVERY_ASSIGNED, 5=>DELIVERED, 6=>CANCELLED, 8=>DELIVERY_BOY_REACHED_TO_RESTAURANT
                if($running_order->orderstatus_id == 3 || $running_order->orderstatus_id == 10){                       
                    $delivery_guy = AcceptDelivery::where('order_id', $running_order->id)->first();
                    if ($delivery_guy) {
                        $delivery_user = User::where('id', $delivery_guy->user_id)->first();
                        
                        //$delivery_details = $delivery_user;
                        $delivery_details = $delivery_user->delivery_guy_detail;
                        $delivery_details["phone"] = $delivery_user->phone;
                    }
                }                
                $running_order["delivery_details"] = $delivery_details;     
            }


        }else{
            $acceptedOrders = [];
        }
        return response()->json($acceptedOrders);
    }


    /**
     * @param Request $request
     */
    public function getNewOrdersOld(Request $request)
    {
        $user = User::where('id', $request->user_id)->first();

        $restaurant = $user->restaurants;

        $restaurantIds = $user->restaurants->pluck('id')->toArray(); 
        if($restaurantIds == null) return response()->json(array("success"=>false, "message"=>"No resturant found for this user"));  
        $firstRestaurant = $restaurantIds[0];

        //$restaurant = Restaurant::where('id', $firstRestaurant)->first();

        
        $from = date('2018-01-01');
        $to = date('2018-05-02');
        $now = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime("-1 days"));

        $listedOrderIds = $request->listed_order_ids;
        if ($listedOrderIds) {
            $newOrders = Order::where('restaurant_id', $firstRestaurant)
            ->whereNotIn('id', $listedOrderIds)
            ->where('orderstatus_id', '1')
            ->where('created_at', '>=', $now)
            ->orderBy('id', 'DESC')
            ->with('restaurant', 'restaurant.location', 'user', 'orderitems')
            ->get();
        }else{
            $newOrders = Order::where('restaurant_id', $firstRestaurant)
            ->where('orderstatus_id', '1')
            ->where('created_at', '>=', $now)
            ->orderBy('id', 'DESC')
            ->with('restaurant', 'restaurant.location', 'user', 'orderitems')
            ->get();
        }

        return response()->json($newOrders);
    }




    /**
     * @param Request $request
     */
    public function getNewOrders(Request $request)
    {
        $user = User::where('id', $request->user_id)->first();

        $restaurant = $user->restaurants;

        $restaurantIds = $user->restaurants->pluck('id')->toArray(); 
        if($restaurantIds == null) return response()->json(array("success"=>false, "message"=>"No resturant found for this user"));  
        $firstRestaurant = $restaurantIds[0];

        //$restaurant = Restaurant::where('id', $firstRestaurant)->first();

        
        $from = date('2018-01-01');
        $to = date('2018-05-02');
        $now = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime("-1 days"));

        $listedOrderIds = $request->listed_order_ids;
        if ($listedOrderIds) {
            $newOrders = Order::where('restaurant_id', $firstRestaurant)
            ->whereNotIn('id', $listedOrderIds)
            ->where('orderstatus_id', '1')
            ->where('created_at', '>=', $now)
            ->orderBy('id', 'DESC')
            //->with('restaurant', 'restaurant.location', 'user', 'orderitems')
            ->with('restaurant', 'user', 'orderitems')
            ->get();

            foreach ($newOrders as $running_order){
                $itemTotal =  $this->getItemTotal($running_order);
                $discountAmount = "0";
                $coupon = Coupon::where('code', strtoupper($running_order->coupon_name))->first();        
                if ($coupon) {                
                    $discountAmount = $this->getCouponAmount($coupon, $itemTotal);    
                }
                $running_order["discount_amount"] = $discountAmount;
                $running_order["item_total"] = $itemTotal;
            }           
            
        
        }else{
            $newOrders = Order::where('restaurant_id', $firstRestaurant)
            ->where('orderstatus_id', '1')
            ->where('created_at', '>=', $now)
            ->orderBy('id', 'DESC')
            //->with('restaurant', 'restaurant.location', 'user', 'orderitems')
            ->with('restaurant', 'user', 'orderitems')
            ->get();

            foreach ($newOrders as $running_order){
                $itemTotal =  $this->getItemTotal($running_order);
                $discountAmount = "0";
                $coupon = Coupon::where('code', strtoupper($running_order->coupon_name))->first();        
                if ($coupon) {                
                    $discountAmount = $this->getCouponAmount($coupon, $itemTotal);    
                }
                $running_order["discount_amount"] = $discountAmount;
                $running_order["item_total"] = $itemTotal;
            }
        }



        
        $acceptedOrders = Order::where('restaurant_id', $firstRestaurant)
        ->whereIn('id', $listedOrderIds)
        //->whereDate('created_at', Carbon::today())
        ->whereIn('orderstatus_id', ['2', '3', '4', '5', '6', '7', '8', '9', '10'])
        //->where('created_at', '>=', $yesterday)
        ->orderBy('id', 'DESC')
        //->with('restaurant', 'restaurant.location', 'user', 'orderitems')
        ->get();


        // foreach ($acceptedOrders as $running_order) {
        //     $delivery_details = null;
        //     //3=>DELIVERY_ASSIGNED, 5=>DELIVERED, 6=>CANCELLED, 8=>DELIVERY_BOY_REACHED_TO_RESTAURANT
        //     if($running_order->orderstatus_id == 3){                       
        //         $delivery_guy = AcceptDelivery::where('order_id', $running_order->id)->first();
        //         if ($delivery_guy) {
        //             $delivery_user = User::where('id', $delivery_guy->user_id)->first();
        //             //$delivery_details = $delivery_user;
        //             $delivery_details = $delivery_user->delivery_guy_detail;
        //             $delivery_details["phone"] = $delivery_user->phone;
        //         }
        //     }                
        //     $running_order["delivery_details"] = $delivery_details;     
        // }
        


        $response = array(
            "new_orders" => $newOrders,
            "listed_order_status" => $acceptedOrders,
        );
        //return response()->json($newOrders);
        return response()->json($response);
    }


     /**
     * @param Request $request
     */
    public function getAllAcceptedOrders(Request $request)//user_id
    {        
        $user = User::where('id', $request->user_id)->first();
        if($user == null) return response()->json(array("success"=>false, "message"=>"Invalid Resturant user_id"));        

        $restaurantIds = $user->restaurants->pluck('id')->toArray();
        if($restaurantIds == null) return response()->json(array("success"=>false, "message"=>"No resturant found for this user"));  
        $firstRestaurant = $restaurantIds[0];
        //if($firstRestaurant != $request->restaurant_id) return response()->json(array("success"=>false, "message"=>"Invalid restaurant_id"));

        $restaurant = Restaurant::where('id', $firstRestaurant)->first();       

        $from = date('2018-01-01');
        $to = date('2018-05-02');
        $now = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime("-1 days"));

        $newOrders = Order::where('restaurant_id', $firstRestaurant)
        ->whereIn('orderstatus_id', ['2', '3', '4', '7', '8', '9', '10'])
        ->where('created_at', '>=', $yesterday)
        ->orderBy('id', 'DESC')
        //->with('restaurant', 'restaurant.location', 'user', 'orderitems')
        ->with('restaurant', 'user', 'orderitems')
        ->get();       
      

        /*===========================GetDeliveryGuyDetails=====================================*/
        foreach ($newOrders as $running_order) {
            $delivery_details = null;
            if ($running_order->orderstatus_id == 3 || $running_order->orderstatus_id == 4 || $running_order->orderstatus_id == 5 || $running_order->orderstatus_id == 7 || $running_order->orderstatus_id == 8 || $running_order->orderstatus_id == 9 || $running_order->orderstatus_id == 10) {
               //get assigned delivery guy and get the details to show to customers
               $delivery_guy = AcceptDelivery::where('order_id', $running_order->id)->first();
               if ($delivery_guy) {
                    $delivery_user = User::where('id', $delivery_guy->user_id)->first();
                    // $delivery_details = $delivery_user->delivery_guy_detail;
                    // if (!empty($delivery_details)) {
                    //     $delivery_details = $delivery_details->toArray();
                    //     $delivery_details['phone'] = $delivery_user->phone;
                    // }
                    //$delivery_details = $delivery_user;
                    $delivery_details = $delivery_user->delivery_guy_detail;
                    $delivery_details["phone"] = $delivery_user->phone;
                }
            }
            
            $running_order["delivery_details"] = $delivery_details;
            /*======================================================================================*/        
            $itemTotal =  $this->getItemTotal($running_order);
            $discountAmount = "0";
            $coupon = Coupon::where('code', strtoupper($running_order->coupon_name))->first();        
            if ($coupon) {                
                $discountAmount = $this->getCouponAmount($coupon, $itemTotal);    
            }
            $running_order["discount_amount"] = $discountAmount;
            $running_order["item_total"] = $itemTotal;
            /*======================================================================================*/
        }


        // $newOrders = Order::where('restaurant_id', $firstRestaurant)
        //     ->where('orderstatus_id', '1')
        //     ->whereBetween('created_at', [$from, $to])
        //     ->orderBy('id', 'DESC')
        //     ->with('restaurant', 'restaurant.location', 'user', 'orderitems')
        //     ->get();

        // $newOrders = Order::where('restaurant_id', $firstRestaurant)
        //     ->where('orderstatus_id', '1')
        //     ->where('created_at', '>=', $yesterday)
        //     ->where('created_at', '<=', $now)
        //     ->orderBy('id', 'DESC')
        //     ->with('restaurant', 'restaurant.location', 'user', 'orderitems')
        //     ->get();

      
        return response()->json($newOrders);
    }

    


    /**
     * @param Request $request
     */
    public function acceptOrder(Request $request)
    {
        $user = User::where('id', $request->user_id)->first();
        $restaurantIds = $user->restaurants->pluck('id')->toArray();

        $order = Order::where('id', $request->order_id)
            ->whereIn('restaurant_id', $restaurantIds)
            ->with('restaurant', 'restaurant.location', 'user', 'orderitems')
            ->first();


        if($order == null) return response()->json(array("success"=>false, "message"=>"Invalid order_id"));

        if ($order->orderstatus_id == '1') {
            $order->orderstatus_id = 2;
            //$randomNum = substr(str_shuffle("0123456789"), 0, 4);//where 4 can be number of digits
            // $order->delivery_pin = strtoupper(str_random(5)); // Newly created by ARPAN on 02-Oct-2020
            $order->delivery_pin = mt_rand(10000,99999); // Newly created by OM
            //$order->delivery_pin = $randomNum;
            $order->prepare_time = $request->food_prepare_time;
            $order->restaurant_accept_at = Carbon::now()->toDateTimeString();// Produces something like "2019-03-11 12:25:00"
            $order->save();

            // ######################## Send PUSH Notification to Delivery Guy  ########################  
            $push_responses = array();
            if (config('settings.enablePushNotificationOrders') == 'true') {
                //to user
                $notify = new PushNotify();
                $notify->sendPushNotification('2', $order->user_id, $order->unique_order_id);

                //to DeliveryGuy
                /*
                $restaurant = Restaurant::where('id', $order->restaurant_id)->first();
                if ($restaurant) {
                    //get all pivot users of restaurant (delivery guy/ res owners)
                    $pivotUsers = $restaurant->users()
                        ->wherePivot('restaurant_id', $order->restaurant_id)
                        ->get();
                     //filter only DeliveryGuy and send notification.
                    foreach ($pivotUsers as $pU) {
                        if ($pU->hasRole('Delivery Guy')) {
                            $notify = new PushNotify();
                            //$notify->sendPushNotification('TO_DELIVERY', $pU->id);//ORIGINAL
                            $res = $notify->pushNotificationToDeliveryGuy($pU->id, $order);//NEW
                            $push_responses[] = $res;
                        }
                    }
                }
                */
            }
            // ######################## END Send PUSH Notification to Delivery Guy ########################

            

            // ######################## Send SMS Notification to Delivery Guy  ########################                       
            if (config('settings.smsDeliveryNotify') == 'true' && $order->delivery_type == 1) {
                /*
                //get restaurant
                $restaurant = Restaurant::where('id', $order->restaurant_id)->first();
                if ($restaurant) {
                    //get all pivot users of restaurant (delivery guy/ res owners)
                    $pivotUsers = $restaurant->users()
                        ->wherePivot('restaurant_id', $order->restaurant_id)
                        ->get();
                    //filter only res owner and send notification.
                    foreach ($pivotUsers as $pU) {
                        if ($pU->hasRole('Delivery Guy')) {
                            //send Notification to Delivery Guy
                            $message = config('settings.defaultSmsDeliveryMsg');
                            $otp = null;
                            $smsnotify = new Sms();
                            $smsnotify->processSmsAction('OD_NOTIFY', $pU->phone, $otp, $message);
                        }
                    }
                }
                */
            }  
            // ######################## END Send SMS Notification to Delivery Guy ########################

            return response()->json(array("success"=>true, "message"=>"Order Accepted", "push_responses"=>$push_responses));

        } else {
            return response()->json(array("success"=>false, "message"=>"Something went wrong."));
        }
    }
   

    /**
     * @param Request $request
     */
    public function markOrderReady(Request $request)
    {
        $user = User::where('id', $request->user_id)->first();
        $restaurantIds = $user->restaurants->pluck('id')->toArray();

        $order = Order::where('id', $request->order_id)->whereIn('restaurant_id', $restaurantIds)->first();
        if($order == null) return response()->json(array("success"=>false, "message"=>"Invalid order_id"));

        if ($order->orderstatus_id == '2' || $order->orderstatus_id == '3' || $order->orderstatus_id == '8') {            
            if($order->orderstatus_id == '3'){
                // As delivery guy already assigned(status_id = 3), and restaurant currently marking the order as "READY", so order is ready to PICKUP
                $order->orderstatus_id = 10; // New status introduced to confirm ORDER_READY_FOR_PICKUP
            }else{
                $order->orderstatus_id = 7;  // As DeliveryGuy not accepted(status_id != 3) the order, but restaurant is marking the order as READY
            }
            
            $order->restaurant_ready_at = Carbon::now()->toDateTimeString();// Produces something like "2019-03-11 12:25:00"
            // update onE [28-05-2022]
            $order->delivery_pin = mt_rand(10000,99999);
            $order->save();

            if (config('settings.enablePushNotificationOrders') == 'true') {
                $notify = new PushNotify();

                //to user
                $notify->sendPushNotification('7', $order->user_id, $order->unique_order_id);// ORIGINAL

                // to restaurant
                /*
                $restaurant = Restaurant::where('id', $order->restaurant_id)->first();
                $order->restaurant = $restaurant;
                //$notify->pushNotificationToDeliveryGuy($pU->id, $order);//NEW
                */
            }
            return response()->json(array("success"=>true, "message"=>"Order Marked as Ready"));
        } else {
            return response()->json(array("success"=>false, "message"=>"Something went wrong."));
        }
    }

    /**
     * @param Request $request
     */
    public function markSelfPickupOrderAsCompleted(Request $request)
    {
        $user = User::where('id', $request->user_id)->first();
        $restaurantIds = $user->restaurants->pluck('id')->toArray();

        $order = Order::where('id', $request->order_id)->whereIn('restaurant_id', $restaurantIds)->first();
        if($order == null) return response()->json(array("success"=>false, "message"=>"Invalid order_id"));

        // update onE [28-05-2022]
        if(!isset($request->delivery_pin))return redirect()->back()->with(array('message' => 'Delivery PIN should not be empty'));
        if($order->delivery_pin != $request->delivery_pin)return redirect()->back()->with(array('message' => 'Invalid Delivery PIN'));

        if ($order->orderstatus_id == '7') {
            $order->orderstatus_id = 5;
            $order->rider_deliver_at = Carbon::now()->toDateTimeString();// Produces something like "2019-03-11 12:25:00"
            $order->save();

            //if selfpickup add amount to restaurant earnings if not COD then add order total
            if ($order->payment_mode == 'STRIPE' || $order->payment_mode == 'PAYPAL' || $order->payment_mode == 'PAYSTACK' || $order->payment_mode == 'RAZORPAY') {
                $restaurant_earning = RestaurantEarning::where('restaurant_id', $order->restaurant->id)
                    ->where('is_requested', 0)
                    ->first();
                if ($restaurant_earning) {
                    $restaurant_earning->amount += $order->total;
                    $restaurant_earning->save();
                } else {
                    $restaurant_earning = new RestaurantEarning();
                    $restaurant_earning->restaurant_id = $order->restaurant->id;
                    $restaurant_earning->amount = $order->total;
                    $restaurant_earning->save();
                }
            }
            //if COD, then take the $total minus $payable amount
            if ($order->payment_mode == 'COD') {
                $restaurant_earning = RestaurantEarning::where('restaurant_id', $order->restaurant->id)
                    ->where('is_requested', 0)
                    ->first();
                if ($restaurant_earning) {
                    $restaurant_earning->amount += $order->total - $order->payable;
                    $restaurant_earning->save();
                } else {
                    $restaurant_earning = new RestaurantEarning();
                    $restaurant_earning->restaurant_id = $order->restaurant->id;
                    $restaurant_earning->amount = $order->total - $order->payable;
                    $restaurant_earning->save();
                }
            }

            if (config('settings.enablePushNotificationOrders') == 'true') {

                //to user
                $notify = new PushNotify();
                $notify->sendPushNotification('5', $order->user_id, $order->unique_order_id);
            }

            return redirect()->back()->with(array('success' => 'Order Completed'));
        } else {
            return redirect()->back()->with(array('message' => 'Something went wrong.'));
        }
    }


    /**
     * @param Request $request
     */
    public function cancelOrder(Request $request)
    {
        $user = User::where('id', $request->user_id)->first();
        $restaurantIds = $user->restaurants->pluck('id')->toArray();
        $cancelReason = "CANCEL_BY_CUSTOMER";
        if($request->cancel_reason)$cancelReason = $request->cancel_reason;

        $order = Order::where('id', $request->order_id)->whereIn('restaurant_id', $restaurantIds)->first();
        if($order == null) return response()->json(array("success"=>false, "message"=>"Invalid order_id"));

        if ($order) {
            if ($order->orderstatus_id == '1') {
                //change order status to 6 (Canceled)
                $order->orderstatus_id = 6;
                $order->order_comment = $cancelReason;
                $order->save();
                //refund money if paid online
                if (!$order->payment_mode == 'COD') {
                    //paid online or paid fully with wallet (Give full refund)
                    $customer = User::where('id', $order->user_id)->first();
                    if ($customer) {
                        $customer->deposit($order->total * 100, ['description' => config('settings.orderRefundWalletComment') . $order->unique_order_id]);
                    }
                }

                if ($order->payment_mode == 'COD' || $order->payment_mode == 'STRIPE' || $order->payment_mode == 'PAYPAL' || $order->payment_mode == 'PAYSTACK' || $order->payment_mode == 'RAZORPAY') {
                    //if payment is COD, check total and payable.. if there is change, pay the difference
                    $customer = User::where('id', $order->user_id)->first();
                    if ($customer) {
                        if ($order->total - $order->payable != 0) {
                            //that means the customer has used wallet to pay partially..
                            //initiate partial refund
                            $customer->deposit(($order->total - $order->payable) * 100, ['description' => config('settings.orderRefundWalletComment') . $order->unique_order_id]);
                        }
                    }
                }

                if ($order->payment_mode == 'WALLET') {
                    $customer = User::where('id', $order->user_id)->first();
                    if ($customer) {
                        $customer->deposit($order->total * 100, ['description' => config('settings.orderRefundWalletComment') . $order->unique_order_id]);
                    }
                }

                //show notification to user
                if (config('settings.enablePushNotificationOrders') == 'true') {
                    //to user
                    $notify = new PushNotify();
                    $notify->sendPushNotification('6', $order->user_id, $order->unique_order_id);//ORIGINAL
                    //$notify->pushNotificationToDeliveryGuy($pU->id, $order);//NEW
                }

                return response()->json(array("success"=>true, "message"=>"Order Canceled"));
            }else if($order->orderstatus_id == '6'){
                return response()->json(array("success"=>false, "message"=>"Order already Canceled"));
            }else{
                return response()->json(array("success"=>false, "message"=>"Something went wrong for the order id"));
            }
        } else {
            return response()->json(array("success"=>false, "message"=>"Something went wrong."));
        }
    }


    /**
     * @param Request $request
     */
    public function postSearchOrders(Request $request)
    {
        $user = User::where('id', $request->user_id)->first();
        $restaurantIds = $user->restaurants->pluck('id')->toArray();

        //$query = $request['query'];
        $query = $request->query_string;

        $orders = Order::whereIn('restaurant_id', $restaurantIds)
            ->where('unique_order_id', 'LIKE', '%' . $query . '%')
            ->with('accept_delivery.user', 'restaurant')
            ->paginate(20);

        $count = $orders->total();

        $response = [
            'orders' => $orders,
            'count' => $count,
        ];

        return response()->json($response);
    }



    /**
     * @param Request $request
     */
    public function viewOrder(Request $request)
    {
        $user = User::where('id', $request->user_id)->first();
        $restaurantIds = $user->restaurants->pluck('id')->toArray();

        $order = Order::whereIn('restaurant_id', $restaurantIds)
            ->where('unique_order_id', $request->order_id)
            ->with('orderitems.order_item_addons')
            ->first();

        if ($order) {
            return response()->json(array('order' => $order));   
        } else {
            return response()->json(array("success"=>false, "message"=>"No order found for given order_id"));
        }
    }

    /*========================$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$==================================*/
    /*========================$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$==================================*/
    /*========================$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$==================================*/
    /*                                               STORES                                                              */
    /*========================$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$==================================*/
    /*========================$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$==================================*/
    /*========================$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$==================================*/


    /**
     * @param $user_id
     */
    public function restaurants($user_id)
    {
        $user = User::where('id', $user_id)->first();
        if($user == null) return response()->json(array("success"=>false, "message"=>"Invalid Resturant user_id"));

        $restaurants = $user->restaurants;

        //return response()->json(array('restaurants' => $restaurants)); 
        return response()->json($restaurants);   
    }

    /**
     * @param Request $request
     */
    public function getEditRestaurant(Request $request) //user_id,id
    {
        $user = User::where('id', $request->user_id)->first();
        $restaurantIds = $user->restaurants->pluck('id')->toArray();


        $restaurant = Restaurant::where('id', $request->resturant_id)->whereIn('id', $restaurantIds)->first(); //$id

        if ($restaurant) {

            return response()->json(array(
                'restaurant' => $restaurant,
                'schedule_data' => json_decode($restaurant->schedule_data),
            ));
        } else {
            return response()->json(array("success"=>false, "message"=>"Access Denied"));
            //return redirect()->route('restaurant.restaurants')->with(array('message' => 'Access Denied'));
        }
    }


    /**
     * @param Request $request
     */
    public function saveNewRestaurant(Request $request)
    {
        $restaurant = new Restaurant();

        $restaurant->name = $request->name;
        $restaurant->description = $request->description;

        //$image = $request->file('image');
        //$rand_name = time() . str_random(10);
        //$filename = $rand_name . '.' . $image->getClientOriginalExtension();
        // Image::make($image)
        //     ->resize(160, 117)
        //     ->save(base_path('assets/img/restaurants/' . $filename));
        //$restaurant->image = '/assets/img/restaurants/' . $filename;
        //$restaurant->image = '/assets/img/restaurants/' . "dummy.jpg";

        $restaurant->delivery_time = $request->delivery_time;
        $restaurant->price_range = $request->price_range;

        if ($request->is_pureveg == 'true') {
            $restaurant->is_pureveg = true;
        } else {
            $restaurant->is_pureveg = false;
        }

        $restaurant->slug = str_slug($request->name) . '-' . str_random(15);
        $restaurant->certificate = $request->certificate;

        $restaurant->address = $request->address;
        $restaurant->pincode = $request->pincode;
        $restaurant->landmark = $request->landmark;
        $restaurant->latitude = $request->latitude;
        $restaurant->longitude = $request->longitude;

        $restaurant->restaurant_charges = $request->restaurant_charges;

        $restaurant->sku = time() . str_random(10);

        $restaurant->is_active = 0;

        $restaurant->min_order_price = $request->min_order_price;

        try {
            $restaurant->save();
            $user = User::where('id', $request->user_id)->first();
            $user->restaurants()->attach($restaurant);
            //return redirect()->back()->with(array('success' => 'Restaurant Saved'));
            return response()->json(array("success"=>true, "message"=>"Restaurant Saved"));
        } catch (\Illuminate\Database\QueryException $qe) {
            //return redirect()->back()->with(['message' => $qe->getMessage()]);
            return response()->json(array("success"=>false, "message"=>$qe->getMessage()));
        } catch (Exception $e) {
            //return redirect()->back()->with(['message' => $e->getMessage()]);
            return response()->json(array("success"=>false, "message"=>$e->getMessage()));
        } catch (\Throwable $th) {
            //return redirect()->back()->with(['message' => $th]);
            return response()->json(array("success"=>false, "message"=>$th));
        }
    }    

    /**
     * @param $id
     */
    public function disableRestaurant(Request $request)//restaurant_id
    {        
        $user = User::where('id', $request->user_id)->first();
        if($user == null) return response()->json(array("success"=>false, "message"=>"Invalid Resturant user_id"));
        
        $restaurantIds = $user->restaurants->pluck('id')->toArray();
        if($restaurantIds == null) return response()->json(array("success"=>false, "message"=>"No resturant found for this user"));  
        $firstRestaurant = $restaurantIds[0];
        if($firstRestaurant != $request->restaurant_id) return response()->json(array("success"=>false, "message"=>"Invalid restaurant_id")); 


        $restaurant = Restaurant::where('id', $firstRestaurant)->first();
        $toggleResult = $restaurant->is_active == 1 ? 0 : 1;
        if ($restaurant) {
            $restaurant->toggleActive()->save();
            return response()->json(array("success"=>true, "message"=>$toggleResult));
        } else {
            return response()->json(array("success"=>false, "message"=>$toggleResult));
        }
    }


    /**
     * @param Request $request
     */
    public function updateRestaurant(Request $request)
    {
        $restaurant = Restaurant::where('id', $request->resturant_id)->first();
        if($restaurant == null) return response()->json(array("success"=>false, "message"=>"Invalid ResturantId"));

        if ($restaurant) {
            $restaurant->name = $request->name;
            $restaurant->description = $request->description;

            // if ($request->image == null) {
            //     $restaurant->image = $request->old_image;
            // } else {
            //     $image = $request->file('image');
            //     $rand_name = time() . str_random(10);
            //     $filename = $rand_name . '.' . $image->getClientOriginalExtension();

            //     Image::make($image)
            //         ->resize(160, 117)
            //         ->save(base_path('assets/img/restaurants/' . $filename));
            //     $restaurant->image = '/assets/img/restaurants/' . $filename;

            // }

            $restaurant->delivery_time = $request->delivery_time;
            $restaurant->price_range = $request->price_range;

            if ($request->is_pureveg == 'true') {
                $restaurant->is_pureveg = true;
            } else {
                $restaurant->is_pureveg = false;
            }

            $restaurant->certificate = $request->certificate;

            $restaurant->address = $request->address;
            $restaurant->pincode = $request->pincode;
            $restaurant->landmark = $request->landmark;
            $restaurant->latitude = $request->latitude;
            $restaurant->longitude = $request->longitude;

            $restaurant->restaurant_charges = $request->restaurant_charges;

            $restaurant->min_order_price = $request->min_order_price;

            // if ($request->is_schedulable == 'true') {
            //     $restaurant->is_schedulable = true;
            // } else {
            //     $restaurant->is_schedulable = false;
            // }

            try {
                $restaurant->save();
                return response()->json(array("success"=>true, "message"=>"Restaurant Updated"));
            } catch (\Illuminate\Database\QueryException $qe) {
                //return redirect()->back()->with(['message' => $qe->getMessage()]);
                return response()->json(array("success"=>false, "message"=>$qe->getMessage()."first"));
            } catch (Exception $e) {
                //return redirect()->back()->with(['message' => $e->getMessage()]);
                return response()->json(array("success"=>false, "message"=>$e->getMessage()."second"));
            } catch (\Throwable $th) {
                //return redirect()->back()->with(['message' => $th]);
                return response()->json(array("success"=>false, "message"=>$th."third"));
            }




        }
    }



    /*========================$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$==================================*/
    /*========================$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$==================================*/
    /*                                               ITEM_CATEGORIES                                                     */
    /*========================$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$==================================*/
    /*========================$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$==================================*/



    /**
     * @param $user_id
     */
    public function itemcategories($user_id)
    {
        $itemCategories = ItemCategory::orderBy('id', 'DESC')
            ->where('user_id', $user_id)
            ->get();
        $itemCategories->loadCount('items');
        $count = count($itemCategories);

        return response()->json(array(
            'itemCategories' => $itemCategories,
            'count' => $count,
        ));
    }

    /**
     * @param Request $request
     */
    public function createItemCategory(Request $request)
    {
        $itemCategory = new ItemCategory();

        $itemCategory->name = $request->name;
        $itemCategory->user_id = $request->user_id;

        try {
            $itemCategory->save();
            return response()->json(array('success'=>true, 'message' => 'Category Created'));
        } catch (\Illuminate\Database\QueryException $qe) {
            return response()->json(array('message' => $qe->getMessage()));
        } catch (Exception $e) {
            return response()->json(array('message' => $e->getMessage()));
        } catch (\Throwable $th) {
            return response()->json(array('message' => $th));
        }
    }

    /**
     * @param $id
     */
    public function disableCategory(Request $request)//user_id, category_id
    {        
        $user = User::where('id', $request->user_id)->first();
        if($user == null) return response()->json(array("success"=>false, "message"=>"Invalid user_id"));
        

        $itemCategory = ItemCategory::where('id', $request->category_id)->first();
        if ($itemCategory) {
            $itemCategory->toggleEnable()->save();
            return response()->json(array('success'=>true, 'message' => 'Operation Successful'));
        } else {
            return response()->json(array("success"=>false, 'message' => "Invalid category_id"));
        }
    }


    /*========================$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$==================================*/
    /*========================$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$==================================*/
    /*                                               ITEMS                                                               */
    /*========================$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$==================================*/
    /*========================$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$==================================*/



    /**
     * @param Request $user_id
     */
    public function items($user_id)
    {
        $user = User::where('id', $user_id)->first();
        if($user == null) return response()->json(array("success"=>false, "message"=>"Invalid Resturant user_id"));

        $restaurantIds = $user->restaurants->pluck('id')->toArray();
        $firstRestaurant = $restaurantIds[0];

        $items = Item::where('restaurant_id', $firstRestaurant)
            ->join('item_categories', 'items.item_category_id', '=', 'item_categories.id')
            ->with('item_category');



        $items = $items->with('addon_categories')
        ->with(array('addon_categories.addons' => function ($query) {
            $query->where('is_active', 1);
        }))
        ->get(array('items.*', 'item_categories.name as category_name'));

        $items = json_decode($items, true);


        $array = [];
        foreach ($items as $item) {
            $array[$item['category_name']][] = $item;
        }
        // sleep(10);
        return response()->json(array(
            'items' => $array,
        ));
    }


    /**
     * @param Request $user_id
     */
    public function itemsOld($user_id)
    {
        $user = User::where('id', $user_id)->first();
        if($user == null) return response()->json(array("success"=>false, "message"=>"Invalid Resturant user_id"));

        $restaurantIds = $user->restaurants->pluck('id')->toArray();
        $firstRestaurant = $restaurantIds[0];

        $items = Item::where('restaurant_id', $firstRestaurant)
            ->orderBy('id', 'DESC')
            ->with('item_category', 'restaurant')
            ->paginate(20);

        $count = $items->total();

        $restaurants = $user->restaurants;

        $itemCategories = ItemCategory::where('is_enabled', '1')
            ->where('user_id', $user_id)
            ->get();
        $addonCategories = AddonCategory::where('user_id', $user_id)->get();

        return response()->json(array(
            'items' => $items,
            'count' => $count,
            'restaurants' => $restaurants,
            'itemCategories' => $itemCategories,
            'addonCategories' => $addonCategories,
        ));
    }

    /**
     * @param Request $request
     */
    public function searchItems(Request $request)//(user_id, query_string)
    {
        $user = User::where('id', $request->user_id)->first();
        if($user == null) return response()->json(array("success"=>false, "message"=>"Invalid Resturant user_id"));

        $restaurantIds = $user->restaurants->pluck('id')->toArray();
        if($restaurantIds == null) return response()->json(array("success"=>false, "message"=>"No resturant found"));

        //$query = $request['query'];
        $query = $request->query_string;

        $items = Item::whereIn('restaurant_id', $restaurantIds)
            ->where('name', 'LIKE', '%' . $query . '%')
            ->with('item_category', 'restaurant')
            ->paginate(20);

        $count = $items->total();

        $restaurants = Restaurant::get();
        $itemCategories = ItemCategory::where('is_enabled', '1')->get();
        $addonCategories = AddonCategory::where('user_id', $request->user_id)->get();

        return response()->json(array(
            'items' => $items,
            'count' => $count,
            'restaurants' => $restaurants,
            'query' => $query,
            'itemCategories' => $itemCategories,
            'addonCategories' => $addonCategories,
        ));
    }


    /**
     * @param Request $request
     */
    public function disableItem(Request $request)////(user_id, restaurant_id, item_id)
    {
        $user = User::where('id', $request->user_id)->first();
        if($user == null) return response()->json(array("success"=>false, "message"=>"Invalid Resturant user_id"));
        
        $restaurantIds = $user->restaurants->pluck('id')->toArray();
        if($restaurantIds == null) return response()->json(array("success"=>false, "message"=>"No resturant found for this user"));  
        $firstRestaurant = $restaurantIds[0];
        //if($firstRestaurant != $request->restaurant_id) return response()->json(array("success"=>false, "message"=>"Invalid restaurant_id")); 

        $item = Item::where('id', $request->item_id)
            ->where('restaurant_id', $firstRestaurant)
            ->first();
        if ($item) {
            $item->toggleActive()->save();
            return response()->json(array('success'=>true, 'message' => 'Operation Successful'));
        } else {
            return response()->json(array('success'=>false, 'message' => 'Invalid item_id'));
        }
    }


    /**
     * @param $id
     */
    public function getEditItem(Request $request)//(user_id,item_id)
    {
        $user = User::where('id', $request->user_id)->first();
        if($user == null) return response()->json(array("success"=>false, "message"=>"Invalid Resturant user_id"));
        
        $restaurantIds = $user->restaurants->pluck('id')->toArray();
        if($restaurantIds == null) return response()->json(array("success"=>false, "message"=>"No resturant found for this user"));  
        $firstRestaurant = $restaurantIds[0];
        //if($firstRestaurant != $request->restaurant_id) return response()->json(array("success"=>false, "message"=>"Invalid restaurant_id")); 


        $item = Item::where('id', $request->item_id)
            ->where('restaurant_id', $restaurantIds)
            ->first();

        $addonCategories = AddonCategory::where('user_id', Auth::user()->id)->get();

        if ($item) {
            $restaurants = $user->restaurants;
            $itemCategories = ItemCategory::where('is_enabled', '1')
                ->where('user_id', Auth::user()->id)
                ->get();

            return view('restaurantowner.editItem', array(
                'item' => $item,
                'restaurants' => $restaurants,
                'itemCategories' => $itemCategories,
                'addonCategories' => $addonCategories,
            ));
        } else {
            return redirect()->route('restaurant.items')->with(array('message' => 'Access Denied'));
        }
    }

    /**
     * @param Request $request
     */
    public function updateItem(Request $request)//user_id, restaurant_id, item_category_id, item_id, name, desc, image, price, old_price, is_recommended, is_popular, is_new, is_veg
    {
        //$user = Auth::user();

        $user = User::where('id', $request->user_id)->first();
        if($user == null) return response()->json(array("success"=>false, "message"=>"Invalid Resturant user_id"));
        
        $restaurantIds = $user->restaurants->pluck('id')->toArray();
        if($restaurantIds == null) return response()->json(array("success"=>false, "message"=>"No resturant found for this user"));  
        $firstRestaurant = $restaurantIds[0];
        //if($firstRestaurant != $request->restaurant_id) return response()->json(array("success"=>false, "message"=>"Invalid restaurant_id")); 

        $item = Item::where('id', $request->item_id)
            ->where('restaurant_id', $firstRestaurant)
            ->first();


        if ($item) {
            $item->name = $request->name;
            $item->restaurant_id = $request->restaurant_id;
            $item->item_category_id = $request->item_category_id;

            if ($request->image == null) {
                $item->image = $request->old_image;
            } else {
                $image = $request->file('image');
                $rand_name = time() . str_random(10);
                $filename = $rand_name . '.jpg';
                Image::make($image)
                    ->resize(486, 355)
                    ->save(base_path('assets/img/items/' . $filename), config('settings.uploadImageQuality '), 'jpg');
                $item->image = '/assets/img/items/' . $filename;

            }

            $item->price = $request->price;
            $item->old_price = $request->old_price == null ? 0 : $request->old_price;

            if ($request->is_recommended == 'true') {
                $item->is_recommended = true;
            } else {
                $item->is_recommended = false;
            }

            if ($request->is_popular == 'true') {
                $item->is_popular = true;
            } else {
                $item->is_popular = false;
            }

            if ($request->is_new == 'true') {
                $item->is_new = true;
            } else {
                $item->is_new = false;
            }

            if ($request->is_veg == 'true') {
                $item->is_veg = true;
            } else {
                $item->is_veg = false;
            }

            $item->desc = $request->desc;
            try {
                $item->save();
                return response()->json(array('success'=>true,'message' => $th));


                if (isset($request->addon_category_item)) {
                    $item->addon_categories()->sync($request->addon_category_item);
                }
                if ($request->remove_all_addons == '1') {
                    $item->addon_categories()->sync($request->addon_category_item);
                }
                return redirect()->back()->with(array('success' => 'Item Saved'));
            } catch (\Illuminate\Database\QueryException $qe) {
                //return redirect()->back()->with(['message' => $qe->getMessage()]);
                return response()->json(array('success'=>false,'message' => $qe->getMessage()));
            } catch (Exception $e) {
                //return redirect()->back()->with(['message' => $e->getMessage()]);
                return response()->json(array('success'=>false,'message' => $e->getMessage()));
            } catch (\Throwable $th) {
                //return redirect()->back()->with(['message' => $th]);
                return response()->json(array('success'=>false,'message' => $th));
            }
        }
    }

    /**
     * @param Request $request
     */
    public function saveNewItem(Request $request)
    {
        // dd($request->all());

        $item = new Item();

        $item->name = $request->name;
        $item->price = $request->price;
        $item->old_price = $request->old_price == null ? 0 : $request->old_price;
        $item->restaurant_id = $request->restaurant_id;
        $item->item_category_id = $request->item_category_id;

        $image = $request->file('image');
        $rand_name = time() . str_random(10);
        $filename = $rand_name . '.jpg';
        Image::make($image)
            ->resize(486, 355)
            ->save(base_path('assets/img/items/' . $filename), config('settings.uploadImageQuality '), 'jpg');

        $item->image = '/assets/img/items/' . $filename;

        if ($request->is_recommended == 'true') {
            $item->is_recommended = true;
        } else {
            $item->is_recommended = false;
        }

        if ($request->is_popular == 'true') {
            $item->is_popular = true;
        } else {
            $item->is_popular = false;
        }

        if ($request->is_new == 'true') {
            $item->is_new = true;
        } else {
            $item->is_new = false;
        }

        if ($request->is_veg == 'true') {
            $item->is_veg = true;
        } else {
            $item->is_veg = false;
        }
        $item->desc = $request->desc;
        try {
            $item->save();
            if (isset($request->addon_category_item)) {
                $item->addon_categories()->sync($request->addon_category_item);
            }
            //return redirect()->back()->with(['success' => 'Item Saved']);
            return response()->json(array('success'=>true, 'message' => 'Item Saved'));
        } catch (\Illuminate\Database\QueryException $qe) {
            return response()->json(array('success'=>false,'message' => $qe->getMessage()));
        } catch (Exception $e) {
            return response()->json(array('success'=>false,'message' => $e->getMessage()));
        } catch (\Throwable $th) {
            return response()->json(array('success'=>false,'message' => $th));
        }
    }


    

    /*========================$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$==================================*/
    /*========================$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$==================================*/
    /*                                               ADDON_CATEGORY                                                      */
    /*========================$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$==================================*/
    /*========================$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$==================================*/


    public function addonCategories()
    {

        $addonCategories = AddonCategory::where('user_id', Auth::user()->id)
            ->orderBy('id', 'DESC')
            ->paginate(20);
        $addonCategories->loadCount('addons');

        $count = $addonCategories->total();

        return view('restaurantowner.addonCategories', array(
            'addonCategories' => $addonCategories,
            'count' => $count,
        ));
    }

    /**
     * @param Request $request
     */
    public function searchAddonCategories(Request $request)
    {
        $query = $request['query'];

        $addonCategories = AddonCategory::where('user_id', Auth::user()->id)
            ->where('name', 'LIKE', '%' . $query . '%')
            ->paginate(20);
        $addonCategories->loadCount('addons');

        $count = $addonCategories->total();

        return view('restaurantowner.addonCategories', array(
            'addonCategories' => $addonCategories,
            'count' => $count,
        ));
    }

    /**
     * @param Request $request
     */
    public function saveNewAddonCategory(Request $request)
    {
        $addonCategory = new AddonCategory();

        $addonCategory->name = $request->name;
        $addonCategory->type = $request->type;
        $addonCategory->user_id = Auth::user()->id;

        try {
            $addonCategory->save();
            return redirect()->back()->with(array('success' => 'Addon Category Saved'));
        } catch (\Illuminate\Database\QueryException $qe) {
            return redirect()->back()->with(array('message' => 'Something went wrong. Please check your form and try again.'));
        } catch (Exception $e) {
            return redirect()->back()->with(array('message' => $e->getMessage()));
        } catch (\Throwable $th) {
            return redirect()->back()->with(array('message' => $th));
        }
    }

    /**
     * @param $id
     */
    public function getEditAddonCategory($id)
    {
        $addonCategory = AddonCategory::where('id', $id)->first();
        if ($addonCategory) {
            if ($addonCategory->user_id == Auth::user()->id) {
                return view('restaurantowner.editAddonCategory', array(
                    'addonCategory' => $addonCategory,
                ));
            } else {
                return redirect()
                    ->route('restaurant.editAddonCategory')
                    ->with(array('message' => 'Access Denied'));
            }
        } else {
            return redirect()
                ->route('restaurant.editAddonCategory')
                ->with(array('message' => 'Access Denied'));
        }
    }

    /**
     * @param Request $request
     */
    public function updateAddonCategory(Request $request)
    {
        $addonCategory = AddonCategory::where('id', $request->id)->first();

        if ($addonCategory) {
            $addonCategory->name = $request->name;
            $addonCategory->type = $request->type;

            try {
                $addonCategory->save();
                return redirect()->back()->with(array('success' => 'Addon Category Updated'));
            } catch (\Illuminate\Database\QueryException $qe) {
                return redirect()->back()->with(array('message' => 'Something went wrong. Please check your form and try again.'));
            } catch (Exception $e) {
                return redirect()->back()->with(array('message' => $e->getMessage()));
            } catch (\Throwable $th) {
                return redirect()->back()->with(array('message' => $th));
            }
        }
    }

    public function addons()
    {
        $addons = Addon::where('user_id', Auth::user()->id)->with('addon_category')->paginate();

        $count = $addons->total();

        $addonCategories = AddonCategory::where('user_id', Auth::user()->id)->get();

        return view('restaurantowner.addons', array(
            'addons' => $addons,
            'count' => $count,
            'addonCategories' => $addonCategories,
        ));
    }

    /**
     * @param Request $request
     */
    public function searchAddons(Request $request)
    {
        $query = $request['query'];

        $addons = Addon::where('user_id', Auth::user()->id)
            ->where('name', 'LIKE', '%' . $query . '%')
            ->with('addon_category')
            ->paginate(20);

        $count = $addons->total();

        $addonCategories = AddonCategory::where('user_id', Auth::user()->id)->get();

        return view('restaurantowner.addons', array(
            'addons' => $addons,
            'count' => $count,
            'addonCategories' => $addonCategories,
        ));
    }

    /**
     * @param Request $request
     */
    public function saveNewAddon(Request $request)
    {
        $addon = new Addon();

        $addon->name = $request->name;
        $addon->price = $request->price;
        $addon->user_id = Auth::user()->id;
        $addon->addon_category_id = $request->addon_category_id;

        try {
            $addon->save();
            return redirect()->back()->with(array('success' => 'Addon Saved'));
        } catch (\Illuminate\Database\QueryException $qe) {
            return redirect()->back()->with(array('message' => 'Something went wrong. Please check your form and try again.'));
        } catch (Exception $e) {
            return redirect()->back()->with(array('message' => $e->getMessage()));
        } catch (\Throwable $th) {
            return redirect()->back()->with(array('message' => $th));
        }
    }

    /**
     * @param $id
     */
    public function getEditAddon($id)
    {
        $addon = Addon::where('id', $id)
            ->where('user_id', Auth::user()->id)
            ->first();

        $addonCategories = AddonCategory::where('user_id', Auth::user()->id)->get();
        if ($addon) {
            return view('restaurantowner.editAddon', array(
                'addon' => $addon,
                'addonCategories' => $addonCategories,
            ));
        } else {
            return redirect()->route('restaurant.addons')->with(array('message' => 'Access Denied'));
        }
    }

    /**
     * @param Request $request
     */
    public function updateAddon(Request $request)
    {
        $addon = Addon::where('id', $request->id)->first();

        if ($addon) {
            if ($addon->user_id == Auth::user()->id) {
                $addon->name = $request->name;
                $addon->price = $request->price;
                $addon->addon_category_id = $request->addon_category_id;

                try {
                    $addon->save();
                    return redirect()->back()->with(array('success' => 'Addon Updated'));
                } catch (\Illuminate\Database\QueryException $qe) {
                    return redirect()->back()->with(array('message' => 'Something went wrong. Please check your form and try again.'));
                } catch (Exception $e) {
                    return redirect()->back()->with(array('message' => $e->getMessage()));
                } catch (\Throwable $th) {
                    return redirect()->back()->with(array('message' => $th));
                }
            } else {
                return redirect()->route('restaurant.addons')->with(array('message' => 'Access Denied'));
            }
        } else {
            return redirect()->route('restaurant.addons')->with(array('message' => 'Access Denied'));
        }
    }

    /**
     * @param $id
     */
    public function disableAddon($id)
    {
        $addon = Addon::where('id', $id)->firstOrFail();
        if ($addon) {
            $addon->toggleActive()->save();
            return redirect()->back()->with(['success' => 'Operation Successful']);
        } else {
            return redirect()->back()->with(['message' => 'Something Went Wrong']);
        }
    }

    


    /*========================$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$==================================*/
    /*========================$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$==================================*/
    /*                                               EARNING                                                              */
    /*========================$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$==================================*/
    /*========================$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$==================================*/




    /**
     * @param $restaurant_id
     */
    public function earnings($user_id = null, $restaurant_id = null)
    {
        if ($restaurant_id && $user_id) {
            $user = User::where('id', $user_id)->first();
            if($user == null) return response()->json(array("success"=>false, "message"=>"Invalid Resturant user_id"));


            $restaurant = $user->restaurants;
            $restaurantIds = $user->restaurants->pluck('id')->toArray();

            $restaurant = Restaurant::where('id', $restaurant_id)->first();
            // check if restaurant exists
            if ($restaurant) {
                //check if restaurant belongs to the auth user
                // $contains = Arr::has($restaurantIds, $restaurant->id);
                $contains = in_array($restaurant->id, $restaurantIds);
                if ($contains) {
                    //true
                    $allCompletedOrders = Order::where('restaurant_id', $restaurant->id)
                        ->where('orderstatus_id', '5')
                        ->get();

                    $totalEarning = 0;
                    settype($var, 'float');

                    foreach ($allCompletedOrders as $completedOrder) {
                        $totalEarning += $completedOrder->total - $completedOrder->delivery_charge;
                    }

                    // Build an array of the dates we want to show, oldest first
                    $dates = collect();
                    foreach (range(-30, 0) as $i) {
                        $date = Carbon::now()->addDays($i)->format('Y-m-d');
                        $dates->put($date, 0);
                    }

                    // Get the post counts
                    $posts = Order::where('restaurant_id', $restaurant->id)
                        ->where('orderstatus_id', '5')
                        ->where('created_at', '>=', $dates->keys()->first())
                        ->groupBy('date')
                        ->orderBy('date')
                        ->get([
                            DB::raw('DATE( created_at ) as date'),
                            DB::raw('SUM( total ) as "total"'),
                        ])
                        ->pluck('total', 'date');

                    // Merge the two collections; any results in `$posts` will overwrite the zero-value in `$dates`
                    $dates = $dates->merge($posts);

                    // dd($dates);
                    $monthlyDate = '[';
                    $monthlyEarning = '[';
                    foreach ($dates as $date => $value) {
                        $monthlyDate .= "'" . $date . "' ,";
                        $monthlyEarning .= "'" . $value . "' ,";
                    }

                    $monthlyDate = rtrim($monthlyDate, ' ,');
                    $monthlyDate = $monthlyDate . ']';

                    $monthlyEarning = rtrim($monthlyEarning, ' ,');
                    $monthlyEarning = $monthlyEarning . ']';
                    /*=====  End of Monthly Post Analytics  ======*/

                    $balance = RestaurantEarning::where('restaurant_id', $restaurant->id)
                        ->where('is_requested', 0)
                        ->first();

                    if (!$balance) {
                        $balanceBeforeCommission = 0;
                        $balanceAfterCommission = 0;
                    } else {
                        $balanceBeforeCommission = $balance->amount;
                        $balanceAfterCommission = ($balance->amount - ($restaurant->commission_rate / 100) * $balance->amount);
                        $balanceAfterCommission = number_format((float) $balanceAfterCommission, 2, '.', '');
                    }

                    $payoutRequests = RestaurantPayout::where('restaurant_id', $restaurant_id)->orderBy('id', 'DESC')->get();

                    return response()->json(array(
                        'restaurant' => $restaurant,
                        'totalEarning' => $totalEarning,
                        'monthlyDate' => $monthlyDate,
                        'monthlyEarning' => $monthlyEarning,
                        'balanceBeforeCommission' => $balanceBeforeCommission,
                        'balanceAfterCommission' => $balanceAfterCommission,
                        'payoutRequests' => $payoutRequests,
                    ));
                } else {
                    //return redirect()->route('restaurant.earnings')->with(array('message' => 'Access Denied'));
                    return response()->json(array('success'=>false, 'message'=>'Access Denied'));
                }
            } else {
                //return redirect()->route('restaurant.earnings')->with(array('message' => 'Access Denied'));
                return response()->json(array('success'=>false, 'message'=>'Access Denied'));
            }
        } else {
            $user = User::where('id', $user_id)->first();
            $restaurants = $user->restaurants;

            return response()->json(array(
                'restaurants' => $restaurants,
            ));
        }
    }

    /**
     * @param Request $request
     */
    public function sendPayoutRequest(Request $request)
    {
        $restaurant = Restaurant::where('id', $request->restaurant_id)->first();
        $earning = RestaurantEarning::where('restaurant_id', $request->restaurant_id)
            ->where('is_requested', 0)
            ->first();

        $balanceBeforeCommission = $earning->amount;
        $balanceAfterCommission = ($earning->amount - ($restaurant->commission_rate / 100) * $earning->amount);
        $balanceAfterCommission = number_format((float) $balanceAfterCommission, 2, '.', '');

        if ($earning) {
            $payoutRequest = new RestaurantPayout;
            $payoutRequest->restaurant_id = $request->restaurant_id;
            $payoutRequest->restaurant_earning_id = $earning->id;
            $payoutRequest->amount = $balanceAfterCommission;
            $payoutRequest->status = 'PENDING';
            try {
                $payoutRequest->save();
                $earning->is_requested = 1;
                $earning->restaurant_payout_id = $payoutRequest->id;
                $earning->save();
            } catch (\Illuminate\Database\QueryException $qe) {
                return redirect()->back()->with(array('message' => 'Something went wrong. Please check your form and try again.'));
            } catch (Exception $e) {
                return redirect()->back()->with(array('message' => $e->getMessage()));
            } catch (\Throwable $th) {
                return redirect()->back()->with(array('message' => $th));
            }

            return redirect()->back()->with(array('success' => 'Payout Request Sent'));
        } else {
            return redirect()->route('restaurant.earnings')->with(array('message' => 'Access Denied'));
        }
    }

   

    /**
     * @param Request $request
     */
    public function updateRestaurantScheduleData(Request $request)
    {
        $data = $request->except(['_token', 'restaurant_id']);

        $i = 0;
        $str = '{';
        foreach ($data as $day => $times) {
            $str .= '"' . $day . '":[';
            if ($times) {
                foreach ($times as $key => $time) {

                    if ($key % 2 == 0) {
                        $t1 = $time;
                        $str .= '{"open" :' . '"' . $time . '"';

                    } else {
                        $t2 = $time;
                        $str .= '"close" :' . '"' . $time . '"}';
                    }

                    //check if last, if last then dont add comma,
                    if (count($times) != $key + 1) {
                        $str .= ',';
                    }
                }
                // dd($t1);
                if (Carbon::parse($t1) >= Carbon::parse($t2)) {

                    return redirect()->back()->with(['message' => 'Opening and Closing time is incorrect']);
                }
            } else {
                $str .= '}]';
            }

            if ($i != count($data) - 1) {
                $str .= '],';
            } else {
                $str .= ']';
            }
            $i++;
        }
        $str .= '}';

        // Fetches The Restaurant
        $restaurant = Restaurant::where('id', $request->restaurant_id)->first();
        // Enters The Data
        $restaurant->schedule_data = $str;
        // Saves the Data to Database
        $restaurant->save();

        return redirect()->back()->with(['success' => 'Scheduling data saved successfully']);

    }
}
