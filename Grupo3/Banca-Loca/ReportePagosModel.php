
<?php

/**
 * ==========================================================
 * MODELO DE REPORTE DE PAGOS
 * Sistema Banca Loca
 * ==========================================================
 */

class ReportePagosModel
{
    private mysqli $conexion;

    public function __construct(mysqli $conexion)
    {
        $this->conexion = $conexion;
    }

    /**
     * OBTENER PAGOS CON FILTROS
     */
    public function obtenerPagos(array $filtros): array
    {
        $sql = "SELECT
                    id,
                    numero_boleto,
                    premio,
                    tipo_pago,
                    cuenta_destino,
                    estado,
                    fecha_pago,
                    hora_pago,
                    cajero
                FROM vista_pagos
                WHERE (
                    fecha_pago BETWEEN ? AND ?
                    OR fecha_pago IS NULL
                )";

        $parametros = [
            $filtros['desde'],
            $filtros['hasta']
        ];

        $tipos = "ss";

        if (!empty($filtros['tipo_pago'])) {
            $sql .= " AND tipo_pago = ?";
            $parametros[] = $filtros['tipo_pago'];
            $tipos .= "s";
        }

        if (!empty($filtros['estado'])) {
            $sql .= " AND estado = ?";
            $parametros[] = $filtros['estado'];
            $tipos .= "s";
        }

        $sql .= " ORDER BY fecha_pago DESC, hora_pago DESC, id DESC";

        $stmt = $this->conexion->prepare($sql);

        if (!$stmt) {
            throw new RuntimeException(
                'Error preparando consulta de pagos: '
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

        $pagos = $resultado->fetch_all(MYSQLI_ASSOC);

        $stmt->close();

        return $pagos;
    }

    /**
     * OBTENER RESUMEN DE PAGOS
     */
    public function obtenerResumen(array $pagos): array
    {
        $resumen = [
            'total_pagos' => count($pagos),
            'total_pagados' => 0,
            'total_pendientes' => 0,
            'monto_total' => 0,
            'monto_pagado' => 0,
            'monto_pendiente' => 0
        ];

        foreach ($pagos as $pago) {

            $monto = (float) $pago['premio'];

            $resumen['monto_total'] += $monto;

            if ($pago['estado'] === 'pagado') {

                $resumen['total_pagados']++;

                $resumen['monto_pagado'] += $monto;

            } elseif ($pago['estado'] === 'pendiente') {

                $resumen['total_pendientes']++;

                $resumen['monto_pendiente'] += $monto;
            }
        }

        return $resumen;
    }

    /**
     * OBTENER TIPOS DE PAGO
     */
    public function obtenerTiposPago(): array
    {
        return [
            'efectivo',
            'transferencia'
        ];
    }
}
