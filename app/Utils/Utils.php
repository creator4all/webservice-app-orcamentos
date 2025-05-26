<?php

namespace App\Utils;

class Utils{
    public static function valid_form(array $data, array $required_fields): bool{
        foreach($required_fields as $field){
            if(!in_array($field, $data)){
                return false;
            }
        }
        return true;
    }

    public static function user_role($role_user, array $role_verify): bool
    {
        if(!empty($role_user) && !empty($role_verify)){
            if(!in_array($role_user, $role_verify)){
                return false;
            }
            return true;
        }
        return false;
    }
}