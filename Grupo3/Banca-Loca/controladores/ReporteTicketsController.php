
<?php

/**
 * ==========================================================
 * CONTROLADOR DEL REPORTE DE TICKETS
 * Sistema Banca Loca
 * ==========================================================
 */

require_once dirname(__DIR__) . '/ReporteTicketsModel.php';

class ReporteTicketsController
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
     * Validar identificadores.
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
     * MOSTRAR REPORTE DE TICKETS
     * ======================================================
     */
    public function index(mysqli $conexion): void
    {
        // 1. Inicializar resumen.

        $resumen = [
            'total_tickets' => 0,
            'tickets_activos' => 0,
            'tickets_anulados' => 0,
            'importe_activos' => 0,
            'importe_anulados' => 0
        ];

        $tickets = [];

        $usuarios = [];

        $error = '';

        // 2. Valores iniciales.

        $desde = date('Y-m-01');

        $hasta = date('Y-m-d');

        $usuario = 0;

        $estado = '';

        $codigo = '';

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

        // 4. Recibir filtros.

        $usuario = $this->validarId(
            $_GET['usuario'] ?? 0
        );

        $estadoRecibido = $_GET['estado'] ?? '';

        if (
            is_string($estadoRecibido)
            && in_array($estadoRecibido, ['activo', 'anulado'], true)
        ) {
            $estado = $estadoRecibido;
        }

        $codigoRecibido = $_GET['codigo'] ?? '';

        if (is_string($codigoRecibido)) {
            $codigo = trim($codigoRecibido);
        }

        // 5. Preparar filtros.

        $filtros = [
            'desde' => $desde,
            'hasta' => $hasta,
            'usuario' => $usuario,
            'estado' => $estado,
            'codigo' => $codigo
        ];

        // 6. Consultar modelo.

        try {

            $modelo = new ReporteTicketsModel($conexion);

            $resultadoResumen = $modelo->obtenerResumen($filtros);

            $resumen = array_merge(
                $resumen,
                $resultadoResumen
            );

            $tickets = $modelo->obtenerTickets($filtros);

            $usuarios = $modelo->obtenerUsuarios();

        } catch (Throwable $e) {

            error_log(
                'Error en ReporteTicketsController: '
                . $e->getMessage()
            );

            $error = 'No fue posible cargar el reporte de tickets. '
                   . 'Verifica la configuración y los registros.';
        }

        // 7. Cargar vista.

        $vista = dirname(__DIR__) . '/vistas/tickets.php';

        if (!file_exists($vista)) {

            http_response_code(500);

            exit('Error: no se encontró la vista vistas/tickets.php.');
        }

        require $vista;
    }
}
