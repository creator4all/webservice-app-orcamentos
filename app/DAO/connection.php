<?php

class Connection{
    private $db;
    private function __construct() {
        $this->db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_DATABASE, DB_USERNAME, DB_PASSWORD);
    }

    public static function db() {
        if ($db == null) {
            $db = new self();
        }
        return $db;
    }
}