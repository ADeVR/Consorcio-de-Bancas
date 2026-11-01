<?php
declare(strict_types=1);
namespace Caja\Integracion\Puertos;

/** M5 · Hora oficial de la República Dominicana. */
interface HoraPort
{
    public function ahora(): \DateTimeImmutable;
}
