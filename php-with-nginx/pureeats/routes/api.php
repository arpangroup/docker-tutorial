<?php

use Illuminate\Http\Request;
//Laravel 7 - API Logging Using Middleware
//https://www.youtube.com/watch?v=zIHDOjz1SR4
//GET http://159.89.160.182/api/employees
//GET http://159.89.160.182/api/employees/{empId}
//POST http://159.89.160.182/api/employees/create
//GET http://159.89.160.182/api/employees/delete/1


//Route::get('/employees', [ 'uses' => 'DemoController@getEmployees',]);
//Route::get('/employees/{empId}', ['uses' => 'DemoController@getEmployeeById',]);
//Route::post('/employees/create', ['uses' => 'DemoController@createEmployee',]);
//Route::get('/employees/delete/{empId}', ['uses' => 'DemoController@deleteEmployee',]);



/* API ROUTES */

Route::get('/test', [
    'uses' => 'SettingController@getSettings',
]);
//Route::get('/test',function(){
//    return "hello";
//});


Route::post('/coordinate-to-address', [
    'uses' => 'GeocoderController@coordinatesToAddress',
]);

Route::post('/address-to-coordinate', [
    'uses' => 'GeocoderController@addressToCoordinates',
]);

Route::post('/get-settings', [
    'uses' => 'SettingController@getSettings',
]);

//  to get tip amount list in cart page
Route::get('/get-setting/{key}', [
    'uses' => 'SettingController@getSettingByKey',
]);

Route::post('/search-location/{query}', [
    'uses' => 'LocationController@searchLocation',
]);

Route::post('/popular-locations', [
    'uses' => 'LocationController@popularLocations',
]);

Route::post('/popular-geo-locations', [
    'uses' => 'LocationController@popularGeoLocations',
]);

Route::post('/promo-slider', [
    'uses' => 'PromoSliderController@promoSlider',
]);

Route::post('/home', [
    'uses' => 'HomeController@loadHome',
]);

Route::get('/restaurants', [
    'uses' => 'RestaurantControllerV1@getRestaurants',
]);

Route::get('/restaurant', [
    'uses' => 'RestaurantControllerV1@getRestaurantInfo',
]);

Route::post('/get-delivery-restaurants', [
    'uses' => 'RestaurantController@getDeliveryRestaurants',
]);

Route::post('/get-selfpickup-restaurants', [
    'uses' => 'RestaurantController@getSelfPickupRestaurants',
]);

Route::post('/get-restaurant-info/{slug}', [
    'uses' => 'RestaurantController@getRestaurantInfo',
]);

Route::post('/get-restaurant-info-by-id/{id}', [
    'uses' => 'RestaurantController@getRestaurantInfoById',
]);

Route::post('/get-restaurant-details', [
    'uses' => 'RestaurantController@getRestaurantDetails',
]);

Route::post('/get-restaurant-info-and-operational-status', [
    'uses' => 'RestaurantController@getRestaurantInfoAndOperationalStatus',
]);

Route::post('/get-restaurant-items/{slug}', [
    'uses' => 'RestaurantController@getRestaurantItems',
]);






Route::post('/get-pages', [
    'uses' => 'PageController@getPages',
]);

Route::post('/get-single-page', [
    'uses' => 'PageController@getSinglePage',
]);

Route::post('/search-restaurants', [
    'uses' => 'RestaurantController@searchRestaurants',
]);

Route::post('/send-otp', [
    'uses' => 'SmsController@sendOtp',
]);
Route::post('/verify-otp', [
    'uses' => 'SmsController@verifyOtp',
]);
Route::post('/check-restaurant-operation-service', [
    'uses' => 'RestaurantController@checkRestaurantOperationService',
]);

Route::post('/get-single-item', [
    'uses' => 'RestaurantController@getSingleItem',
]);

Route::post('/get-all-languages', [
    'uses' => 'LanguageController@getAllLanguages',
]);

