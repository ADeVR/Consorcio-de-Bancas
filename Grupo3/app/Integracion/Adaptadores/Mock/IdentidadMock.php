<?php
declare(strict_types=1);
namespace Caja\Integracion\Adaptadores\Mock;

use Caja\Core\CajaException;
use Caja\Core\Config;
use Caja\Core\Sesion;
use Caja\Core\UsuarioSesion;
use Caja\Core\Xml;
use Caja\Integracion\Puertos\IdentidadPort;


/** M1 sin BD (fase visual): usuarios fijos del §17, usuario activo en sesión, permisos desde config/permisos.xml. Se elimina al conectar la BD. */
final class IdentidadMock implements IdentidadPort
{
    private const CLAVE_SESION = 'id_usuario_sim';
    private const USUARIOS = [
        1 => ['nombre' => 'Superadministrador', 'usuario' => 'superadmin1', 'rol' => 'superadmin'],
        2 => ['nombre' => 'Administrador Uno',   'usuario' => 'admin1',      'rol' => 'admin'],
        3 => ['nombre' => 'Agente Uno',          'usuario' => 'agente1',     'rol' => 'agente'],
        4 => ['nombre' => 'Agente Dos',          'usuario' => 'agente2',     'rol' => 'agente'],
    ];

    public function seleccionar(int $idUsuario): void
    {
        if (!isset(self::USUARIOS[$idUsuario])) throw new CajaException('PARAMETROS_INVALIDOS', 'id_usuario');
        Sesion::set(self::CLAVE_SESION, $idUsuario);
    }

    /** @return array<int,array{id_usuario:int,nombre:string,usuario:string,rol:string}> */
    public function usuariosSimulados(): array
    {
        $r = [];
        foreach (self::USUARIOS as $id => $u) $r[] = ['id_usuario' => $id] + $u;
        return $r;
    }

    public function usuarioActual(): ?UsuarioSesion
    {
        $id = Sesion::get(self::CLAVE_SESION);
        $u  = is_int($id) ? (self::USUARIOS[$id] ?? null) : null;
        return $u === null ? null : new UsuarioSesion($id, $u['nombre'], $u['usuario'], $u['rol'], true);
    }

    public function puede(string $permiso, ?int $idUsuario = null): bool
    {
        $idUsuario ??= $this->usuarioActual()?->id_usuario;
        $u = $idUsuario === null ? null : (self::USUARIOS[$idUsuario] ?? null);
        return $u !== null && (Xml::permisos()[$permiso][$u['rol']] ?? 'ninguno') !== 'ninguno';
    }

    public function nombreDe(int $idUsuario): string { return self::USUARIOS[$idUsuario]['nombre'] ?? ''; }

    public function agentesActivos(): array
    {
        $r = [];
        foreach (self::USUARIOS as $id => $u) {
            if ($u['rol'] === 'agente') $r[] = ['id_usuario' => $id, 'nombre' => $u['nombre'], 'usuario' => $u['usuario']];
        }
        return $r;
    }

    public function idBancaDe(int $idUsuario): int { return (int) Config::get('app')['id_banca_defecto']; }
}
