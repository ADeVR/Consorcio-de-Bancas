<?php
function escapar($v): string
{
    return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}
function exigirSesion(): void
{
    if (empty($_SESSION["usuario_id"])) {
        header("Location: /Seguridad/php/login.php");
        exit;
    }
}
function exigirAdmin(): void
{
    if (empty($_SESSION["usuario_id"]) || ($_SESSION["rol"] ?? "") !== "admin") {
        header("Location: /Seguridad/php/login.php");
        exit;
    }
}
function tokenCsrf(): string
{
    if (empty($_SESSION["token_csrf"])) $_SESSION["token_csrf"] = bin2hex(random_bytes(32));
    return $_SESSION["token_csrf"];
}
function csrfValido(): bool
{
    return isset($_SESSION["token_csrf"], $_POST["token_csrf"])
        && is_string($_POST["token_csrf"])
        && hash_equals($_SESSION["token_csrf"], $_POST["token_csrf"]);
}



/**
 * Catálogo de módulos disponibles en el sistema.
 */
function modulosSistema(): array
{
    return [
        "Configuracion" => "Configuración",
        "Apertura" => "Apertura",
        "Jugar" => "Jugar",
        "Cierre" => "Cierre",
        "Pagos" => "Pagos"
    ];
}

/**
 * Devuelve los módulos asignados a un usuario.
 * El administrador tiene acceso a todos.
 */
function obtenerPermisosUsuario(PDO $conexion, int $usuarioId): array
{
    if (
        (int)($_SESSION["usuario_id"] ?? 0) === $usuarioId &&
        ($_SESSION["rol"] ?? "") === "admin"
    ) {
        return array_keys(modulosSistema());
    }

    $q = $conexion->prepare(
        "SELECT modulo FROM usuario_permisos WHERE usuario_id = ?"
    );
    $q->execute([$usuarioId]);

    return array_column($q->fetchAll(), "modulo");
}

/**
 * Comprueba si el usuario actual puede entrar a un módulo.
 */
function tienePermisoModulo(PDO $conexion, string $modulo): bool
{
    if (empty($_SESSION["usuario_id"])) {
        return false;
    }

    if (($_SESSION["rol"] ?? "") === "admin") {
        return true;
    }

    if (!array_key_exists($modulo, modulosSistema())) {
        return false;
    }

    $q = $conexion->prepare(
        "SELECT 1
         FROM usuario_permisos
         WHERE usuario_id = ? AND modulo = ?
         LIMIT 1"
    );
    $q->execute([(int)$_SESSION["usuario_id"], $modulo]);

    return (bool)$q->fetchColumn();
}

/**
 * Bloquea en el servidor el acceso a un módulo no autorizado.
 */
function exigirPermisoModulo(string $modulo): void
{
    global $conexion;

    if (empty($_SESSION["usuario_id"])) {
        header("Location: /ConsorcioDeBancas/Seguridad/php/login.php");
        exit;
    }

    if (!tienePermisoModulo($conexion, $modulo)) {
        http_response_code(403);
        exit("Acceso denegado: no tienes permiso para entrar a este módulo.");
    }
}
