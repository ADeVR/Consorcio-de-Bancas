
<?php

/**
 * ==========================================================
 * CONTROLADOR DEL REPORTE DE PREMIOS
 * Sistema Banca Loca
 * ==========================================================
 */

require_once dirname(__DIR__) . '/ReportePremiosModel.php';

class ReportePremiosController
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
     * VALIDAR IDENTIFICADORES NUMÉRICOS
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
     * MOSTRAR REPORTE DE PREMIOS
     * ======================================================
     */
    public function index(mysqli $conexion): void
    {
        // 1. Inicializar resumen.

        $resumen = [
            'total_premios' => 0,
            'total_ganadoras' => 0,
            'total_pagadas' => 0,
            'total_pendientes_pago' => 0,
            'total_apostado' => 0,
            'total_premios_estimados' => 0
        ];

        $premios = [];

        $loterias = [];

        $sorteos = [];

        $tiposPremio = [];

        $error = '';

        // 2. Valores iniciales.

        $desde = date('Y-m-01');

        $hasta = date('Y-m-d');

        $loteria = 0;

        $sorteo = 0;

        $premio = '';

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

        // 4. Recibir identificadores.

        $loteria = $this->validarId(
            $_GET['loteria'] ?? 0
        );

        $sorteo = $this->validarId(
            $_GET['sorteo'] ?? 0
        );

        // 5. Recibir tipo de premio.

        $premioRecibido = $_GET['premio'] ?? '';

        $tiposPermitidos = [
            'Primera',
            'Segunda',
            'Tercera',
            'Pale',
            'Tripleta'
        ];

        if (
            is_string($premioRecibido)
            && in_array($premioRecibido, $tiposPermitidos, true)
        ) {
            $premio = $premioRecibido;
        }

        // 6. Recibir estado.

        $estadoRecibido = $_GET['estado'] ?? '';

        if (
            is_string($estadoRecibido)
            && in_array(
                $estadoRecibido,
                ['ganadora', 'pagada'],
                true
            )
        ) {
            $estado = $estadoRecibido;
        }

        // 7. Preparar filtros.

        $filtros = [
            'desde' => $desde,
            'hasta' => $hasta,
            'loteria' => $loteria,
            'sorteo' => $sorteo,
            'premio' => $premio,
            'estado' => $estado
        ];

        // 8. Consultar modelo.

        try {

            $modelo = new ReportePremiosModel($conexion);

            $premios = $modelo->obtenerPremios($filtros);

            $resumen = $modelo->obtenerResumen($premios);

            $loterias = $modelo->obtenerLoterias();

            $sorteos = $modelo->obtenerSorteos();

            $tiposPremio = $modelo->obtenerTiposPremio();

        } catch (Throwable $e) {

            error_log(
                'Error en ReportePremiosController: '
                . $e->getMessage()
            );

            $error = 'No fue posible cargar el reporte de premios. '
                   . 'Verifica la configuración y los registros del sistema.';
        }

        // 9. Cargar vista.

        $vista = dirname(__DIR__) . '/vistas/premios.php';

        if (!file_exists($vista)) {

            http_response_code(500);

            exit('Error: no se encontró la vista vistas/premios.php.');
        }

        require $vista;
    }
}
