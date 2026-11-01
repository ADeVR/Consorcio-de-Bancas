<?php
declare(strict_types=1);
namespace Caja\Integracion\Adaptadores\Simulado;

use Caja\Core\CajaException;
use Caja\Core\Config;
use Caja\Core\Db;
use Caja\Core\Sesion;
use Caja\Core\UsuarioSesion;
use Caja\Core\Xml;
use Caja\Integracion\Puertos\IdentidadPort;


/** M1 simulado: usuarios en sim_usuario, usuario activo en sesión, permisos desde config/permisos.xml. */
final class IdentidadSimulado implements IdentidadPort
{
    private const CLAVE_SESION = 'id_usuario_sim';
    private static array $filas = [];

    /** Selector de usuario simulado (solo modo simulado). */
    public function seleccionar(int $idUsuario): void
    {
        $f = $this->fila($idUsuario);
        if ($f === null || $f['estado'] !== 'activo') throw new CajaException('PARAMETROS_INVALIDOS', 'id_usuario');
        Sesion::set(self::CLAVE_SESION, $idUsuario);
    }

    /** @return array<int,array{id_usuario:int,nombre:string,usuario:string,rol:string}> */
    public function usuariosSimulados(): array
    {
        $t  = Config::tabla('usuario');
        $st = Db::pdo()->query("SELECT id_usuario, nombre, usuario, rol FROM {$t} WHERE estado = 'activo' ORDER BY id_usuario");
        return array_map(fn(array $f): array => ['id_usuario' => (int) $f['id_usuario']] + $f, $st->fetchAll());
    }

    public function usuarioActual(): ?UsuarioSesion
    {
        $id = Sesion::get(self::CLAVE_SESION);
        if (!is_int($id)) return null;
        $f = $this->fila($id);
        return $f === null ? null
            : new UsuarioSesion((int) $f['id_usuario'], $f['nombre'], $f['usuario'], $f['rol'], $f['estado'] === 'activo');
    }

    public function puede(string $permiso, ?int $idUsuario = null): bool
    {
        $idUsuario ??= $this->usuarioActual()?->id_usuario;
        $f = $idUsuario === null ? null : $this->fila($idUsuario);
        if ($f === null || $f['estado'] !== 'activo') return false;
        return (Xml::permisos()[$permiso][$f['rol']] ?? 'ninguno') !== 'ninguno';
    }

    public function nombreDe(int $idUsuario): string { return $this->fila($idUsuario)['nombre'] ?? ''; }

    public function agentesActivos(): array
    {
        return array_values(array_map(
            fn(array $u): array => ['id_usuario' => $u['id_usuario'], 'nombre' => $u['nombre'], 'usuario' => $u['usuario']],
            array_filter($this->usuariosSimulados(), fn(array $u): bool => $u['rol'] === 'agente')
        ));
    }

    public function idBancaDe(int $idUsuario): int { return (int) Config::get('app')['id_banca_defecto']; }

    private function fila(int $id): ?array
    {
        if (!array_key_exists($id, self::$filas)) {
            $t  = Config::tabla('usuario');
            $st = Db::pdo()->prepare("SELECT id_usuario, nombre, usuario, rol, estado FROM {$t} WHERE id_usuario = ?");
            $st->execute([$id]);
            self::$filas[$id] = $st->fetch() ?: null;
        }
        return self::$filas[$id];
    }
}
