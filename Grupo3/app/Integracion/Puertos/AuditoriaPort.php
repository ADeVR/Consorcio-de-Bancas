<?php
declare(strict_types=1);
namespace Caja\Integracion\Puertos;

/** M5 · Auditoría. $evento: id_usuario, accion, entidad, id_registro, detalle. */
interface AuditoriaPort
{
    public function registrar(array $evento): void;
}
