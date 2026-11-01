<?php
declare(strict_types=1);
namespace Caja\Integracion\Adaptadores\Simulado;

use Caja\Core\Config;
use Caja\Core\Db;
use Caja\Integracion\Puertos\HoraPort;


/** M5 simulado: hora del servidor de BD (zona RD). `hora_fija` en app.php solo para pruebas. */
final class HoraSimulado implements HoraPort
{
    public function ahora(): \DateTimeImmutable
    {
        $app  = Config::get('app');
        $zona = new \DateTimeZone($app['zona_horaria']);
        if ($app['hora_fija'] !== null) return new \DateTimeImmutable((string) $app['hora_fija'], $zona);
        return new \DateTimeImmutable((string) Db::pdo()->query('SELECT NOW()')->fetchColumn(), $zona);
    }
}
