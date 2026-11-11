
<?php

/**
 * ==========================================================
 * MODELO DEL REPORTE GENERAL
 * Sistema Banca Loca
 * Base de datos: u318940728_aderson
 * ==========================================================
 */

class ReporteGeneralModel
{
    private mysqli $conexion;

    public function __construct(mysqli $conexion)
    {
        $this->conexion = $conexion;
    }

    /**
     * EJECUTAR CONSULTAS PREPARADAS
     */
    private function consultar(
        string $sql,
        string $tipos,
        array $parametros
    ): array {

        $stmt = $this->conexion->prepare($sql);

        if (!$stmt) {
            throw new RuntimeException(
                'Error preparando consulta: '
                . $this->conexion->error
            );
        }

        if ($tipos !== '') {

            $argumentos = [];
            $argumentos[] = &$tipos;

            foreach ($parametros as &$valor) {
                $argumentos[] = &$valor;
            }

            unset($valor);

            call_user_func_array(
                [$stmt, 'bind_param'],
                $argumentos
            );
        }

        if (!$stmt->execute()) {

            $error = $stmt->error;

            $stmt->close();

            throw new RuntimeException($error);
        }

        $resultado = $stmt->get_result();

        $datos = $resultado->fetch_assoc() ?: [];

        $stmt->close();

        return $datos;
    }

    /**
     * ======================================================
     * 1. RESUMEN DE VENTAS Y TICKETS
     * ======================================================
     */
    public function obtenerResumenVentas(array $filtros): array
    {
        $sql = "
            SELECT

                COUNT(*) AS total_tickets,

                COALESCE(SUM(
                    CASE
                        WHEN estado = 'activo' THEN 1
                        ELSE 0
                    END
                ), 0) AS tickets_activos,

                COALESCE(SUM(
                    CASE
                        WHEN estado = 'anulado' THEN 1
                        ELSE 0
                    END
                ), 0) AS tickets_anulados,

                COALESCE(SUM(
                    CASE
                        WHEN estado = 'activo' THEN total
                        ELSE 0
                    END
                ), 0) AS ventas_validas,

                COALESCE(SUM(
                    CASE
                        WHEN estado = 'anulado' THEN total
                        ELSE 0
                    END
                ), 0) AS ventas_anuladas

            FROM ticket

            WHERE fecha_hora >= ?

            AND fecha_hora < DATE_ADD(?, INTERVAL 1 DAY)
        ";

        return $this->consultar(
            $sql,
            'ss',
            [
                $filtros['desde'] . ' 00:00:00',
                $filtros['hasta'] . ' 00:00:00'
            ]
        );
    }

    /**
     * ======================================================
     * 2. RESUMEN DE PREMIOS
     * ======================================================
     */
    public function obtenerResumenPremios(array $filtros): array
    {
        $sql = "
            SELECT

                COUNT(*) AS total_premios,

                COALESCE(SUM(
                    CASE
                        WHEN jd.estado = 'ganadora' THEN 1
                        ELSE 0
                    END
                ), 0) AS premios_ganadores,

                COALESCE(SUM(
                    CASE
                        WHEN jd.estado = 'pagada' THEN 1
                        ELSE 0
                    END
                ), 0) AS premios_pagados,

                COALESCE(SUM(
                    CASE
                        WHEN jd.estado = 'ganadora'
                        THEN jd.monto * pj.valor_por_peso
                        ELSE 0
                    END
                ), 0) AS monto_pendiente_estimado,

                COALESCE(SUM(
                    CASE
                        WHEN jd.estado = 'pagada'
                        THEN jd.monto * pj.valor_por_peso
                        ELSE 0
                    END
                ), 0) AS monto_premios_pagados

            FROM numero_ganador ng

            INNER JOIN jugada_detalle jd
                ON jd.id_detalle = ng.id_detalle

            INNER JOIN ticket t
                ON t.id_ticket = jd.id_ticket

            INNER JOIN premios_juego pj
                ON pj.id = ng.id_premio

            WHERE DATE(ng.fecha_sorteo) BETWEEN ? AND ?

            AND t.estado = 'activo'
        ";

        return $this->consultar(
            $sql,
            'ss',
            [
                $filtros['desde'],
                $filtros['hasta']
            ]
        );
    }

    /**
     * ======================================================
     * 3. RESUMEN DE PAGOS
     * ======================================================
     */
    public function obtenerResumenPagos(array $filtros): array
    {
        $sql = "
            SELECT

                COUNT(*) AS total_pagos,

                COALESCE(SUM(
                    CASE
                        WHEN estado = 'pagado' THEN 1
                        ELSE 0
                    END
                ), 0) AS pagos_realizados,

                COALESCE(SUM(
                    CASE
                        WHEN estado = 'pendiente' THEN 1
                        ELSE 0
                    END
                ), 0) AS pagos_pendientes,

                COALESCE(SUM(premio), 0) AS monto_total,

                COALESCE(SUM(
                    CASE
                        WHEN estado = 'pagado' THEN premio
                        ELSE 0
                    END
                ), 0) AS monto_pagado,

                COALESCE(SUM(
                    CASE
                        WHEN estado = 'pendiente' THEN premio
                        ELSE 0
                    END
                ), 0) AS monto_pendiente

            FROM vista_pagos

            WHERE (
                fecha_pago >= ?

                AND fecha_pago < DATE_ADD(?, INTERVAL 1 DAY)

                OR fecha_pago IS NULL
            )
        ";

        return $this->consultar(
            $sql,
            'ss',
            [
                $filtros['desde'],
                $filtros['hasta']
            ]
        );
    }

    /**
     * ======================================================
     * 4. OBTENER RESUMEN GENERAL
     * ======================================================
     */
    public function obtenerResumenGeneral(array $filtros): array
    {
        return [

            'ventas' => $this->obtenerResumenVentas($filtros),

            'premios' => $this->obtenerResumenPremios($filtros),

            'pagos' => $this->obtenerResumenPagos($filtros)

        ];
    }
}
