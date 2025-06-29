<?php

namespace App\Database;

use PDO;
use PDOException;


class Database
{
    private static ?PDO $instance = null;
    private static array $config = [];

    public static function setConfig(array $config): void
    {

        self::$config = $config;
    }

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            if (empty(self::$config)) {
                throw new PDOException("Database configuration not set. Call Database::setConfig() first.");
            }

            $dsn = "mysql:host=" . self::$config['host'] . ";dbname=" . self::$config['database'] . ";charset=" . (self::$config['charset'] ?? 'utf8mb4');
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dsn, self::$config['username'], self::$config['password'], $options);
            } catch (PDOException $e) {
                die("Error de conexión a la base de datos: " . $e->getMessage());
            }
        }

        return self::$instance;
    }


    private function __clone() {}
    public function __wakeup() {}
}
