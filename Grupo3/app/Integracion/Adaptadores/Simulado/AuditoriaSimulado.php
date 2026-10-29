<?php
declare(strict_types=1);
namespace Caja\Integracion\Adaptadores\Simulado;

use Caja\Core\Config;
use Caja\Core\Contenedor;
use Caja\Core\Db;
use Caja\Core\Registro;
use Caja\Integracion\Puertos\AuditoriaPort;


/** M5 simulado: escribe en sim_auditoria. Un fallo se registra en el log y no interrumpe la operación (§9.2). */
final class AuditoriaSimulado implements AuditoriaPort
{
    public function registrar(array $evento): void
    {
        try {
            $t       = Config::tabla('auditoria');
            $detalle = $evento['detalle'] ?? null;
            if (is_array($detalle)) $detalle = json_encode($detalle, JSON_UNESCAPED_UNICODE);
            Db::pdo()->prepare("INSERT INTO {$t} (fecha_hora, id_usuario, accion, entidad, id_registro, detalle) VALUES (?,?,?,?,?,?)")
                ->execute([
                    Contenedor::puerto('hora')->ahora()->format('Y-m-d H:i:s'),
                    $evento['id_usuario'] ?? null,
                    mb_substr((string) $evento['accion'], 0, 60),
                    isset($evento['entidad']) ? mb_substr((string) $evento['entidad'], 0, 40) : null,
                    isset($evento['id_registro']) ? mb_substr((string) $evento['id_registro'], 0, 40) : null,
                    $detalle,
                ]);
        } catch (\Throwable $e) {
            Registro::error($e);
        }
    }
}