Route::post('/get-single-language', [
    'uses' => 'LanguageController@getSingleLanguage',
]);

Route::post('/get-restaurant-category-slides', [
    'uses' => 'RestaurantCategoryController@getRestaurantCategorySlider',
]);

Route::post('/get-all-restaurants-categories', [
    'uses' => 'RestaurantCategoryController@getAllRestaurantsCategories',
]);

Route::post('/get-filtered-restaurants', [
    'uses' => 'RestaurantController@getFilteredRestaurants',
]);

Route::post('/send-password-reset-mail', [
    'uses' => 'PasswordResetController@sendPasswordResetMail',
]);

Route::post('/verify-password-reset-otp', [
    'uses' => 'PasswordResetController@verifyPasswordResetOtp',
]);

Route::post('/change-user-password', [
    'uses' => 'PasswordResetController@changeUserPassword',
]);

Route::post('/check-cart-items-availability', [
    'uses' => 'RestaurantController@checkCartItemsAvailability',
]);

Route::get('/stripe-redirect-capture', [
    'uses' => 'PaymentController@stripeRedirectCapture',
])->name('stripeRedirectCapture');



/* Protected Routes for Loggedin users */
Route::group(['middleware' => ['jwt.auth']], function () {

    Route::post('/apply-coupon', [
        'uses' => 'CouponController@applyCoupon',
    ]);

    Route::post('/save-notification-token', [
        'uses' => 'NotificationController@saveToken',
    ]);

    Route::post('/get-payment-gateways', [
        'uses' => 'PaymentController@getPaymentGateways',
    ]);

    Route::post('/get-addresses', [
        'uses' => 'AddressController@getAddresses',
    ]);
    Route::post('/save-address', [
        'uses' => 'AddressController@saveAddress',
    ]);
    Route::post('/edit-address', [
        'uses' => 'AddressController@editAddress',
    ]);
    Route::post('/delete-address', [
        'uses' => 'AddressController@deleteAddress',
    ]);
    Route::post('/update-user-info', [
        'uses' => 'UserController@updateUserInfo',
    ]);
    Route::post('/check-running-order', [
        'uses' => 'UserController@checkRunningOrder',
    ]);

    Route::group(['middleware' => ['isactiveuser']], function () {
        Route::post('/place-order', [
            'uses' => 'OrderController@placeOrder',
        ]);
    });

    Route::post('/accept-stripe-payment', [
        'uses' => 'PaymentController@acceptStripePayment',
    ]);

    Route::post('/set-default-address', [
        'uses' => 'AddressController@setDefaultAddress',
    ]);
    Route::post('/get-orders', [
        'uses' => 'OrderController@getOrders',
    ]);
    Route::post('/get-order-details', [
        'uses' => 'OrderController@getOrderDetails',
    ]);
    Route::post('/get-order-items', [
        'uses' => 'OrderController@getOrderItems',
    ]);

    Route::post('/cancel-order', [
        'uses' => 'OrderController@cancelOrder',
    ]);

    Route::post('/get-wallet-transactions', [
        'uses' => 'UserController@getWalletTransactions',
    ]);

    Route::post('/get-user-notifications', [
        'uses' => 'NotificationController@getUserNotifications',
    ]);
    Route::post('/mark-all-notifications-read', [
        'uses' => 'NotificationController@markAllNotificationsRead',
    ]);
    Route::post('/mark-one-notification-read', [
        'uses' => 'NotificationController@markOneNotificationRead',
    ]);


    /*#################################### DELIVERY ###################################*/
    Route::post('/delivery/dashboard', ['uses' => 'DeliveryController@dashboard',]);
    Route::post('/delivery/heartbeat', ['uses' => 'DeliveryController@scheduleHeartBeat',]);//NEW
    Route::post('/delivery/update-user-info', ['uses' => 'DeliveryController@updateDeliveryUserInfo', ]);
    Route::post('/delivery/get-delivery-orders', ['uses' => 'DeliveryController@getDeliveryOrders', ]);
    Route::post('/delivery/get-single-delivery-order', ['uses' => 'DeliveryController@getSingleDeliveryOrder',]);
    Route::post('/delivery/set-delivery-guy-gps-location', ['uses' => 'DeliveryController@setDeliveryGuyGpsLocation', ]);
    Route::post('/delivery/get-delivery-guy-gps-location', ['uses' => 'DeliveryController@getDeliveryGuyGpsLocation',]);

    Route::post('/delivery/accept-to-deliver', ['uses' => 'DeliveryController@acceptToDeliver', ]);
    Route::post('/delivery/reached-to-pickup-location', ['uses' => 'DeliveryController@reachedPickUpLocation',]);//NEW
    Route::post('/delivery/pickedup-order', ['uses' => 'DeliveryController@pickedupOrder',]);
    Route::post('/delivery/reached-to-drop-location', ['uses' => 'DeliveryController@reachedDropLocation',]);//NEW
    Route::post('/delivery/deliver-order', ['uses' => 'DeliveryController@deliverOrder',]);
    Route::post('/delivery/send-message', ['uses' => 'DeliveryController@sendMessageToCustomer',]);//NEW
    Route::post('/delivery/logout', ['uses' => 'DeliveryController@logoutDeliveryGuy',]);//NEW
    Route::post('/conversation/chat', ['uses' => 'ChatController@deliveryCustomerChat',]);
    Route::post('/change-avatar', ['uses' => 'UserController@changeAvatar',]);
    Route::post('/check-ban', ['uses' => 'UserController@checkBan',]);
    Route::post('/delivery/get-login-history', ['uses' => 'DeliveryController@getLoginHistory',]);//NEW
});

