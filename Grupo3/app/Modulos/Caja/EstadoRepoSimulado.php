<?php
declare(strict_types=1);
namespace Caja\Modulos\Caja;

/** Estado de ejemplo en memoria (sin BD). El saldo coincide con el último movimiento de MovimientoRepoSimulado. */
final class EstadoRepoSimulado implements EstadoRepoInterface
{
    public function actual(int $idBanca): ?array
    {
        $hoy = date('Y-m-d');
        return [
            'banca' => 'Banca Principal', 'saldo' => '570.00', 'actualizado' => "$hoy 10:30:00",
            'turno' => ['id' => 1, 'id_usuario' => 4, 'usuario' => 'agente2', 'desde' => "$hoy 08:00:00", 'fondo_inicial' => '0.00'],
            'pendientes_efectivo' => 1, 'pendientes_cierres' => 0,
        ];
    }
}
