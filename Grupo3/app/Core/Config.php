<?php
declare(strict_types=1);
namespace Caja\Core;

/** Lectura (con caché) de config/*.php. */
final class Config
{
    private static array $cache = [];

    public static function raiz(): string { return dirname(__DIR__, 2); }

    public static function get(string $nombre): array
    {
        if (!isset(self::$cache[$nombre])) {
            $ruta = self::raiz() . '/config/' . $nombre . '.php';
            if (!is_file($ruta)) throw new \RuntimeException("Falta config/{$nombre}.php");
            self::$cache[$nombre] = require $ruta;
        }
        return self::$cache[$nombre];
    }

    /** Nombre de tabla externa (sim_* hoy, real mañana) desde integracion.php; solo [a-z_]. */
    public static function tabla(string $clave): string
    {
        $nombre = self::get('integracion')['tablas_externas'][$clave] ?? '';
        if (!preg_match('/^[a-z_]{1,40}$/', $nombre)) throw new \RuntimeException("Tabla externa inválida: {$clave}");
        return '`' . $nombre . '`';
    }
}
