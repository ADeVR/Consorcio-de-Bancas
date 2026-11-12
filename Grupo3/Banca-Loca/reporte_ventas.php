<?php

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/controladores/VentasController.php';

if (!isset($conexion) || !($conexion instanceof mysqli)) {
    exit('Error: conexión MySQL no disponible.');
}

$controlador = new VentasController();

$controlador->index($conexion);