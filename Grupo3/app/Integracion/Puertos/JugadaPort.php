<?php
declare(strict_types=1);
namespace Caja\Integracion\Puertos;

/** M2 · Jugadas (solo lectura). */
interface JugadaPort
{
    /** @return ?array{id_jugada:int,ticket:string,id_agente:int,estado:string,total_cobrar:string} */
    public function porId(int $idJugada): ?array;
    /** @return array<int,array<string,mixed>> id_sorteo, id_loteria, monto_apostado … */
    public function lineas(int $idJugada): array;
    public function ticketDe(int $idJugada): ?string;
}
