<?php
namespace App\DAO;

use PDO;
use PDOException;

class Connection {
    /**
     * Retorna uma conexão PDO com o banco de dados
     * @return PDO
     */
    public static function db() {
        try {
            $host = $_ENV['DB_HOST'] ?? 'db';
            $port = $_ENV['DB_PORT'] ?? '3306';
            $dbname = $_ENV['DB_DATABASE'] ?? 'webservice';
            $username = $_ENV['DB_USERNAME'] ?? 'user';
            $password = $_ENV['DB_PASSWORD'] ?? 'password';
            
            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8";
            
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
            
            return $pdo;
        } catch (PDOException $e) {
            throw new \Exception("Erro de conexão com o banco de dados: " . $e->getMessage());
        }
    }
}
