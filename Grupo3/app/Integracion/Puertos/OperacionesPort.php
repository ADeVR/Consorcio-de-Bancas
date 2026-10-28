<?php
declare(strict_types=1);
namespace Caja\Integracion\Puertos;

/** M4 · Operaciones de cobro, pago y anulación (solo lectura). */
interface OperacionesPort
{
    /** @return ?array<string,mixed> id_operacion, tipo, id_jugada, monto, id_usuario, decision, fecha_hora */
    public function porId(int $idOperacion): ?array;
    /** @return array<int,array<string,mixed>> cobro / anulación aprobada sin movimiento en Caja */
    public function pendientesDeRegistrar(): array;
}
