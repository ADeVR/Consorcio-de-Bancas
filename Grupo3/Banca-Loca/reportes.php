
<?php

/**
 * Punto de entrada del módulo de reportes.
 */

require_once __DIR__ . '/controladores/ReportesController.php';

$controlador = new ReportesController();

$controlador->index();

