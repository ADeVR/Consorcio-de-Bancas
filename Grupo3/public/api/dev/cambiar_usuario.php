<?php
// SOLO modo simulado: cambia el usuario activo del selector de la barra superior.
require __DIR__ . '/../../../app/bootstrap.php';

use Caja\Core\CajaException;
use Caja\Core\Config;
use Caja\Core\Contenedor;
use Caja\Core\Csrf;
use Caja\Core\Peticion;
use Caja\Core\RespuestaJson;


Peticion::exigirMetodo('POST');
Csrf::verificar();
$ident = Contenedor::puerto('identidad');
if (Config::get('integracion')['identidad'] === 'real' || Config::get('app')['modo'] !== 'simulado' || !method_exists($ident, 'seleccionar')) throw new CajaException('SIN_PERMISO');
$d = Peticion::validar(['id_usuario' => 'entero+']);
$ident->seleccionar($d['id_usuario']);
RespuestaJson::ok(['csrf' => Csrf::regenerar()]);
