
<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$host = getenv('DB_HOST');
$dbname = getenv('DB_NAME');
$user = getenv('DB_USER');
$password = getenv('DB_PASSWORD');

if (
    !$host ||
    !$dbname ||
    !$user ||
    $password === false ||
    $password === ''
) {
    error_log('Falta configuración de MySQL.');
    http_response_code(500);
    exit('No se pudo conectar con la base de datos.');
}

try {
    $conexion = new PDO(
        "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    error_log('Error de conexión con MySQL.');
    http_response_code(500);
    exit('No se pudo conectar con la base de datos.');
}
