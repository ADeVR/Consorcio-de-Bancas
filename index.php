
<?php
session_start();

$rutaSeguridad = "/ConsorcioDeBancas/Seguridad/php";

// Si no ha iniciado sesión, ir al login.
if (empty($_SESSION["usuario_id"])) {
    header("Location: " . $rutaSeguridad . "/login.php");
    exit;
}

// Comprobar que el rol sea válido.
$rol = $_SESSION["rol"] ?? "";

if (!in_array($rol, ["admin", "agente"], true)) {
    $_SESSION = [];
    session_destroy();

    header("Location: " . $rutaSeguridad . "/login.php");
    exit;
}

// Ambos roles entran al panel principal.
// El panel decidirá qué opciones mostrar.
header("Location: " . $rutaSeguridad . "/inicio.php");
exit;