Route::get('/delivery/get-login-history/{user_id}', ['uses' => 'DeliveryController@getLoginHistory',]);//NEW
Route::get('/delivery/get-trip-details/{order_id}', ['uses' => 'DeliveryController@getTripDetails',]);
Route::get('/delivery/get-trip-summary/{rider_id}', ['uses' => 'DeliveryController@getTripSummary',]);

/* END Protected Routes */

Route::post('/get-coupons', [
    'uses' => 'CouponController@getAllCoupons',
]);

//Route::post('/payment/process-razor-pay', [
//    'uses' => 'PaymentController@processRazorpay',
//]);

Route::get('/payment/process-mercado-pago/{id}', [
    'uses' => 'PaymentController@processMercadoPago',
]);
Route::get('/payment/return-mercado-pago', [
    'uses' => 'PaymentController@returnMercadoPago',
]);

Route::post('/payment/process-paymongo', [
    'uses' => 'PaymentController@processPaymongo',
]);
Route::get('/payment/handle-process-paymongo/{id}', [
    'uses' => 'PaymentController@handlePayMongoRedirect',
]);




Route::group(['middleware' => ['log.route']], function () {
    /* Auth Routes */
    Route::post('/sendOTP', ['uses' => 'UserControllerV1@sendOTP',]);
    Route::post('/verifyOTP', ['uses' => 'UserControllerV1@verifyOTP',]);
    Route::post('/register', ['uses' => 'UserControllerV1@register',]);

//Route::post('/verify-phone', ['uses' => 'UserController@verifyPhone',]);
Route::post('/login-using-otp', ['uses' => 'UserController@loginUsingOtp',]);
//    Route::post('/login-using-otp', ['uses' => 'UserController@verifyOTP',]);
//    Route::post('/login', ['uses' => 'UserController@login',]);
//    Route::post('/register', ['uses' => 'UserController@register',]);
    Route::post('/delivery/login', ['uses' => 'DeliveryController@login',]);
    /* END Auth Routes */
});





