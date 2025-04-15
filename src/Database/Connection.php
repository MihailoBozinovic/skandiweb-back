<?php
namespace App\Database;

use PDO;
use PDOException;

class Connection {
    private static ?PDO $pdo = null;

    public static function getInstance(): PDO {
        if (self::$pdo === null) {
            $host = $_ENV['DB_HOST'];
            $port = $_ENV['DB_PORT'];
            $dbname = $_ENV['DB_NAME'];
            $username = $_ENV['DB_USER'];
            $password = $_ENV['DB_PASS'];
            $charset = $_ENV['DB_CHARSET'];

            $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

            try {
                self::$pdo = new PDO($dsn, $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ]);
            } catch (PDOException $e) {
                throw new \RuntimeException('Database connection error: ' . $e->getMessage());
            }
        }

        return self::$pdo;
    }
}