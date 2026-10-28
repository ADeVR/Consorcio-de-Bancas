<?php
// GET: estado de caja (saldo, turno activo, últimos movimientos). La fuente la decide config/app.php → fuente_datos.
require __DIR__ . '/../../../app/bootstrap.php';

use Caja\Core\CajaException;
use Caja\Core\Config;
use Caja\Core\Contenedor;
use Caja\Core\PermisoGuard;
use Caja\Core\Peticion;
use Caja\Core\RespuestaJson;
use Caja\Modulos\Caja\EstadoRepo;
use Caja\Modulos\Caja\EstadoRepoSimulado;
use Caja\Modulos\Reportes\MovimientoRepo;
use Caja\Modulos\Reportes\MovimientoRepoSimulado;

Peticion::exigirMetodo('GET');
$u  = PermisoGuard::exigir('caja.ver_saldo');
$db = Config::get('app')['fuente_datos'] === 'db';

$caja = ($db ? new EstadoRepo() : new EstadoRepoSimulado())->actual(Contenedor::puerto('identidad')->idBancaDe($u->id_usuario));
if ($caja === null) throw new CajaException('CAJA_SIN_TURNO');

$veSaldo = $u->rol === 'admin' || $caja['turno']['id_usuario'] === $u->id_usuario;   // D-05: el agente solo ve el saldo si es el activo
$ultimos = [];
if ($veSaldo) {
    $hoy = Contenedor::puerto('hora')->ahora()->format('Y-m-d');
    $filas = ($db ? new MovimientoRepo() : new MovimientoRepoSimulado())->buscar(['desde' => $hoy, 'hasta' => $hoy]);
    $ultimos = array_slice(array_reverse($filas), 0, 5);
}

RespuestaJson::ok([
    'banca'       => $caja['banca'],
    've_saldo'    => $veSaldo,
    'saldo'       => $veSaldo ? $caja['saldo'] : null,
    'actualizado' => $veSaldo ? $caja['actualizado'] : null,
    'turno'       => ['id' => $caja['turno']['id'], 'usuario' => $caja['turno']['usuario'], 'desde' => $caja['turno']['desde'],
                      'fondo_inicial' => $veSaldo ? $caja['turno']['fondo_inicial'] : null],
    'ultimos'     => $ultimos,
    'pendientes'  => $veSaldo ? ['efectivo' => $caja['pendientes_efectivo'], 'cierres' => $caja['pendientes_cierres']] : ['efectivo' => 0, 'cierres' => 0],
]);
