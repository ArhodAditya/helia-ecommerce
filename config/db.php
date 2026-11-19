<?php
$serverName = $_SERVER['SERVER_NAME'] ?? 'localhost';
$isLocal = in_array($serverName, ['localhost', '127.0.0.1', '::1']);

if ($isLocal) {
    $host = 'localhost';
    $dbName = 'helia_db';
    $dbUser = 'root';
    $dbPass = '';
} else {
    $host = 'sql111.infinityfree.com';
    $dbName = 'if0_43475130_helia_db';
    $dbUser = 'if0_43475130';
    $dbPass = 'B0521T10AM';
}

date_default_timezone_set('Asia/Dhaka');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

$adminCount = $pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn();

if ($adminCount == 0) {
    $stmt = $pdo->prepare('INSERT INTO admins (name, email, password) VALUES (?, ?, ?)');
    $stmt->execute(['Admin', 'admin@helia.com', password_hash('admin123', PASSWORD_DEFAULT)]);
}

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
