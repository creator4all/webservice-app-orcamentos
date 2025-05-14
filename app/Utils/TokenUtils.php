<?php
namespace App\Utils;

class TokenUtils {
    private function __construct() 
    {
    }
    
    /**
     * Generate a random numeric OTP with specified length
     */
    public static function generateOTP($length = 6) {
        $digits = '';
        for ($i = 0; $i < $length; $i++) {
            $digits .= rand(0, 9);
        }
        return $digits;
    }
}
