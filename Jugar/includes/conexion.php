<?php
// Usa la conexión del grupo (variables DB_* del .htaccess), igual que el login.
// Este archivo abre la sesión y crea $conexion.
require_once __DIR__ . '/../../Seguridad/php/conexion.php';

date_default_timezone_set('America/Santo_Domingo');
$pdo = $conexion;   // el resto del módulo usa $pdo