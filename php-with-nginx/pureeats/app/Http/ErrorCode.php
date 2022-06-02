<?php

/**
 * Created By Arpan, 09-Jan-2021
 */
class ErrorCode{
    //CustomerApp ClientSide Errors:
    const BAD_REQUEST = 'C0001';
    const NO_INTERNET = 'C0002';
    const DATA_CONVERTION_FAILURE = 'C0003';
    const BAD_RESPONSE = 'C0004';

    // Authentication
    const INVALID_REQUEST_BODY = 'E1001';
    const INCORRECT_DATA_TYPE= 'E1002';
    const INVALID_AUTH_TOKEN = 'E1003';
    const PHONE_NOT_EXIST = 'E1004';
    const DUPLICATE_MOBILE_NUMBER = 'E1005';
    const DUPLICATE_EMAIL_ID = 'E1006';
    const SUSPENDED_USER = 'E1007';
    const ACCOUNT_BLOCKED = 'E1008';
    const MAXIMUM_ATTEMPT_REACH = 'E1009';
    const INVALID_PUSH_TOKEN = 'E1010';
    const REGISTRATION_NOT_POSSIBLE = 'E1011';
    const TOKEN_GENERATION_FAILED = 'E1012';
    const INVALID_AUTH_PROVIDER = 'E1013';
    const INVALID_OTP = 'E1014';
    const USER_EXIST = 'E1015';


    
    //Order
    const INVALID_RESTAURANT_ID = 'E2001';
    const UNSUPPORTED_DELIVERY_TYPE = 'E2002';//
    const INVALID_LOCATION = 'E2003';
    const INVALID_ADDRESS = 'E2004';
    const UNSUPPORTED_PAYMENT_METHOD = 'E2005';//
    const INVALID_PAYMENT_REFERRENCE_NUMBER = 'E2006';//
    const INVALID_COUPON = 'E2007';
    const ADDRESS_NOT_OPERATIONAL= 'E2008';
    const PAYMENT_METHOD_NOT_AVAILABLE= 'E2009';
    const TAX_AMOUNT_MISSMATCH= 'E2010';
    const RESTAURANT_CHARGE_MISSMATCH= 'E2011';
    const PAYABLE_AMOUNT_MISSMATCH= 'E2012';
    const PAYABLE_DELIVERY_CHARGE_MISSMATCH= 'E2013';
    const ORDER_AMOUNT_NOT_PERMIT = 'E2014';
    const RESTAURANT_NOT_OPERATIONAL = 'E2015';
    const OPERATION_ALREADY_COMPLETED = 'E2016';
    const STORE_CLOSED = 'E2017';


    //Payment
    const UPI_PAYMENT_VERIFICATION_FAILED = 'E5001';

}

?>