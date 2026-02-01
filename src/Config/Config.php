<?php
/**
 * Centralized application configuration
 */

namespace App\Config;

class Config
{
    private static array $config = [];

    public static function init(): void
    {
        self::$config = [
            'db' => [
                'host' => getenv('MYSQL_HOST') ?: 'localhost',
                'port' => (int)(getenv('MYSQL_PORT') ?: 3306),
                'user' => getenv('MYSQL_USER') ?: 'root',
                'password' => getenv('MYSQL_PASSWORD') ?: '',
                'database' => getenv('MYSQL_DATABASE') ?: 'todo_db',
                'charset' => getenv('MYSQL_CHARSET') ?: 'utf8mb4',
            ],
            'app' => [
                'env' => getenv('APP_ENV') ?: 'development',
                'debug' => filter_var(getenv('APP_DEBUG') ?: false, FILTER_VALIDATE_BOOLEAN),
                'timezone' => getenv('APP_TIMEZONE') ?: 'UTC',
            ],
            'date' => [
                'format' => getenv('DATE_FORMAT') ?: 'M d, Y - H:i',
                'timezone' => getenv('DATE_TIMEZONE') ?: 'UTC',
            ],
            'server' => [
                'host' => getenv('SERVER_HOST') ?: 'localhost',
                'port' => (int)(getenv('SERVER_PORT') ?: 8080),
            ]
        ];
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::$config = self::$config ?: [];
        
        $keys = explode('.', $key);
        $value = self::$config;
        
        foreach ($keys as $k) {
            if (is_array($value) && isset($value[$k])) {
                $value = $value[$k];
            } else {
                return $default;
            }
        }
        
        return $value;
    }
}

Config::init();
