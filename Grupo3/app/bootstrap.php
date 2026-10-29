<?php
declare(strict_types=1);

use Caja\Core\CajaException;
use Caja\Core\Config;
use Caja\Core\Registro;
use Caja\Core\RespuestaJson;
use Caja\Core\Sesion;
require __DIR__ . '/autoload.php';


$app = Config::get('app');
date_default_timezone_set($app['zona_horaria']);               // America/Santo_Domingo
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (\Caja\Core\Config::get('integracion')['identidad'] !== 'real') {
    Sesion::renovar((int) $app['sesion_inactividad_min']);
}

set_exception_handler(function (Throwable $e): void {
    $esApi = str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/api/') || PHP_SAPI === 'cli';
    if (!($e instanceof CajaException)) {
        Registro::error($e);
        $e = new CajaException('ERROR_INTERNO');
    }
    if ($esApi) { RespuestaJson::error($e); return; }
    http_response_code(500);                                      // páginas: texto plano, sin HTML fuera de public/
    header('Content-Type: text/plain; charset=utf-8');
    echo \Caja\Core\Xml::mensajeDe($e->codigo);
});
