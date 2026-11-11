
<?php

/**
 * ======================================================
 * PUNTO DE ENTRADA - REPORTE DE TICKETS
 * Sistema Banca Loca
 * ======================================================
 */

// Conexión a la base de datos.
require_once __DIR__ . '/conexion.php';

// Controlador de tickets.
require_once __DIR__ . '/controladores/ReporteTicketsController.php';

// Verificar conexión.
if (!isset($conexion) || !($conexion instanceof mysqli)) {
    http_response_code(500);
    exit('Error: no se pudo establecer la conexión con MySQL.');
}

// Ejecutar controlador.
$controlador = new ReporteTicketsController();

$controlador->index($conexion);
