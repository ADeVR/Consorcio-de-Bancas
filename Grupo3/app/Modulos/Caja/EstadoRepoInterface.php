<?php
declare(strict_types=1);
namespace Caja\Modulos\Caja;

/** Contrato del estado de caja. Hoy lo cumple EstadoRepoSimulado; con MySQL, EstadoRepo. */
interface EstadoRepoInterface
{
    /**
     * @return ?array{banca:string,saldo:string,actualizado:string,turno:array<string,mixed>,pendientes_efectivo:int,pendientes_cierres:int}
     *         null = no hay turno activo
     */
    public function actual(int $idBanca): ?array;
}
