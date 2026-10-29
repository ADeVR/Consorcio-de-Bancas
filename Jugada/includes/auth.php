<?php
session_start();

// TEMPORAL: sesión de prueba hasta integrar el login real del grupo.
// Cuando integren, se borra TODO este bloque y se usa la sesión del login.
require_once __DIR__ . '/conexion.php';
$st = $pdo->prepare("SELECT id, usuario, rol FROM usuarios WHERE usuario = ?");
$st->execute(['Cristofer']);
$u = $st->fetch();
if (!$u) {
    die('Usuario de prueba no encontrado en la base de datos local.');
}
$_SESSION['usuario_id'] = $u['id'];
$_SESSION['usuario']    = $u['usuario'];
$_SESSION['rol']        = $u['rol'];