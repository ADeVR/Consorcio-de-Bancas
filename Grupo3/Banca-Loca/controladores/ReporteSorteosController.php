
<?php

/**
 * ==========================================================
 * CONTROLADOR DEL REPORTE DE SORTEOS
 * Sistema Banca Loca
 * ==========================================================
 */

require_once dirname(__DIR__) . '/ReporteSorteosModel.php';

class ReporteSorteosController
{
    /**
     * Validar formato de fecha.
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
     * Validar identificadores numéricos.
     */
    private function validarId($valor): int
    {
        if (!is_scalar($valor)) {
            return 0;
        }

        $id = filter_var($valor, FILTER_VALIDATE_INT);

        return ($id !== false && $id > 0) ? $id : 0;
    }

    /**
     * ======================================================
     * MOSTRAR REPORTE DE SORTEOS
     * ======================================================
     */
    public function index(mysqli $conexion): void
    {
        // 1. Inicializar resumen.

        $resumen = [
            'total_sorteos' => 0,
            'sorteos_activos' => 0,
            'sorteos_inactivos' => 0,
            'total_jugadas' => 0,
            'total_apostado' => 0,
            'total_ganadoras' => 0
        ];

        $sorteos = [];

        $loterias = [];

        $error = '';

        // 2. Valores iniciales de los filtros.

        $desde = date('Y-m-01');

        $hasta = date('Y-m-d');

        $loteria = 0;

        $sorteo = 0;

        $activo = '';

        // 3. Recibir fechas.

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

        // 4. Recibir identificadores.

        $loteria = $this->validarId(
            $_GET['loteria'] ?? 0
        );

        $sorteo = $this->validarId(
            $_GET['sorteo'] ?? 0
        );

        // 5. Recibir estado.

        $estadoRecibido = $_GET['activo'] ?? '';

        if (
            is_scalar($estadoRecibido)
            && in_array((string)$estadoRecibido, ['0', '1'], true)
        ) {
            $activo = (string)$estadoRecibido;
        }

        // 6. Preparar filtros.

        $filtros = [
            'desde' => $desde,
            'hasta' => $hasta,
            'loteria' => $loteria,
            'sorteo' => $sorteo,
            'activo' => $activo
        ];

        // 7. Consultar modelo.

        try {

            $modelo = new ReporteSorteosModel($conexion);

            // Consultar sorteos.
            $sorteos = $modelo->obtenerSorteos($filtros);

            // Calcular resumen.
            $resumen = $modelo->obtenerResumen($sorteos);

            // Consultar loterías.
            $loterias = $modelo->obtenerLoterias();

        } catch (Throwable $e) {

            error_log(
                'Error en ReporteSorteosController: '
                . $e->getMessage()
            );

            $error = 'No fue posible cargar el reporte de sorteos. '
                   . 'Verifica la configuración y los registros del sistema.';
        }

        // 8. Cargar vista.

        $vista = dirname(__DIR__) . '/vistas/sorteos.php';

        if (!file_exists($vista)) {

            http_response_code(500);

            exit('Error: no se encontró la vista vistas/sorteos.php.');
        }

        require $vista;
    }
}
