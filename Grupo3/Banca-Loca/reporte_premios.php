
<?php

require_once __DIR__ . '/conexion.php';

require_once __DIR__ . '/controladores/ReportePremiosController.php';

if (!isset($conexion) || !($conexion instanceof mysqli)) {
    http_response_code(500);
    exit('Error: no se pudo establecer la conexión con MySQL.');
}

$controlador = new ReportePremiosController();

$controlador->index($conexion);
