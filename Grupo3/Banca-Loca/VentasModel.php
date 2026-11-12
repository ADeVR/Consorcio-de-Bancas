
<?php

/**
 * ==========================================================
 * MODELO DE VENTAS
 * Sistema de Administración de Loterías
 * Base de datos: u318940728_aderson
 * ==========================================================
 */

class VentasModel
{
    private mysqli $conexion;

    /**
     * Recibir la conexión existente.
     */
    public function __construct(mysqli $conexion)
    {
        $this->conexion = $conexion;
    }

    /**
     * Ejecutar consultas preparadas.
     */
    private function consultar(
        string $sql,
        string $tipos = '',
        array $parametros = []
    ): array {

        $stmt = $this->conexion->prepare($sql);

        if ($tipos !== '') {

            $argumentos = [];
            $argumentos[] = &$tipos;

            foreach ($parametros as $indice => &$valor) {
                $argumentos[] = &$valor;
            }

            unset($valor);

            call_user_func_array(
                [$stmt, 'bind_param'],
                $argumentos
            );
        }

        $stmt->execute();

        $resultado = $stmt->get_result();

        $filas = $resultado->fetch_all(MYSQLI_ASSOC);

        $stmt->close();

        return $filas;
    }

    /**
     * Construir filtros para el resumen de tickets.
     */
    private function construirFiltrosResumen(array $filtros): array
    {
        $condiciones = [
            't.fecha_hora >= ?',
            't.fecha_hora < DATE_ADD(?, INTERVAL 1 DAY)'
        ];

        $tipos = 'ss';

        $parametros = [
            ($filtros['desde'] ?? date('Y-m-01')) . ' 00:00:00',
            ($filtros['hasta'] ?? date('Y-m-d')) . ' 00:00:00'
        ];

        $usuario = (int)($filtros['usuario'] ?? 0);
        $loteria = (int)($filtros['loteria'] ?? 0);
        $sorteo = (int)($filtros['sorteo'] ?? 0);

        if ($usuario > 0) {
            $condiciones[] = 't.id_usuario = ?';
            $tipos .= 'i';
            $parametros[] = $usuario;
        }

        if ($loteria > 0 || $sorteo > 0) {

            $filtros_jugada = [];

            if ($loteria > 0) {
                $filtros_jugada[] = 'jd_filtro.id_loteria = ?';
                $tipos .= 'i';
                $parametros[] = $loteria;
            }

            if ($sorteo > 0) {
                $filtros_jugada[] = 'jd_filtro.id_sorteo = ?';
                $tipos .= 'i';
                $parametros[] = $sorteo;
            }

            $condiciones[] = "
                EXISTS (
                    SELECT 1
                    FROM jugada_detalle jd_filtro
                    WHERE jd_filtro.id_ticket = t.id_ticket
                    AND " . implode(' AND ', $filtros_jugada) . "
                )
            ";
        }

        return [
            'where' => implode(' AND ', $condiciones),
            'tipos' => $tipos,
            'parametros' => $parametros
        ];
    }

    /**
     * Construir filtros para el detalle de jugadas.
     */
    private function construirFiltrosDetalle(array $filtros): array
    {
        $condiciones = [
            't.fecha_hora >= ?',
            't.fecha_hora < DATE_ADD(?, INTERVAL 1 DAY)'
        ];

        $tipos = 'ss';

        $parametros = [
            ($filtros['desde'] ?? date('Y-m-01')) . ' 00:00:00',
            ($filtros['hasta'] ?? date('Y-m-d')) . ' 00:00:00'
        ];

        $usuario = (int)($filtros['usuario'] ?? 0);
        $loteria = (int)($filtros['loteria'] ?? 0);
        $sorteo = (int)($filtros['sorteo'] ?? 0);

        if ($usuario > 0) {
            $condiciones[] = 't.id_usuario = ?';
            $tipos .= 'i';
            $parametros[] = $usuario;
        }

        if ($loteria > 0) {
            $condiciones[] = 'jd.id_loteria = ?';
            $tipos .= 'i';
            $parametros[] = $loteria;
        }

        if ($sorteo > 0) {
            $condiciones[] = 'jd.id_sorteo = ?';
            $tipos .= 'i';
            $parametros[] = $sorteo;
        }

        return [
            'where' => implode(' AND ', $condiciones),
            'tipos' => $tipos,
            'parametros' => $parametros
        ];
    }

