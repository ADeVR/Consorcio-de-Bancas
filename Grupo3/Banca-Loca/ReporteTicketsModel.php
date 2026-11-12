
<?php

/**
 * ==========================================================
 * MODELO DE REPORTE DE TICKETS
 * Sistema Banca Loca
 * Base de datos: u318940728_aderson
 * ==========================================================
 */

class ReporteTicketsModel
{
    private mysqli $conexion;

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

        if (!$stmt) {
            throw new RuntimeException(
                'Error al preparar consulta: ' . $this->conexion->error
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

            throw new RuntimeException(
                'Error al ejecutar consulta: ' . $error
            );
        }

        $resultado = $stmt->get_result();

        if (!$resultado) {
            $stmt->close();

            throw new RuntimeException(
                'No se pudo obtener el resultado.'
            );
        }

        $datos = $resultado->fetch_all(MYSQLI_ASSOC);

        $stmt->close();

        return $datos;
    }

    /**
     * Construir filtros del reporte.
     */
    private function construirFiltros(array $filtros): array
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

        $estado = $filtros['estado'] ?? '';

        $codigo = trim($filtros['codigo'] ?? '');

        if ($usuario > 0) {

            $condiciones[] = 't.id_usuario = ?';

            $tipos .= 'i';

            $parametros[] = $usuario;
        }

        if (in_array($estado, ['activo', 'anulado'], true)) {

            $condiciones[] = 't.estado = ?';

            $tipos .= 's';

            $parametros[] = $estado;
        }

        if ($codigo !== '') {

            $condiciones[] = 't.codigo_ticket LIKE ?';

            $tipos .= 's';

            $parametros[] = '%' . $codigo . '%';
        }

        return [
            'where' => implode(' AND ', $condiciones),
            'tipos' => $tipos,
            'parametros' => $parametros
        ];
    }

    /**
     * ======================================================
     * 1. RESUMEN GENERAL DE TICKETS
     * ======================================================
     */
    public function obtenerResumen(array $filtros = []): array
    {
        $f = $this->construirFiltros($filtros);

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
                ), 0) AS importe_activos,

                COALESCE(SUM(
                    CASE
                        WHEN t.estado = 'anulado' THEN t.total
                        ELSE 0
                    END
                ), 0) AS importe_anulados

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
     * 2. LISTADO DE TICKETS
     * ======================================================
     */
    public function obtenerTickets(array $filtros = []): array
    {
        $f = $this->construirFiltros($filtros);

        $sql = "
            SELECT

                t.id_ticket,

                t.codigo_ticket,

                t.fecha_hora,

                u.usuario,

                t.id_apertura,

                t.total,

                t.estado,

                COUNT(jd.id_detalle) AS cantidad_jugadas

            FROM ticket t

            INNER JOIN usuarios u
                ON t.id_usuario = u.id

            LEFT JOIN jugada_detalle jd
                ON t.id_ticket = jd.id_ticket

            WHERE {$f['where']}

            GROUP BY
                t.id_ticket,
                t.codigo_ticket,
                t.fecha_hora,
                u.usuario,
                t.id_apertura,
                t.total,
                t.estado

            ORDER BY
                t.fecha_hora DESC,
                t.id_ticket DESC
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
}