/* Resturant-Owner Routes [NEW_UPDATE By ARPAN on 19-Jun-2020]*/
Route::post('/store-owner/login-using-otp', ['uses' => 'RestaurantOwnerApiController@loginUsingOtp',]);
Route::get('/store-owner/dashboard/{user_id}', ['uses' => 'RestaurantOwnerApiController@dashboard',]); 
Route::get('/store-owner/orders/{user_id}', ['uses' => 'RestaurantOwnerApiController@orders',]); 
Route::post('/store-owner/orders/get-new-orders', ['uses' => 'RestaurantOwnerApiController@getNewOrders',]); 
Route::post('/store-owner/orders/get-accepted-orders', ['uses' => 'RestaurantOwnerApiController@getAllAcceptedOrders',]); 
Route::post('/store-owner/orders/running-orders', ['uses' => 'RestaurantOwnerApiController@getRunningOrders',]); //NEW
Route::post('/store-owner/orders/accept-order', ['uses' => 'RestaurantOwnerApiController@acceptOrder',]); 
Route::post('/store-owner/orders/mark-order-ready', ['uses' => 'RestaurantOwnerApiController@markOrderReady',]); 
Route::post('/store-owner/orders/mark-selfpickup-order-completed', ['uses' => 'RestaurantOwnerApiController@markSelfPickupOrderAsCompleted',]); //////////////////
Route::post('/store-owner/orders/cancel-order', ['uses' => 'RestaurantOwnerApiController@cancelOrder',]); 
Route::post('/store-owner/orders/searchOrders', ['uses' => 'RestaurantOwnerApiController@postSearchOrders',]); 
Route::post('/store-owner/orders/details', ['uses' => 'RestaurantOwnerApiController@viewOrder',]); 
Route::get('/store-owner/stores/{user_id}', ['uses' => 'RestaurantOwnerApiController@restaurants',]); 
Route::post('/store-owner/store/edit', ['uses' => 'RestaurantOwnerApiController@getEditRestaurant',]); 
Route::post('/store-owner/store/new/save', ['uses' => 'RestaurantOwnerApiController@saveNewRestaurant',]); 
Route::post('/store-owner/store/disable', ['uses' => 'RestaurantOwnerApiController@disableRestaurant',]); 
Route::post('/store-owner/store/edit/save', ['uses' => 'RestaurantOwnerApiController@updateRestaurant',]); 
//Route::post('/store-owner/store/schedule/save', ['uses' => 'RestaurantOwnerApiController@updateRestaurantScheduleData',]);
Route::get('/store-owner/itemcategories/{user_id}',  ['uses' => 'RestaurantOwnerApiController@itemcategories',]); 
Route::post('/store-owner/itemcategories/new/save',  ['uses' => 'RestaurantOwnerApiController@createItemCategory',]); 
Route::post('/store-owner/itemcategory/disable',  ['uses' => 'RestaurantOwnerApiController@disableCategory',]); 
Route::get('/store-owner/items/{user_id}', ['uses' => 'RestaurantOwnerApiController@items',]); 
Route::post('/store-owner/stores/searchItems', ['uses' => 'RestaurantOwnerApiController@searchItems',]); 
Route::post('/store-owner/item/disable', ['uses' => 'RestaurantOwnerApiController@disableItem',]);
//Route::post('/store-owner/items/edit', ['uses' => 'RestaurantOwnerApiController@getEditItem',]);
//Route::post('/store-owner/item/edit/save', ['uses' => 'RestaurantOwnerApiController@updateItem',]);
//Route::post('/store-owner/item/new/save', ['uses' => 'RestaurantOwnerApiController@saveNewItem',]);
Route::get('/store-owner/earnings/{user_id}/{restaurant_id?}', ['uses' => 'RestaurantOwnerApiController@earnings',]); 
//Route::post('/store-owner/earnings/send-payout-request', ['uses' => 'RestaurantOwnerApiController@sendPayoutRequest',]);

