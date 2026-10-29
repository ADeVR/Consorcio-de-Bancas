<?php
declare(strict_types=1);
namespace Caja\Modulos\Reportes;

use Caja\Core\Config;
use Caja\Core\Db;


/** MySQL real (tablas de database/schema.sql). Mismo formato de salida que el simulado. */
final class MovimientoRepo implements MovimientoRepoInterface
{
    public function buscar(array $f): array
    {
        $t = Config::get('integracion')['tablas_externas'];
        $sql = "SELECT m.id_movimiento AS id, m.fecha_hora, m.tipo_movimiento AS tipo, m.efecto, m.estado,
                       m.importe, m.saldo_resultante, m.observacion, m.id_usuario, u.usuario,
                       (SELECT j.ticket FROM {$t['jugada']} j WHERE j.id_jugada = m.id_jugada) AS ticket
                  FROM movimiento_caja m
                  JOIN {$t['usuario']} u ON u.id_usuario = m.id_usuario
                 WHERE DATE(m.fecha_hora) BETWEEN ? AND ?";
        $p = [$f['desde'], $f['hasta']];
        foreach (['tipo' => 'm.tipo_movimiento = ?', 'estado' => 'm.estado = ?', 'id_usuario' => 'm.id_usuario = ?'] as $k => $cond) {
            if (!empty($f[$k])) { $sql .= " AND $cond"; $p[] = $f[$k]; }
        }
        $sql .= ' ORDER BY m.fecha_hora, m.id_movimiento';
        $st = Db::pdo()->prepare($sql);
        $st->execute($p);
        $filas = [];
        foreach ($st->fetchAll() as $r) {
            if (!empty($f['ticket']) && stripos((string) $r['ticket'], $f['ticket']) !== 0) continue;
            $r['operacion'] = ucfirst((string) $r['tipo']);
            $r['detalle']   = ['observacion' => $r['observacion']];
            unset($r['observacion']);
            $filas[] = $r;
        }
        return $filas;
    }
}
