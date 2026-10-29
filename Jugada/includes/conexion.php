<?php
date_default_timezone_set('America/Santo_Domingo');
$c = require __DIR__ . '/config.php';
try {
    $pdo = new PDO(
        "mysql:host={$c['host']};dbname={$c['db']};charset=utf8mb4",
        $c['user'], $c['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (PDOException $e) {
    die('Error de conexión a la base de datos.');
}