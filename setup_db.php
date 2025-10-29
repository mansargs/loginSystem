<?php
require_once __DIR__ . '/config.php';

$serverConn = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS']);
if ($serverConn->connect_error) {
    die('MySQL server connection failed: ' . $serverConn->connect_error);
}

$dbExisted = false;
$checkDbSql = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$_ENV[DB_NAME]'";
$checkDbRes = $serverConn->query($checkDbSql);

if ($checkDbRes) {
    $dbExisted = $checkDbRes->num_rows > 0;
    $checkDbRes->free();
}

$createDbSql = "CREATE DATABASE IF NOT EXISTS `$_ENV[DB_NAME]` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if (!$serverConn->query($createDbSql)) {
    $serverConn->close();
    die('Failed to create database: ' . $serverConn->error);
}

echo $dbExisted ? "Database '$_ENV[DB_NAME]' already exists.\n" : "Database '$_ENV[DB_NAME]' created successfully.\n";

$conn = new mysqli($_ENV['DB_HOST'], $_ENV['DB_USER'], $_ENV['DB_PASS'], $_ENV['DB_NAME']);
if ($conn->connect_error) {
    $serverConn->close();
    die('Database connection failed: ' . $conn->connect_error);
}
$conn->set_charset('utf8mb4');

$tableExisted = false;
$checkTableSql = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES
                  WHERE TABLE_SCHEMA = '$_ENV[DB_NAME]' AND TABLE_NAME = 'users'";
$checkTableRes = $conn->query($checkTableSql);

if ($checkTableRes) {
    $tableExisted = $checkTableRes->num_rows > 0;
    $checkTableRes->free();
}

$createTableSql = <<<SQL
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(191) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    verified TINYINT(1) DEFAULT 0,
    verification_token VARCHAR(255) NULL,
    verified_at TIMESTAMP NULL DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL;

if (!$conn->query($createTableSql)) {
    $conn->close();
    $serverConn->close();
    die('Failed to create table: ' . $conn->error);
}

echo $tableExisted ? "Table 'users' already exists.\n" : "Table 'users' created successfully.\n";

