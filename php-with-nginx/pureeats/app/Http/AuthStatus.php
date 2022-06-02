<?php

class AuthStatus{
    const CODE_SEND = 'CODE_SEND'; //'next_link' => '/login-using-otp',
    const VERIFIED = 'VERIFIED';//'next_link' => '/home',
    const AUTHENTICATED = 'AUTHENTICATED'; //'next_link' => '/home',
    const REGISTRATION_REQUIRED = 'REGISTRATION_REQUIRED';//'next_link' => '/register',
    const REGISTERED = 'REGISTERED';//'next_link' => '/login-using-otp
}

?>