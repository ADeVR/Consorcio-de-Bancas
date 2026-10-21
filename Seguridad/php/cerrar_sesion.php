<?php
require_once __DIR__ . "/conexion.php";
require_once __DIR__ . "/funciones.php";
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !csrfValido()) {
    http_response_code(403);
    exit("Solicitud no válida.");
}
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $p = session_get_cookie_params();
    setcookie(session_name(), "", ["expires" => time() - 42000, "path" => $p["path"], "domain" => $p["domain"], "secure" => $p["secure"], "httponly" => $p["httponly"], "samesite" => $p["samesite"] ?? "Lax"]);
}
session_destroy();
header("Location: /Seguridad/php/login.php");
exit;
