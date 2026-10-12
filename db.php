<?php
// config/db.php

$host     = '127.0.0.1';
$port     = '3306';
$dbname   = 'college_erp';
$username = 'dbviewer';
$password = 'dbviewer';

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::ATTR_PERSISTENT         => true, // Persistent connection phone par handshake delay khatam karta hai
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
];

try {
    $db = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password, $options);
} catch (PDOException $e) {
    die("<div style='font-family:sans-serif;padding:15px;background:#fee2e2;color:#991b1b;border-radius:10px;margin:20px auto;max-width:500px;'>
            <h4 style='margin:0 0 8px;'>Database Connection Error</h4>
            <p style='margin:0;font-size:14px;'>" . htmlspecialchars($e->getMessage()) . "</p>
            <small style='display:block;margin-top:8px;color:#7f1d1d;'>Check karein ki MariaDB/MySQL app background me running hai.</small>
         </div>");
}
?>
