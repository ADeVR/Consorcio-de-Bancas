<?php
declare(strict_types=1);
namespace Caja\Modulos\Caja;

use Caja\Core\Config;
use Caja\Core\Db;

/** MySQL real (tablas de database/schema.sql). El nombre de la tabla de usuarios sale de config/integracion.php. */
final class EstadoRepo implements EstadoRepoInterface
{
    public function actual(int $idBanca): ?array
    {
        $pdo = Db::pdo();
        $usuarios = Config::get('integracion')['tablas_externas']['usuario'];
        $st = $pdo->prepare('SELECT c.id_caja, b.nombre AS banca, c.saldo_actual AS saldo, c.fecha_actualizacion AS actualizado
                               FROM caja c JOIN banca b ON b.id_banca = c.id_banca WHERE c.id_banca = ?');
        $st->execute([$idBanca]);
        $caja = $st->fetch();
        if (!$caja) return null;

        $st = $pdo->prepare("SELECT t.id_turno AS id, t.id_usuario_entrante AS id_usuario, u.usuario, t.hora_inicio AS desde, t.fondo_inicial
                               FROM turno_caja t JOIN {$usuarios} u ON u.id_usuario = t.id_usuario_entrante
                              WHERE t.id_caja = ? AND t.estado = 'activo' LIMIT 1");
        $st->execute([$caja['id_caja']]);
        $turno = $st->fetch();
        if (!$turno) return null;
        $turno['id'] = (int) $turno['id']; $turno['id_usuario'] = (int) $turno['id_usuario'];

        return [
            'banca' => $caja['banca'], 'saldo' => $caja['saldo'], 'actualizado' => $caja['actualizado'], 'turno' => $turno,
            'pendientes_efectivo' => $this->contar('solicitud_efectivo', "estado = 'pendiente'", (int) $caja['id_caja']),
            'pendientes_cierres'  => $this->contar('cierre_caja', "estado = 'pendiente_autorizacion'", (int) $caja['id_caja']),
        ];
    }

    private function contar(string $tabla, string $condicion, int $idCaja): int
    {
        $st = Db::pdo()->prepare("SELECT COUNT(*) FROM {$tabla} WHERE id_caja = ? AND {$condicion}");
        $st->execute([$idCaja]);
        return (int) $st->fetchColumn();
    }
}
