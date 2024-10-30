<?php

class IpayUtils{

    public function __construct(){
        /**
         * Empty constructor
         */
    }

    public static function generate_random($length){

        $char_set = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';

        for($i=0; $i<$length; $i++){
            $index = rand(0, strlen($char_set)-1);
            $randomString .= $char_set[$index];
        }

        return $randomString;

    }
    
}