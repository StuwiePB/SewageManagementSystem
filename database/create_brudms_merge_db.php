<?php

/**
 * One-off: create MySQL database if missing (run: php database/create_brudms_merge_db.php).
 */
$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: '3306';
$user = getenv('DB_USERNAME') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: '';
$name = 'brudms-merge';

$pdo = new PDO(
    "mysql:host={$host};port={$port}",
    $user,
    $pass,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
$pdo->exec(
    'CREATE DATABASE IF NOT EXISTS `'.$name.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
);
echo "Database `{$name}` is ready.\n";
