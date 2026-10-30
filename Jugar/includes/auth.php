<?php
// Sesión y permisos reales: usa el login del grupo (carpeta Seguridad).
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/../../Seguridad/php/funciones.php';

// Ruta web a Seguridad, sacada de la URL actual.
// Sirve con el Alias (/ConsorcioDeBancas), en local sin alias y en el hosting.
$urlScript     = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
$pos           = stripos($urlScript, '/Jugada/');
$baseWeb       = ($pos !== false) ? substr($urlScript, 0, $pos) : '';
$rutaSeguridad = $baseWeb . '/Seguridad/php';

$esApi = strpos($urlScript, '/api/') !== false;

// 1) Debe haber iniciado sesión
if (empty($_SESSION['usuario_id'])) {
    if ($esApi) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'error' => 'Sesión expirada. Inicie sesión de nuevo.']);
    } else {
        header('Location: ' . $rutaSeguridad . '/login.php');
    }
    exit;
}

// 2) Debe tener permiso para el módulo Jugar (el admin entra siempre)
if (!tienePermisoModulo($conexion, 'Jugar')) {
    http_response_code(403);
    if ($esApi) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'error' => 'No tiene permiso para el módulo Jugar.']);
    } else {
        echo 'Acceso denegado: no tienes permiso para entrar a este módulo.';
    }
    exit;
}