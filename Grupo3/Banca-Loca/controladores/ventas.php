
<?php

/**
 * ======================================================
 * PUNTO DE ENTRADA - REPORTE DE VENTAS
 * Sistema Banca Loca
 * ======================================================
 */

// Conexión a la base de datos.
require_once dirname(__DIR__) . '/conexion.php';

// Controlador de ventas.
require_once __DIR__ . '/controladores/VentasController.php';

// Verificar conexión.
if (!isset($conexion) || !($conexion instanceof mysqli)) {
    http_response_code(500);
    exit('Error: no se pudo establecer la conexión con MySQL.');
}

// Ejecutar controlador.
$controlador = new VentasController();

$controlador->index($conexion);

