<?php
/**
 * Database: crea UNA sola conexión PDO a MySQL (patrón Singleton).
 * PDO con sentencias preparadas evita la inyección SQL (RNF-07).
 */
class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            $c = require __DIR__ . '/../../config/config.php';
            $dsn = "mysql:host={$c['db_host']};dbname={$c['db_name']};charset=utf8mb4";
            self::$pdo = new PDO($dsn, $c['db_user'], $c['db_pass'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // errores como excepciones
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // filas como arreglos asociativos
                PDO::ATTR_EMULATE_PREPARES   => false,                    // preparación real en el servidor
            ]);
        }
        return self::$pdo;
    }
}
