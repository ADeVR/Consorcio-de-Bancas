<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set("session.use_strict_mode", "1");
    session_set_cookie_params(["httponly" => true, "secure" => !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off", "samesite" => "Lax"]);
    session_start();
}
$servidor = "localhost";
$baseDatos = "consorcio_bancas";
$usuarioDB = "root";
$claveDB = "";
try {
    $conexion = new PDO("mysql:host=$servidor;dbname=$baseDatos;charset=utf8mb4", $usuarioDB, $claveDB, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    exit("Error de conexión. Revisa MySQL y la configuración de php/conexion.php.");
}
