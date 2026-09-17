<?php
/**
 * Database Connection & Auto-Migration Handler
 * Connects via PDO (MySQL primary, SQLite fallback)
 */

require_once __DIR__ . '/config.php';

function getDB(): PDO {
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $mysqlError = null;

    // 1. Attempt MySQL Connection
    try {
        // First connect to MySQL server without database specified (to create db if needed)
        $dsnWithoutDb = sprintf('mysql:host=%s;port=%s;charset=%s', DB_HOST, DB_PORT, DB_CHARSET);
        $serverPdo = new PDO($dsnWithoutDb, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 3, // Fail fast if MySQL is offline
        ]);

        // Auto-create database if not exists
        $serverPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET " . DB_CHARSET . " COLLATE utf8mb4_unicode_ci");

        // Now connect directly to the target database
        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', DB_HOST, DB_PORT, DB_NAME, DB_CHARSET);
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        // Auto-create table if not exists
        $createTableSql = "
            CREATE TABLE IF NOT EXISTS `responses` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `choice` VARCHAR(10) NOT NULL,
                `message` TEXT NULL,
                `visitor_ip` VARCHAR(45) NULL,
                `user_agent` TEXT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        $pdo->exec($createTableSql);

        return $pdo;
    } catch (PDOException $e) {
        $mysqlError = $e->getMessage();
    }

    // 2. Fallback to SQLite if MySQL failed and fallback is enabled
    if (ENABLE_SQLITE_FALLBACK) {
        try {
            $sqliteDsn = 'sqlite:' . SQLITE_FILE;
            $pdo = new PDO($sqliteDsn, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

            // Auto-create SQLite table if not exists
            $createSqliteTable = "
                CREATE TABLE IF NOT EXISTS responses (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    choice TEXT NOT NULL,
                    message TEXT,
                    visitor_ip TEXT,
                    user_agent TEXT,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );
            ";
            $pdo->exec($createSqliteTable);

            return $pdo;
        } catch (PDOException $e) {
            throw new Exception("Database connection failed. MySQL error: " . $mysqlError . " | SQLite error: " . $e->getMessage());
        }
    }

    throw new Exception("MySQL connection failed: " . $mysqlError);
}
