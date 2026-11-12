<?php
/**
 * Auditoría automática del módulo Seguridad.
 * Se ejecuta mediante auto_prepend_file antes de cada PHP de esta carpeta.
 * No registra contraseñas, tokens CSRF ni parámetros de URL.
 */
if (!defined("CONSORCIO_AUDIT_PREPEND")) {
    define("CONSORCIO_AUDIT_PREPEND", true);

    register_shutdown_function(static function (): void {
        $uri = (string)($_SERVER["REQUEST_URI"] ?? "");
        $path = (string)(parse_url($uri, PHP_URL_PATH) ?? "");
        $method = strtoupper((string)($_SERVER["REQUEST_METHOD"] ?? "GET"));
        $script = basename((string)($_SERVER["SCRIPT_NAME"] ?? ""));
        $userId = $_SESSION["usuario_id"] ?? null;
        $usuario = $_SESSION["usuario"] ?? null;
        $rol = $_SESSION["rol"] ?? null;

        $evento = "ACCESO_PAGINA";
        $estado = "REGISTRADO";
        $usuarioIntentado = null;

        if ($script === "login.php" && $method === "POST") {
            $evento = empty($userId) ? "INTENTO_INICIO_SESION" : "INICIO_SESION";
            $estado = empty($userId) ? "NO_AUTENTICADO" : "EXITOSO";

            // Se guarda únicamente el nombre de usuario enviado, nunca la contraseña.
            $usuarioIntentado = trim((string)($_POST["usuario"] ?? ""));
            $usuarioIntentado = substr($usuarioIntentado, 0, 50);
        } elseif ($script === "cerrar_sesion.php" && $method === "POST") {
            $evento = "CIERRE_SESION";
            $estado = "SOLICITADO";
        } elseif ($script === "usuarios.php" && $method === "POST") {
            $evento = "GESTION_USUARIOS";
        } elseif ($script === "permisos.php" && $method === "POST") {
            $evento = "CAMBIO_PERMISOS";
        }

        $registro = [
            "fecha" => date(DATE_ATOM),
            "evento" => $evento,
            "estado" => $estado,
            "metodo" => $method,
            "pagina" => $script,
            "ruta" => $path,
            "usuario_id" => $userId !== null ? (int)$userId : null,
            "usuario" => is_string($usuario) ? $usuario : null,
            "usuario_intentado" => $usuarioIntentado,
            "rol" => is_string($rol) ? $rol : null,
            "codigo_http" => http_response_code() ?: 200
        ];

        $archivo = dirname(__DIR__) . DIRECTORY_SEPARATOR . "datos"
            . DIRECTORY_SEPARATOR . "auditoria.jsonl";

        $json = json_encode(
            $registro,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        if ($json !== false) {
            @file_put_contents($archivo, $json . PHP_EOL, FILE_APPEND | LOCK_EX);
        }
    });
}
