<?php
if (!defined('DB_HOST')) {
    $cfgPath = __DIR__ . '/config.php';
    if (file_exists($cfgPath)) require_once $cfgPath;
    else die(json_encode(['ok'=>false,'error'=>'config.php not found. See config.sample.php.']));
}

function getPDO(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;
    // charset removed from DSN — set via SET NAMES instead (avoids HY000 2019 on Hostinger MySQL)
    $dsn = 'mysql:host='.DB_HOST.';dbname='.DB_NAME;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    $pdo->exec('SET NAMES utf8mb4');
    return $pdo;
}
