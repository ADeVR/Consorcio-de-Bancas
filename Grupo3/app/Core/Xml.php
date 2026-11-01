<?php
declare(strict_types=1);
namespace Caja\Core;

/** Lectura de los catálogos XML (errores y permisos). */
final class Xml
{
    private static ?array $errores  = null;
    private static ?array $permisos = null;

    public static function cargar(string $ruta): \SimpleXMLElement
    {
        $previo = libxml_use_internal_errors(true);
        $xml = simplexml_load_file($ruta, \SimpleXMLElement::class, LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previo);
        if ($xml === false) throw new \RuntimeException("XML inválido: {$ruta}");
        return $xml;
    }

    /** @return array{http:int,severidad:string,mensaje:string} (código desconocido → ERROR_INTERNO) */
    public static function datosError(string $codigo): array
    {
        if (self::$errores === null) {
            self::$errores = [];
            foreach (self::cargar(Config::raiz() . '/config/errores.xml')->error as $e) {
                self::$errores[(string) $e['codigo']] = [
                    'http'      => (int) $e['http'],
                    'severidad' => (string) $e['severidad'],
                    'mensaje'   => trim((string) $e->mensaje),
                ];
            }
        }
        return self::$errores[$codigo] ?? self::$errores['ERROR_INTERNO'];
    }

    public static function mensajeDe(string $codigo): string { return self::datosError($codigo)['mensaje']; }

    /** @return array<string,array<string,string>> permiso => [rol => alcance] */
    public static function permisos(): array
    {
        if (self::$permisos === null) {
            self::$permisos = [];
            foreach (self::cargar(Config::raiz() . '/config/permisos.xml')->permiso as $p) {
                foreach ($p->rol as $r) self::$permisos[(string) $p['codigo']][(string) $r['codigo']] = (string) $r['alcance'];
            }
        }
        return self::$permisos;
    }
}
