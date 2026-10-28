<?php
declare(strict_types=1);
namespace Caja\Integracion\Puertos;

/** Catálogo de loterías (sin dueño definido, D-18). */
interface CatalogoPort
{
    /** @return array<int,array{id_loteria:int,nombre:string,estado:string}> */
    public function loterias(): array;
}
