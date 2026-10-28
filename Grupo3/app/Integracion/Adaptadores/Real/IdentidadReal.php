<?php
declare(strict_types=1);
namespace Caja\Integracion\Adaptadores\Real;

use Caja\Core\Config;
use Caja\Core\UsuarioSesion;
use Caja\Integracion\Puertos\IdentidadPort;

/** Adaptador M1: reutiliza sesión y permisos del repositorio principal.
 * La asignación usuario -> banca sigue pendiente del contrato intergrupal.
 */
final class IdentidadReal implements IdentidadPort
{
    private function seguridad(): \PDO
    {
        require_once dirname(__DIR__, 4) . '/../Seguridad/php/funciones.php';
        static $pdo = null;
        if ($pdo === null) {
            require dirname(__DIR__, 4) . '/../Seguridad/php/conexion.php';
            $pdo = $conexion;
        }
        return $pdo;
    }

    public function usuarioActual(): ?UsuarioSesion
    {
        if (empty($_SESSION['usuario_id'])) return null;
        $pdo = $this->seguridad();
        $q = $pdo->prepare('SELECT id, usuario, rol, activo FROM usuarios WHERE id = ? LIMIT 1');
        $q->execute([(int) $_SESSION['usuario_id']]);
        $u = $q->fetch(\PDO::FETCH_ASSOC);
        if (!$u || !(bool)$u['activo'] || !in_array($u['rol'], ['admin','agente'], true)) return null;
        return new UsuarioSesion((int)$u['id'], $u['usuario'], $u['usuario'], $u['rol'], true);
    }

    public function puede(string $permiso, ?int $idUsuario = null): bool
    {
        $u = $this->usuarioActual();
        if ($u === null || ($idUsuario !== null && $idUsuario !== $u->id_usuario)) return false;
        $pdo = $this->seguridad();
        return \tienePermisoModulo($pdo, 'Apertura');
    }

    public function nombreDe(int $idUsuario): string
    {
        $q = $this->seguridad()->prepare('SELECT usuario FROM usuarios WHERE id = ? AND activo = 1');
        $q->execute([$idUsuario]);
        return (string)($q->fetchColumn() ?: '');
    }

    public function agentesActivos(): array
    {
        $q = $this->seguridad()->query("SELECT id, usuario FROM usuarios WHERE activo = 1 AND rol = 'agente' ORDER BY usuario");
        return array_map(static fn(array $u): array => [
            'id_usuario' => (int)$u['id'], 'nombre' => $u['usuario'], 'usuario' => $u['usuario']
        ], $q->fetchAll(\PDO::FETCH_ASSOC));
    }

    public function idBancaDe(int $idUsuario): int
    {
        // Temporal: la tabla usuarios del repositorio no almacena banca_id.
        // No habilitar operaciones reales multibanca hasta acordar el contrato.
        return (int) Config::get('app')['id_banca_defecto'];
    }
}
