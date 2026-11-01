<?php
declare(strict_types=1);
namespace Caja\Modulos\Reportes;

/** Datos de ejemplo en memoria (sin BD). Mismo formato que devuelve MovimientoRepo. */
final class MovimientoRepoSimulado implements MovimientoRepoInterface
{
    public function buscar(array $f): array
    {
        $hoy = date('Y-m-d');
        $t = static fn (int $n): string => 'T-' . date('Ymd') . '-' . str_pad((string) $n, 6, '0', STR_PAD_LEFT);
        $base = [
            [1, '08:55:00', $t(1),  'venta',   'entrada', '500.00', 3, 'agente1', '500.00'],
            [2, '09:10:00', $t(2),  'venta',   'entrada', '120.00', 3, 'agente1', '620.00'],
            [3, '09:40:00', $t(1),  'pago',    'salida',  '300.00', 3, 'agente1', '320.00'],
            [4, '10:05:00', $t(3),  'venta',   'entrada', '250.00', 4, 'agente2', '570.00'],
            [5, '10:30:00', $t(4),  'pago',    'ninguno', '900.00', 4, 'agente2', '570.00'],
        ];
        $filas = [];
        foreach ($base as [$id, $hora, $ticket, $tipo, $efecto, $importe, $idU, $usuario, $saldo]) {
            $filas[] = ['id' => $id, 'fecha_hora' => "$hoy $hora", 'ticket' => $ticket, 'tipo' => $tipo, 'operacion' => $tipo === 'intento_pago_rechazado' ? 'Pago sin fondos' : ucfirst($tipo),
                'importe' => $importe, 'efecto' => $efecto, 'estado' => $efecto === 'ninguno' ? 'informativo' : 'confirmado',
                'id_usuario' => $idU, 'usuario' => $usuario, 'saldo_resultante' => $saldo,
                'detalle' => $efecto === 'ninguno' ? ['observacion' => 'Pago sin fondos: no se modificó el saldo'] : []];
        }
        return array_values(array_filter($filas, static function (array $r) use ($f): bool {
            $dia = substr($r['fecha_hora'], 0, 10);
            return $dia >= $f['desde'] && $dia <= $f['hasta']
                && (empty($f['tipo'])       || $r['tipo'] === $f['tipo'])
                && (empty($f['estado'])     || $r['estado'] === $f['estado'])
                && (empty($f['id_usuario']) || $r['id_usuario'] === (int) $f['id_usuario'])
                && (empty($f['ticket'])     || stripos($r['ticket'], $f['ticket']) === 0);
        }));
    }
}
