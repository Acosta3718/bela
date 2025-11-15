<?php

namespace App\Core;

use PDO;

class Database
{
    private static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        if (static::$pdo) {
            return static::$pdo;
        }

        $config = require __DIR__ . '/../../config/database.php';
        $dsn = sprintf(
            '%s:host=%s;port=%s;dbname=%s;charset=%s',
            $config['driver'],
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset']
        );

        static::$pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);
        return static::$pdo;
    }
}