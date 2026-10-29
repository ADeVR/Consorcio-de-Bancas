<?php
declare(strict_types=1);
namespace Caja\Core;

/** Log técnico en storage/logs/app.log (el detalle nunca se muestra al usuario). */
final class Registro
{
    public static function error(\Throwable $e): void
    {
        self::escribir($e::class . ': ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    }

    public static function escribir(string $linea): void
    {
        error_log('[' . date('c') . '] ' . $linea . PHP_EOL, 3, Config::raiz() . '/storage/logs/app.log');
    }
}
