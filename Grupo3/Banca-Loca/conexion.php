<?php

mysqli_report(MYSQLI_REPORT_OFF);

$config = require __DIR__ . '/config.local.php';

$conexion = new mysqli(
    $config['DB_HOST'],
    $config['DB_USER'],
    $config['DB_PASSWORD'],
    $config['DB_NAME'],
    $config['DB_PORT']
);

if ($conexion->connect_error) {
    error_log("Error de conexión MySQL: " . $conexion->connect_error);
    http_response_code(500);
    exit("No fue posible conectar con la base de datos.");
}

$conexion->set_charset("utf8mb4");