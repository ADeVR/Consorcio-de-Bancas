
<?php

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/controladores/ReportePagosController.php';

if (!isset($conexion) || !($conexion instanceof mysqli)) {
    exit('Error: conexión MySQL no disponible.');
}

$controlador = new ReportePagosController();

$controlador->index($conexion);
