<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use App\Core\Exceptions\HttpException;

final class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $url = Env::get('DATABASE_URL') ?? Env::get('MYSQL_URL');
        if (is_string($url) && $url !== '') {
            $parts = parse_url($url);
            if (is_array($parts)) {
                $host = (string)($parts['host'] ?? '127.0.0.1');
                $port = (string)($parts['port'] ?? '3306');
                $db = ltrim((string)($parts['path'] ?? ''), '/');
                $user = (string)($parts['user'] ?? '');
                $pass = (string)($parts['pass'] ?? '');
            }
        }

        $host = $host ?? (Env::get('DB_HOST', '127.0.0.1') ?? '127.0.0.1');
        $port = $port ?? (Env::get('DB_PORT', '3306') ?? '3306');
        $db = $db ?? (Env::get('DB_NAME', '') ?? '');
        $user = $user ?? (Env::get('DB_USER', '') ?? '');
        $pass = $pass ?? (Env::get('DB_PASS', '') ?? '');

        if ($db === '' || $user === '') {
            throw new HttpException(500, 'Base de datos no configurada');
        }

        $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";

        try {
            self::$pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            throw new HttpException(500, 'No se pudo conectar a la base de datos');
        }

        return self::$pdo;
    }
}
