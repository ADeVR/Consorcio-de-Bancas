<?php
declare(strict_types=1);
namespace Caja\Integracion\Adaptadores\Mock;

use Caja\Core\Config;
use Caja\Integracion\Puertos\HoraPort;


/** M5 sin BD (fase visual): hora del servidor web. `hora_fija` en app.php solo para pruebas. */
final class HoraMock implements HoraPort
{
    public function ahora(): \DateTimeImmutable
    {
        $app  = Config::get('app');
        $zona = new \DateTimeZone($app['zona_horaria']);
        return new \DateTimeImmutable((string) ($app['hora_fija'] ?? 'now'), $zona);
    }
}
