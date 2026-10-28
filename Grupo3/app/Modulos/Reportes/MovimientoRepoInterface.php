<?php
declare(strict_types=1);
namespace Caja\Modulos\Reportes;

/** Contrato único del reporte. Hoy lo cumple MovimientoRepoSimulado; al conectar MySQL, MovimientoRepo. */
interface MovimientoRepoInterface
{
    /**
     * @param array{desde:string,hasta:string,tipo?:string,estado?:string,ticket?:string,id_usuario?:int} $f
     * @return list<array<string,mixed>> filas ordenadas por fecha_hora; importes como texto "0.00"
     */
    public function buscar(array $f): array;
}
