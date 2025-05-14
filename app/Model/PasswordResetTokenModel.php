<?php
namespace App\Model;

class PasswordResetTokenModel {
    public $id;
    public $usuarios_id;
    public $token;
    public $created_at;
    public $expires_at;
    public $used;

    public function __construct($data = []) {
        foreach ($data as $key => $value) {
            if(property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public function isExpired(): bool {
        return strtotime($this->expires_at) < time();
    }

    public function isUsed(): bool {
        return (bool)$this->used;
    }
}
