<?php
// Database.php

class Database {
    private static ?PDO $instance = null;

    private function __construct() {
        // Mencegah pembuatan objek langsung
    }

    // Metode untuk mendapatkan koneksi (Singleton Pattern)
    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $host = 'localhost';
            $db   = 'toko_online';
            $user = 'root';
            $pass = ''; 
            $charset = 'utf8mb4';

            $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            
            try {
                 self::$instance = new PDO($dsn, $user, $pass, $options);
            } catch (\PDOException $e) {
                 throw new \PDOException($e->getMessage(), (int)$e->getCode());
            }
        }
        return self::$instance;
    }

    // Metode untuk menjalankan query (INSERT, UPDATE, DELETE)
    public static function execute(string $sql, array $params): bool {
        $stmt = self::getConnection()->prepare($sql);
        return $stmt->execute($params);
    }
}