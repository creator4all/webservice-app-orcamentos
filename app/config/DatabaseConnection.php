<?php

namespace App\Config;
use PDO;

final class DatabaseConnection extends PDO {
    private static PDO $connection;
    private function __construct(){
        $host = $_ENV['DB_HOST'] ?? 'db';
        $port = $_ENV['DB_PORT'] ?? '3306';
        $dbname = $_ENV['DB_DATABASE'] ?? 'webservice';
        $username = $_ENV['DB_USERNAME'] ?? 'user';
        $password = $_ENV['DB_PASSWORD'] ?? 'password';
        
        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8";
        
        $this->connection = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
    }

    public static function getInstance(): DatabaseConnection{
        if(self::$connection === null){
            self::$connection = new self();
        }
        return self::$connection;
    }
}