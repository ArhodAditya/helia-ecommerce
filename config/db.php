<?php
$envFile = __DIR__ . '/env.php';
$env = file_exists($envFile) ? require $envFile : [];

$serverName = $_SERVER['SERVER_NAME'] ?? 'localhost';
$isLocal = in_array($serverName, ['localhost', '127.0.0.1', '::1']);

if ($isLocal) {
    $host   = $env['LOCAL_HOST'] ?? 'localhost';
    $dbName = $env['LOCAL_NAME'] ?? 'helia_db';
    $dbUser = $env['LOCAL_USER'] ?? 'root';
    $dbPass = $env['LOCAL_PASS'] ?? '';
} else {
    $host   = $env['LIVE_HOST'] ?? '';
    $dbName = $env['LIVE_NAME'] ?? '';
    $dbUser = $env['LIVE_USER'] ?? '';
    $dbPass = $env['LIVE_PASS'] ?? '';
}

date_default_timezone_set('Asia/Dhaka');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    die('Database connection failed.');
}

$adminCount = $pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn();

if ($adminCount == 0 && !empty($env['ADMIN_EMAIL']) && !empty($env['ADMIN_PASS'])) {
    $stmt = $pdo->prepare('INSERT INTO admins (name, email, password) VALUES (?, ?, ?)');
    $stmt->execute(['Admin', $env['ADMIN_EMAIL'], password_hash($env['ADMIN_PASS'], PASSWORD_DEFAULT)]);
}

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}