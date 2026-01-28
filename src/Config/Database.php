<?php
/**
 * Database connection management
 */

namespace App\Config;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    public static function connect(): PDO
    {
        if (self::$instance === null) {
            try {
                $config = [
                    'host' => Config::get('db.host'),
                    'database' => Config::get('db.database'),
                    'user' => Config::get('db.user'),
                    'password' => Config::get('db.password'),
                    'charset' => Config::get('db.charset'),
                ];

                $dsn = sprintf(
                    "mysql:host=%s;dbname=%s;charset=%s",
                    $config['host'],
                    $config['database'],
                    $config['charset']
                );

                self::$instance = new PDO(
                    $dsn,
                    $config['user'],
                    $config['password'],
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );

            } catch (PDOException $e) {
                throw new PDOException("Database connection error: " . $e->getMessage());
            }
        }

        return self::$instance;
    }

    public static function getInstance(): PDO
    {
        return self::$instance ?? self::connect();
    }
}
