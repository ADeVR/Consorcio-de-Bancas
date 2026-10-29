<?php
declare(strict_types=1);
namespace Caja\Core;



final class RespuestaJson
{
    public static function ok(mixed $data = null): void
    {
        self::emitir(200, ['ok' => true, 'data' => $data]);
    }

    /** HTTP y severidad salen de config/errores.xml (Anexo C). */
    public static function error(CajaException $e): void
    {
        $d    = Xml::datosError($e->codigo);
        $err  = ['codigo' => $e->codigo, 'mensaje' => $d['mensaje'], 'severidad' => $d['severidad']];
        if ($e->campo !== null) $err['campo'] = $e->campo;
        self::emitir($d['http'], ['ok' => false, 'error' => $err]);
    }

    private static function emitir(int $http, array $cuerpo): void
    {
        if (!headers_sent()) {
            http_response_code($http);
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode($cuerpo, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }
}
