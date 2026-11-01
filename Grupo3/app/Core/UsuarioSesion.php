<?php
declare(strict_types=1);
namespace Caja\Core;

/** Usuario autenticado según IdentidadPort (M1). */
final class UsuarioSesion
{
    public function __construct(
        public int $id_usuario,
        public string $nombre,
        public string $usuario,
        public string $rol,          // superadmin | admin | agente
        public bool $activo,
    ) {}
}
