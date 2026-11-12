
<?php

/**
 * Controlador principal del módulo de reportes.
 */

class ReportesController
{
    /**
     * Mostrar el centro principal de reportes.
     */
    public function index()
    {
        $vista = dirname(__DIR__) . '/vistas/index.php';

        if (!file_exists($vista)) {
            http_response_code(500);
            exit('Error: no se encontró la vista vistas/index.php');
        }

        require $vista;
    }
}
