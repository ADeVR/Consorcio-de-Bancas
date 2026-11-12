
<?php

/**
 * ==========================================================
 * CONTROLADOR DEL REPORTE GENERAL
 * Sistema Banca Loca
 * ==========================================================
 */

require_once dirname(__DIR__) . '/ReporteGeneralModel.php';

class ReporteGeneralController
{
    /**
     * VALIDAR FORMATO DE FECHA
     */
    private function validarFecha($fecha): bool
    {
        if (!is_string($fecha)) {
            return false;
        }

        $formato = DateTime::createFromFormat('!Y-m-d', $fecha);

        return $formato !== false
            && $formato->format('Y-m-d') === $fecha;
    }

    /**
     * MOSTRAR REPORTE GENERAL
     */
    public function index(mysqli $conexion): void
    {
        // 1. Valores iniciales.

        $desde = date('Y-m-01');

        $hasta = date('Y-m-d');

        $error = '';

        $resumen = [
            'ventas' => [],
            'premios' => [],
            'pagos' => []
        ];

        // 2. Recibir fechas.

        $fechaDesde = $_GET['desde'] ?? $desde;

        $fechaHasta = $_GET['hasta'] ?? $hasta;

        if ($this->validarFecha($fechaDesde)) {
            $desde = $fechaDesde;
        }

        if (
            $this->validarFecha($fechaHasta)
            && $fechaHasta >= $desde
        ) {
            $hasta = $fechaHasta;
        }

        // 3. Preparar filtros.

        $filtros = [
            'desde' => $desde,
            'hasta' => $hasta
        ];

        // 4. Consultar modelo.

        try {

            $modelo = new ReporteGeneralModel($conexion);

            $resumen = $modelo->obtenerResumenGeneral($filtros);

        } catch (Throwable $e) {

            error_log(
                'Error en ReporteGeneralController: '
                . $e->getMessage()
            );

            $error = 'No fue posible cargar el reporte general. '
                   . 'Verifica la configuración y los registros del sistema.';
        }

        // 5. Cargar vista.

        $vista = dirname(__DIR__) . '/vistas/general.php';

        if (!file_exists($vista)) {

            http_response_code(500);

            exit('Error: no se encontró la vista vistas/general.php.');
        }

        require $vista;
    }
}
