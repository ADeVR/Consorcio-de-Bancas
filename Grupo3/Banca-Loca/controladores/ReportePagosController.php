
<?php

/**
 * ==========================================================
 * CONTROLADOR DEL REPORTE DE PAGOS
 * Sistema Banca Loca
 * ==========================================================
 */

require_once dirname(__DIR__) . '/ReportePagosModel.php';

class ReportePagosController
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
     * MOSTRAR REPORTE DE PAGOS
     */
    public function index(mysqli $conexion): void
    {
        // 1. Inicializar resumen.

        $resumen = [
            'total_pagos' => 0,
            'total_pagados' => 0,
            'total_pendientes' => 0,
            'monto_total' => 0,
            'monto_pagado' => 0,
            'monto_pendiente' => 0
        ];

        $pagos = [];

        $tiposPago = [];

        $error = '';

        // 2. Valores iniciales.

        $desde = date('Y-m-01');

        $hasta = date('Y-m-d');

        $tipoPago = '';

        $estado = '';

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

        // 4. Recibir tipo de pago.

        $tipoRecibido = $_GET['tipo_pago'] ?? '';

        $tiposPermitidos = [
            'efectivo',
            'transferencia'
        ];

        if (
            is_string($tipoRecibido)
            && in_array($tipoRecibido, $tiposPermitidos, true)
        ) {
            $tipoPago = $tipoRecibido;
        }

        // 5. Recibir estado.

        $estadoRecibido = $_GET['estado'] ?? '';

        $estadosPermitidos = [
            'pendiente',
            'pagado'
        ];

        if (
            is_string($estadoRecibido)
            && in_array($estadoRecibido, $estadosPermitidos, true)
        ) {
            $estado = $estadoRecibido;
        }

        // 6. Preparar filtros.

        $filtros = [
            'desde' => $desde,
            'hasta' => $hasta,
            'tipo_pago' => $tipoPago,
            'estado' => $estado
        ];

        // 7. Consultar modelo.

        try {

            $modelo = new ReportePagosModel($conexion);

            $pagos = $modelo->obtenerPagos($filtros);

            $resumen = $modelo->obtenerResumen($pagos);

            $tiposPago = $modelo->obtenerTiposPago();

        } catch (Throwable $e) {

            error_log(
                'Error en ReportePagosController: '
                . $e->getMessage()
            );

            $error = 'No fue posible cargar el reporte de pagos. '
                   . 'Verifica la configuración y los registros del sistema.';
        }

        // 8. Cargar vista.

        $vista = dirname(__DIR__) . '/vistas/pagos.php';

        if (!file_exists($vista)) {

            http_response_code(500);

            exit('Error: no se encontró la vista vistas/pagos.php.');
        }

        require $vista;
    }
}
