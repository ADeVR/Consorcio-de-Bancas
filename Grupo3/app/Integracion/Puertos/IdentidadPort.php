<?php
declare(strict_types=1);
namespace Caja\Integracion\Puertos;

use Caja\Core\UsuarioSesion;


/** M1 · Seguridad y usuarios. */
interface IdentidadPort
{
    public function usuarioActual(): ?UsuarioSesion;
    public function puede(string $permiso, ?int $idUsuario = null): bool;
    public function nombreDe(int $idUsuario): string;
    /** @return array<int,array{id_usuario:int,nombre:string,usuario:string}> para elegir agente entrante */
    public function agentesActivos(): array;
    public function idBancaDe(int $idUsuario): int;                 // hoy siempre 1
}
