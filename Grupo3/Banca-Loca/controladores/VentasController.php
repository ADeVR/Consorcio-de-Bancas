
<?php

/**
 * ==========================================================
 * CONTROLADOR DE VENTAS
 * Sistema de Administración de Loterías
 * ==========================================================
 */

// Cargar el modelo desde la carpeta principal.
require_once dirname(__DIR__) . '/ventasmodel.php';


class VentasController
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
     * MOSTRAR REPORTE DE VENTAS
     * ======================================================
     */
    public function index(mysqli $conexion): void
    {
        // --------------------------------------------------
        // 1. INICIALIZAR VARIABLES
        // --------------------------------------------------

        $resumen = [
            'total_tickets' => 0,
            'tickets_activos' => 0,
            'tickets_anulados' => 0,
            'ventas_validas' => 0,
            'ventas_anuladas' => 0
        ];

        $ventas = [];

        $usuarios = [];

        $loterias = [];

        $sorteos = [];

        $error = '';

        // --------------------------------------------------
        // 2. VALORES INICIALES DE LOS FILTROS
        // --------------------------------------------------

        $desde = '2026-10-01';
        $hasta = date('Y-m-d');

        $usuario = 0;

        $loteria = 0;

        $sorteo = 0;

        // --------------------------------------------------
        // 3. RECIBIR FECHAS
        // --------------------------------------------------

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

        // --------------------------------------------------
        // 4. RECIBIR IDENTIFICADORES
        // --------------------------------------------------

        $usuario = $this->validarId(
            $_GET['usuario'] ?? 0
        );

        $loteria = $this->validarId(
            $_GET['loteria'] ?? 0
        );

        $sorteo = $this->validarId(
            $_GET['sorteo'] ?? 0
        );

        // --------------------------------------------------
        // 5. PREPARAR FILTROS
        // --------------------------------------------------

        $filtros = [
            'desde' => $desde,
            'hasta' => $hasta,
            'usuario' => $usuario,
            'loteria' => $loteria,
            'sorteo' => $sorteo
        ];

        // --------------------------------------------------
        // 6. CONSULTAR MODELO DE VENTAS
        // --------------------------------------------------

        try {

            $modelo = new VentasModel($conexion);

            // Resumen de ventas.
            $resultadoResumen = $modelo->obtenerResumen($filtros);

            $resumen = array_merge(
                $resumen,
                $resultadoResumen
            );

            // Detalle de tickets y jugadas.
            $ventas = $modelo->obtenerVentas($filtros);

            // Listado de usuarios.
            $usuarios = $modelo->obtenerUsuarios();

            // Listado de loterías.
            $loterias = $modelo->obtenerLoterias();

            // Listado de sorteos.
            $sorteos = $modelo->obtenerSorteos();

        } catch (Throwable $e) {

            // Guardar detalles técnicos en el registro de PHP.
            error_log(
                'Error en VentasController: ' . $e->getMessage()
            );

            $error = 'No fue posible cargar el reporte de ventas. '
                   . 'Verifica los registros y la configuración del sistema.';
        }

        // --------------------------------------------------
        // 7. CARGAR LA VISTA
        // --------------------------------------------------

        $vista = dirname(__DIR__) . '/vistas/ventas.php';

        if (!file_exists($vista)) {

            http_response_code(500);

            exit('Error: no se encontró la vista vistas/ventas.php.');
        }

        // Enviar las variables a la vista.
        require $vista;
    }
}
