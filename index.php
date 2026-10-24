<?php
session_start();

$rutaSeguridad = "/ConsorcioDeBancas/Seguridad/php";

// Si no ha iniciado sesión, enviarlo al login.
if (empty($_SESSION["usuario_id"])) {
    header("Location: " . $rutaSeguridad . "/login.php");
    exit;
}

// Comprobar el rol del usuario.
$rol = $_SESSION["rol"] ?? "";

if ($rol === "admin") {
    header("Location: " . $rutaSeguridad . "/usuarios.php");
    exit;
}

if ($rol === "agente") {
    header("Location: " . $rutaSeguridad . "/inicio.php");
    exit;
}

// Si el rol no es válido, cerrar la sesión.
$_SESSION = [];
session_destroy();

header("Location: " . $rutaSeguridad . "/login.php");
exit;
