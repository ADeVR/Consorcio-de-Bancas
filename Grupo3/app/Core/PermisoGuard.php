<?php
declare(strict_types=1);
namespace Caja\Core;



final class PermisoGuard
{
    /** Pregunta a IdentidadPort; si no: audita acceso.denegado y lanza SIN_PERMISO (SESION_EXPIRADA si no hay usuario). */
    public static function exigir(string $permiso): UsuarioSesion
    {
        $id = Contenedor::puerto('identidad');
        $u  = $id->usuarioActual() ?? throw new CajaException('SESION_EXPIRADA');
        if (!$u->activo || !$id->puede($permiso, $u->id_usuario)) {
            Contenedor::puerto('auditoria')->registrar([
                'id_usuario' => $u->id_usuario, 'accion' => 'acceso.denegado',
                'entidad' => 'permiso', 'id_registro' => $permiso, 'detalle' => $_SERVER['REQUEST_URI'] ?? '',
            ]);
            throw new CajaException('SIN_PERMISO');
        }
        return $u;
    }
}
