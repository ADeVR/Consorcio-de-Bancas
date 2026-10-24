
<?php
require_once __DIR__ . "/conexion.php";

// Eliminar los datos de la sesión.
$_SESSION = [];

// Eliminar la cookie de sesión si se está utilizando.
if (ini_get("session.use_cookies")) {
    $parametros = session_get_cookie_params();

    setcookie(
        session_name(),
        "",
        time() - 42000,
        $parametros["path"],
        $parametros["domain"],
        $parametros["secure"],
        $parametros["httponly"]
    );
}

// Destruir la sesión.
session_destroy();

// Volver al login 
header("Location: /ConsorcioDeBancas/Seguridad/php/login.php");
exit;
