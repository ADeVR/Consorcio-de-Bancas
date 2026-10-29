<?php
declare(strict_types=1);
namespace Caja\Integracion\Puertos;

/** M5 · Consulta base de ventas. */
interface VentasBasePort
{
    /**
     * @param string $nivel 'loteria' (empresa) | 'sorteo'
     * @return array<int,array<string,mixed>> id_loteria, loteria, [id_sorteo, sorteo], cantidad_jugadas, brutas,
     *         anuladas_de_ventas_del_periodo, anuladas_en_el_periodo, cantidad_anuladas
     */
    public function ventasPorLoteria(\DateTimeInterface $desde, \DateTimeInterface $hasta, string $nivel = 'loteria', array $filtros = []): array;
}
