<?php
declare(strict_types=1);
namespace Caja\Integracion\Adaptadores\Mock;

use Caja\Core\Registro;
use Caja\Integracion\Puertos\AuditoriaPort;


/** M5 sin BD (fase visual): la auditoría va al log técnico. */
final class AuditoriaMock implements AuditoriaPort
{
    public function registrar(array $evento): void
    {
        Registro::escribir('AUDITORIA ' . json_encode($evento, JSON_UNESCAPED_UNICODE));
    }
}