    /**
     * ======================================================
     * 1. RESUMEN GENERAL DE VENTAS
     * ======================================================
     */
    public function obtenerResumen(array $filtros = []): array
    {
        $f = $this->construirFiltrosResumen($filtros);

        $sql = "
            SELECT

                COUNT(*) AS total_tickets,

                COALESCE(SUM(
                    CASE
                        WHEN t.estado = 'activo' THEN 1
                        ELSE 0
                    END
                ), 0) AS tickets_activos,

                COALESCE(SUM(
                    CASE
                        WHEN t.estado = 'anulado' THEN 1
                        ELSE 0
                    END
                ), 0) AS tickets_anulados,

                COALESCE(SUM(
                    CASE
                        WHEN t.estado = 'activo' THEN t.total
                        ELSE 0
                    END
                ), 0) AS ventas_validas,

                COALESCE(SUM(
                    CASE
                        WHEN t.estado = 'anulado' THEN t.total
                        ELSE 0
                    END
                ), 0) AS ventas_anuladas

            FROM ticket t

            WHERE {$f['where']}
        ";

        $resultado = $this->consultar(
            $sql,
            $f['tipos'],
            $f['parametros']
        );

        return $resultado[0] ?? [];
    }

    /**
     * ======================================================
     * 2. DETALLE DE VENTAS
     * ======================================================
     */
    public function obtenerVentas(array $filtros = []): array
    {
        $f = $this->construirFiltrosDetalle($filtros);

        $sql = "
            SELECT

                t.id_ticket,
                t.codigo_ticket,
                t.fecha_hora,

                u.usuario,

                l.nombre AS loteria,

                s.nombre AS sorteo,

                j.modalidad,

                jd.numeros,
                jd.monto,

                t.total AS total_ticket,

                t.estado AS estado_ticket,

                jd.estado AS estado_jugada

            FROM ticket t

            INNER JOIN usuarios u
                ON t.id_usuario = u.id

            INNER JOIN jugada_detalle jd
                ON t.id_ticket = jd.id_ticket

            INNER JOIN loterias l
                ON jd.id_loteria = l.id

            INNER JOIN sorteos s
                ON jd.id_sorteo = s.id

           INNER JOIN juegos j
            ON jd.id_tipo = j.id
            AND j.sorteo_id = jd.id_sorteo

            WHERE {$f['where']}

            ORDER BY
                t.fecha_hora DESC,
                t.id_ticket DESC,
                jd.id_detalle DESC
        ";

        return $this->consultar(
            $sql,
            $f['tipos'],
            $f['parametros']
        );
    }

    /**
     * ======================================================
     * 3. OBTENER USUARIOS
     * ======================================================
     */
    public function obtenerUsuarios(): array
    {
        $sql = "
            SELECT id, usuario
            FROM usuarios
            ORDER BY usuario ASC
        ";

        return $this->consultar($sql);
    }

    /**
     * ======================================================
     * 4. OBTENER LOTERÍAS
     * ======================================================
     */
    public function obtenerLoterias(): array
    {
        $sql = "
            SELECT id, nombre
            FROM loterias
            ORDER BY nombre ASC
        ";

        return $this->consultar($sql);
    }

    /**
     * ======================================================
     * 5. OBTENER SORTEOS
     * ======================================================
     */
    public function obtenerSorteos(): array
    {
        $sql = "
            SELECT id, nombre
            FROM sorteos
            ORDER BY nombre ASC
        ";

        return $this->consultar($sql);
    }
}
