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
                    'port' => Config::get('db.port', 3306),
                    'database' => Config::get('db.database'),
                    'user' => Config::get('db.user'),
                    'password' => Config::get('db.password'),
                    'charset' => Config::get('db.charset'),
                ];

                // Validate required configuration
                if (empty($config['host']) || empty($config['database']) || empty($config['user'])) {
                    throw new PDOException("Database configuration is incomplete. Please check environment variables.");
                }

                $dsn = sprintf(
                    "mysql:host=%s;port=%d;dbname=%s;charset=%s",
                    $config['host'],
                    $config['port'],
                    $config['database'],
                    $config['charset']
                );

                self::$instance = new PDO(
                    $dsn,
                    $config['user'],
                    $config['password'],
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );

            } catch (PDOException $e) {
                error_log('Database connection error: ' . $e->getMessage());
                throw new PDOException("Unable to connect to database. Please check your configuration.");
            }
        }

        return self::$instance;
    }

    public static function getInstance(): PDO
    {
        return self::connect();
    }
}
