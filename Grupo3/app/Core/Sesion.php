<?php
declare(strict_types=1);
namespace Caja\Core;

/** Único acceso a $_SESSION (junto con Csrf e IdentidadSimulado). */
final class Sesion
{
    public static function get(string $clave, mixed $defecto = null): mixed { return $_SESSION[$clave] ?? $defecto; }
    public static function set(string $clave, mixed $valor): void           { $_SESSION[$clave] = $valor; }
    public static function olvidar(string $clave): void                     { unset($_SESSION[$clave]); }

    /** Vacía la sesión si pasó el tiempo de inactividad; si no, renueva la marca. */
    public static function renovar(int $minutos): void
    {
        $ahora = time();
        if (isset($_SESSION['_ultimo']) && $ahora - (int) $_SESSION['_ultimo'] > $minutos * 60) $_SESSION = [];
        $_SESSION['_ultimo'] = $ahora;
    }
}
