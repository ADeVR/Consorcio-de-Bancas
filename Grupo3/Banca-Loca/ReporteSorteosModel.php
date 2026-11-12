
<?php

/**
 * ==========================================================
 * MODELO DE REPORTE DE SORTEOS
 * Sistema Banca Loca
 * Base de datos: u318940728_aderson
 * ==========================================================
 */

class ReporteSorteosModel
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
     * ======================================================
     * EJECUTAR CONSULTAS PREPARADAS
     * ======================================================
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
     * ======================================================
     * CONSTRUIR FILTROS
     * ======================================================
     */
    private function construirFiltros(array $filtros): array
    {
        $condiciones = [];

        $tipos = '';

        $parametros = [];

        $loteria = (int)($filtros['loteria'] ?? 0);

        $sorteo = (int)($filtros['sorteo'] ?? 0);

        $activo = $filtros['activo'] ?? '';

        if ($loteria > 0) {

            $condiciones[] = 's.loteria_id = ?';

            $tipos .= 'i';

            $parametros[] = $loteria;
        }

        if ($sorteo > 0) {

            $condiciones[] = 's.id = ?';

            $tipos .= 'i';

            $parametros[] = $sorteo;
        }

        if (
            $activo !== '' &&
            in_array((string)$activo, ['0', '1'], true)
        ) {

            $condiciones[] = 's.activo = ?';

            $tipos .= 'i';

            $parametros[] = (int)$activo;
        }

        return [
            'where' => !empty($condiciones)
                ? 'WHERE ' . implode(' AND ', $condiciones)
                : '',

            'tipos' => $tipos,

            'parametros' => $parametros
        ];
    }

    /**
     * ======================================================
     * 1. OBTENER SORTEOS CON SUS LOTERÍAS Y DÍAS
     * ======================================================
     */
    public function obtenerSorteos(array $filtros = []): array
    {
        $f = $this->construirFiltros($filtros);

        $desde = $filtros['desde'] ?? date('Y-m-01');

        $hasta = $filtros['hasta'] ?? date('Y-m-d');

        $sql = "
            SELECT

                s.id,

                s.nombre,

                s.hora,

                s.activo,

                s.fecha_creacion,

                l.nombre AS loteria,

                COALESCE(
                    sd.dias,
                    'Sin días configurados'
                ) AS dias,

                COALESCE(
                    j.total_jugadas,
                    0
                ) AS total_jugadas,

                COALESCE(
                    j.total_apostado,
                    0
                ) AS total_apostado,

                COALESCE(
                    g.total_ganadoras,
                    0
                ) AS total_ganadoras,

                COALESCE(
                    g.premios_registrados,
                    'Sin premios registrados'
                ) AS premios_registrados

            FROM sorteos s

            INNER JOIN loterias l
                ON s.loteria_id = l.id

            LEFT JOIN (

                SELECT

                    sorteo_id,

                    GROUP_CONCAT(
                        dia
                        ORDER BY FIELD(
                            dia,
                            'lunes',
                            'martes',
                            'miercoles',
                            'jueves',
                            'viernes',
                            'sabado',
                            'domingo'
                        )
                        SEPARATOR ', '
                    ) AS dias

                FROM sorteo_dias

                GROUP BY sorteo_id

            ) sd ON sd.sorteo_id = s.id

            LEFT JOIN (

                SELECT

                    jd.id_sorteo,

                    COUNT(DISTINCT jd.id_detalle)
                        AS total_jugadas,

                    COALESCE(
                        SUM(jd.monto),
                        0
                    ) AS total_apostado

                FROM jugada_detalle jd

                INNER JOIN ticket t
                    ON jd.id_ticket = t.id_ticket

                WHERE
                    t.fecha_hora >= ?

                    AND t.fecha_hora < DATE_ADD(
                        ?,
                        INTERVAL 1 DAY
                    )

                    AND t.estado = 'activo'

                    AND jd.estado <> 'anulada'

                GROUP BY jd.id_sorteo

            ) j ON j.id_sorteo = s.id

            LEFT JOIN (

                SELECT

                    jd.id_sorteo,

                    COUNT(DISTINCT ng.id)
                        AS total_ganadoras,

                    GROUP_CONCAT(
                        DISTINCT pj.nombre_premio
                        ORDER BY pj.id
                        SEPARATOR ', '
                    ) AS premios_registrados

                FROM numero_ganador ng

                INNER JOIN jugada_detalle jd
                    ON ng.id_detalle = jd.id_detalle

                LEFT JOIN premios_juego pj
                    ON ng.id_premio = pj.id

                WHERE
                    ng.fecha_sorteo >= ?

                    AND ng.fecha_sorteo <= ?

                GROUP BY jd.id_sorteo

            ) g ON g.id_sorteo = s.id

            {$f['where']}

            ORDER BY
                l.nombre ASC,
                s.hora ASC,
                s.nombre ASC
        ";

        $tipos = 'ssss' . $f['tipos'];

        $parametros = [
            $desde . ' 00:00:00',
            $hasta . ' 00:00:00',
            $desde,
            $hasta
        ];

        $parametros = array_merge(
            $parametros,
            $f['parametros']
        );

        return $this->consultar(
            $sql,
            $tipos,
            $parametros
        );
    }

    /**
     * ======================================================
     * 2. OBTENER RESUMEN GENERAL
     * ======================================================
     */
    public function obtenerResumen(array $sorteos): array
    {
        $resumen = [
            'total_sorteos' => 0,
            'sorteos_activos' => 0,
            'sorteos_inactivos' => 0,
            'total_jugadas' => 0,
            'total_apostado' => 0,
            'total_ganadoras' => 0
        ];

        foreach ($sorteos as $sorteo) {

            $resumen['total_sorteos']++;

            if ((int)$sorteo['activo'] === 1) {

                $resumen['sorteos_activos']++;

            } else {

                $resumen['sorteos_inactivos']++;
            }

            $resumen['total_jugadas'] +=
                (int)$sorteo['total_jugadas'];

            $resumen['total_apostado'] +=
                (float)$sorteo['total_apostado'];

            $resumen['total_ganadoras'] +=
                (int)$sorteo['total_ganadoras'];
        }

        return $resumen;
    }

    /**
     * ======================================================
     * 3. OBTENER LOTERÍAS PARA LOS FILTROS
     * ======================================================
     */
    public function obtenerLoterias(): array
    {
        $sql = "
            SELECT
                id,
                nombre
            FROM loterias
            ORDER BY nombre ASC
        ";

        return $this->consultar($sql);
    }
}
