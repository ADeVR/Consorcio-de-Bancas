<?php
declare(strict_types=1);
namespace Caja\Core;



final class Csrf
{
    public static function token(): string
    {
        $t = Sesion::get('_csrf');
        if (!is_string($t) || $t === '') {
            $t = bin2hex(random_bytes(32));
            Sesion::set('_csrf', $t);
        }
        return $t;
    }

    /** Regenerar al cambiar de usuario simulado. */
    public static function regenerar(): string
    {
        Sesion::olvidar('_csrf');
        return self::token();
    }

    /** Compara la cabecera X-CSRF-Token. Si falla: SESION_EXPIRADA (el código CSRF no existe en el catálogo). */
    public static function verificar(): void
    {
        $enviado = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!is_string($enviado) || $enviado === '' || !hash_equals(self::token(), $enviado)) {
            throw new CajaException('SESION_EXPIRADA');
        }
    }
}
