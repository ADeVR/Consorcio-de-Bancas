
<?php

/**
 * ==========================================================
 * MODELO DE REPORTE DE PREMIOS
 * Sistema Banca Loca
 * Base de datos: u318940728_aderson
 * ==========================================================
 */

class ReportePremiosModel
{
    private mysqli $conexion;

    public function __construct(mysqli $conexion)
    {
        $this->conexion = $conexion;
    }

    /**
     * OBTENER PREMIOS CON FILTROS
     */
    public function obtenerPremios(array $filtros): array
    {
        $sql = "SELECT
                    ng.id AS id_ganador,
                    ng.fecha_sorteo,
                    t.id_ticket,
                    t.codigo_ticket,
                    t.fecha_hora,
                    l.nombre AS loteria,
                    s.nombre AS sorteo,
                    j.modalidad,
                    jd.id_detalle,
                    jd.numeros,
                    jd.monto,
                    jd.estado,
                    pj.id AS id_premio,
                    pj.nombre_premio,
                    pj.valor_por_peso,
                    (jd.monto * pj.valor_por_peso) AS premio_estimado

                FROM numero_ganador ng

                INNER JOIN jugada_detalle jd
                    ON jd.id_detalle = ng.id_detalle

                INNER JOIN ticket t
                    ON t.id_ticket = jd.id_ticket

                INNER JOIN loterias l
                    ON l.id = jd.id_loteria

                INNER JOIN sorteos s
                    ON s.id = jd.id_sorteo

                INNER JOIN juegos j
                    ON j.id = jd.id_tipo

                INNER JOIN premios_juego pj
                    ON pj.id = ng.id_premio

                WHERE DATE(ng.fecha_sorteo) BETWEEN ? AND ?
                AND t.estado = 'activo'";

        $parametros = [
            $filtros['desde'],
            $filtros['hasta']
        ];

        $tipos = "ss";

        if (!empty($filtros['loteria'])) {
            $sql .= " AND jd.id_loteria = ?";
            $parametros[] = $filtros['loteria'];
            $tipos .= "i";
        }

        if (!empty($filtros['sorteo'])) {
            $sql .= " AND jd.id_sorteo = ?";
            $parametros[] = $filtros['sorteo'];
            $tipos .= "i";
        }

        if (!empty($filtros['premio'])) {
            $sql .= " AND pj.nombre_premio = ?";
            $parametros[] = $filtros['premio'];
            $tipos .= "s";
        }

        if (!empty($filtros['estado'])) {
            $sql .= " AND jd.estado = ?";
            $parametros[] = $filtros['estado'];
            $tipos .= "s";
        }

        $sql .= " ORDER BY ng.fecha_sorteo DESC, ng.id DESC";

        $stmt = $this->conexion->prepare($sql);

        if (!$stmt) {
            throw new RuntimeException(
                'Error preparando consulta de premios: '
                . $this->conexion->error
            );
        }

        $stmt->bind_param($tipos, ...$parametros);

        if (!$stmt->execute()) {
            $error = $stmt->error;
            $stmt->close();

            throw new RuntimeException($error);
        }

        $resultado = $stmt->get_result();

        $premios = $resultado->fetch_all(MYSQLI_ASSOC);

        $stmt->close();

        return $premios;
    }

    /**
     * OBTENER RESUMEN DEL REPORTE
     */
    public function obtenerResumen(array $premios): array
    {
        $resumen = [
            'total_premios' => count($premios),
            'total_ganadoras' => 0,
            'total_pagadas' => 0,
            'total_pendientes_pago' => 0,
            'total_apostado' => 0,
            'total_premios_estimados' => 0
        ];

        foreach ($premios as $fila) {

            $estado = $fila['estado'];

            if ($estado === 'ganadora') {
                $resumen['total_ganadoras']++;
                $resumen['total_pendientes_pago']++;
            }

            if ($estado === 'pagada') {
                $resumen['total_pagadas']++;
            }

            $resumen['total_apostado'] +=
                (float) $fila['monto'];

            $resumen['total_premios_estimados'] +=
                (float) $fila['premio_estimado'];
        }

        return $resumen;
    }

    /**
     * OBTENER LOTERIAS
     */
    public function obtenerLoterias(): array
    {
        $sql = "SELECT id, nombre
                FROM loterias
                WHERE activo = 1
                ORDER BY nombre ASC";

        $resultado = $this->conexion->query($sql);

        return $resultado
            ? $resultado->fetch_all(MYSQLI_ASSOC)
            : [];
    }

    /**
     * OBTENER SORTEOS
     */
    public function obtenerSorteos(): array
    {
        $sql = "SELECT
                    s.id,
                    s.nombre,
                    s.loteria_id,
                    l.nombre AS loteria
                FROM sorteos s
                INNER JOIN loterias l
                    ON l.id = s.loteria_id
                WHERE s.activo = 1
                ORDER BY l.nombre ASC, s.nombre ASC";

        $resultado = $this->conexion->query($sql);

        return $resultado
            ? $resultado->fetch_all(MYSQLI_ASSOC)
            : [];
    }

    /**
     * OBTENER TIPOS DE PREMIOS
     */
    public function obtenerTiposPremio(): array
    {
        $sql = "SELECT DISTINCT nombre_premio
                FROM premios_juego
                ORDER BY nombre_premio ASC";

        $resultado = $this->conexion->query($sql);

        if (!$resultado) {
            return [];
        }

        $tipos = [];

        while ($fila = $resultado->fetch_assoc()) {
            $tipos[] = $fila['nombre_premio'];
        }

        return $tipos;
    }
}
