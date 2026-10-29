<?php
// GET: reporte cronológico. Misma respuesta que api_mock.js. La fuente la decide config/app.php → fuente_datos.
require __DIR__ . '/../../../app/bootstrap.php';

use Caja\Core\Config;
use Caja\Core\CajaException;
use Caja\Core\Contenedor;
use Caja\Core\PermisoGuard;
use Caja\Core\Peticion;
use Caja\Core\RespuestaJson;
use Caja\Modulos\Reportes\MovimientoRepo;
use Caja\Modulos\Reportes\MovimientoRepoSimulado;


Peticion::exigirMetodo('GET');
$u = PermisoGuard::exigir('reporte.consultar');

$hoy = Contenedor::puerto('hora')->ahora()->format('Y-m-d');
$f = [
    'desde' => (string) ($_GET['desde'] ?? $hoy), 'hasta' => (string) ($_GET['hasta'] ?? $hoy),
    'tipo' => (string) ($_GET['tipo'] ?? ''), 'estado' => (string) ($_GET['estado'] ?? ''),
    'ticket' => (string) ($_GET['ticket'] ?? ''), 'id_usuario' => (int) ($_GET['id_usuario'] ?? 0),
];
// Validar en el servidor: el navegador no es una barrera de seguridad.
foreach (['desde', 'hasta'] as $campo) {
    $fecha = \DateTimeImmutable::createFromFormat('!Y-m-d', $f[$campo]);
    if (!$fecha || $fecha->format('Y-m-d') !== $f[$campo]) {
        throw new CajaException('PARAMETROS_INVALIDOS', $campo);
    }
}
if ($f['desde'] > $f['hasta']) throw new CajaException('PARAMETROS_INVALIDOS', 'hasta');
if ($f['ticket'] !== '' && !preg_match('/^[A-Za-z0-9-]{1,30}$/D', $f['ticket'])) {
    throw new CajaException('PARAMETROS_INVALIDOS', 'ticket');
}
$tiposPermitidos = ['venta', 'pago', 'anulacion', 'entrega', 'retiro', 'intento_pago_rechazado', 'relevo', 'cierre'];
if ($f['tipo'] !== '' && !in_array($f['tipo'], $tiposPermitidos, true)) throw new CajaException('PARAMETROS_INVALIDOS', 'tipo');
if ($f['estado'] !== '' && !in_array($f['estado'], ['confirmado', 'informativo'], true)) throw new CajaException('PARAMETROS_INVALIDOS', 'estado');
foreach (['id_usuario', 'id_loteria', 'pagina', 'por_pagina'] as $campo) {
    if (isset($_GET[$campo]) && $_GET[$campo] !== '' && (!ctype_digit((string) $_GET[$campo]) || (int) $_GET[$campo] < 1)) {
        throw new CajaException('PARAMETROS_INVALIDOS', $campo);
    }
}
// Aún no existe integración del catálogo/relación de loterías en el repositorio PHP.
// No aceptar silenciosamente un filtro que la consulta SQL no puede aplicar.
if (!empty($_GET['id_loteria'])) throw new CajaException('PARAMETROS_INVALIDOS', 'id_loteria');
if ($u->rol !== 'admin') $f['id_usuario'] = $u->id_usuario;      // D-05: el agente solo ve lo suyo
$repo = Config::get('app')['fuente_datos'] === 'db' ? new MovimientoRepo() : new MovimientoRepoSimulado();
$filas = $repo->buscar($f);

// Totales en centavos enteros (nunca float).
$cent = static fn (string $t): int => (int) str_replace('.', '', $t);
$suma = static function (string $ef) use ($filas, $cent): string {
    $c = 0;
    foreach ($filas as $r) if ($r['efecto'] === $ef && $r['estado'] === 'confirmado') $c += $cent($r['importe']);
    return sprintf('%d.%02d', intdiv($c, 100), $c % 100);
};

$pedido    = (int) ($_GET['por_pagina'] ?? 50);
$porPagina = in_array($pedido, [25, 50, 100], true) ? $pedido : 50;
$paginas = max(1, (int) ceil(count($filas) / $porPagina));
$pagina = min(max(1, (int) ($_GET['pagina'] ?? 1)), $paginas);

RespuestaJson::ok([
    'filas' => array_slice($filas, ($pagina - 1) * $porPagina, $porPagina),
    'totales' => ['filtro' => ['entradas' => $suma('entrada'), 'salidas' => $suma('salida')]],
    'paginacion' => ['pagina' => $pagina, 'paginas' => $paginas, 'total' => count($filas)],
    'filtros_disponibles' => ['loterias' => [], 'usuarios' => array_values(array_unique(array_map(
        static fn ($r) => ['id' => $r['id_usuario'], 'usuario' => $r['usuario']], $filas), SORT_REGULAR))],
]);
