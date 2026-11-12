<?php

/**
 * Auditoría automática del módulo Seguridad.
 * Registra eventos y envía una alerta Gmail al tercer intento fallido
 * del mismo usuario dentro de una ventana de 10 minutos.
 *
 * No registra contraseñas ni tokens CSRF.
 */

if (!defined("CONSORCIO_AUDIT_PREPEND")) {
    define("CONSORCIO_AUDIT_PREPEND", true);

    /**
     * Lee la respuesta SMTP completa y verifica el código esperado.
     */
    function consorcioSmtpRespuesta($socket, array $codigosEsperados): string
    {
        $respuesta = "";
        do {
            $linea = fgets($socket, 515);
            if ($linea === false) {
                throw new RuntimeException("No se recibió respuesta del servidor SMTP.");
            }
            $respuesta .= $linea;
        } while (isset($linea[3]) && $linea[3] === "-");

        $codigo = (int)substr($respuesta, 0, 3);
        if (!in_array($codigo, $codigosEsperados, true)) {
            throw new RuntimeException("Respuesta SMTP inesperada: " . trim($respuesta));
        }

        return $respuesta;
    }

    /**
     * Envía un correo de texto mediante Gmail SMTP y contraseña de aplicación.
     */
    function consorcioEnviarGmail(
        string $remitente,
        string $destinatario,
        string $contrasenaAplicacion,
        string $asunto,
        string $cuerpo
    ): void {
        $socket = stream_socket_client(
            "tcp://smtp.gmail.com:587",
            $errno,
            $error,
            20,
            STREAM_CLIENT_CONNECT
        );

        if (!$socket) {
            throw new RuntimeException("No se pudo conectar con Gmail: " . $error);
        }

        stream_set_timeout($socket, 20);

        try {
            consorcioSmtpRespuesta($socket, [220]);

            fwrite($socket, "EHLO consorciodebancas.local\r\n");
            consorcioSmtpRespuesta($socket, [250]);

            fwrite($socket, "STARTTLS\r\n");
            consorcioSmtpRespuesta($socket, [220]);

            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new RuntimeException("No se pudo establecer la conexión TLS con Gmail.");
            }

            fwrite($socket, "EHLO consorciodebancas.local\r\n");
            consorcioSmtpRespuesta($socket, [250]);

            fwrite($socket, "AUTH LOGIN\r\n");
            consorcioSmtpRespuesta($socket, [334]);

            fwrite($socket, base64_encode($remitente) . "\r\n");
            consorcioSmtpRespuesta($socket, [334]);

            fwrite($socket, base64_encode($contrasenaAplicacion) . "\r\n");
            consorcioSmtpRespuesta($socket, [235]);

            fwrite($socket, "MAIL FROM:<" . $remitente . ">\r\n");
            consorcioSmtpRespuesta($socket, [250]);

            fwrite($socket, "RCPT TO:<" . $destinatario . ">\r\n");
            consorcioSmtpRespuesta($socket, [250, 251]);

            fwrite($socket, "DATA\r\n");
            consorcioSmtpRespuesta($socket, [354]);

            $asuntoCodificado = "=?UTF-8?B?" . base64_encode($asunto) . "?=";
            $cuerpo = str_replace(["\r\n", "\r"], "\n", $cuerpo);
            $cuerpo = str_replace("\n.", "\n..", $cuerpo);

            $mensaje =
                "From: " . $remitente . "\r\n" .
                "To: " . $destinatario . "\r\n" .
                "Subject: " . $asuntoCodificado . "\r\n" .
                "MIME-Version: 1.0\r\n" .
                "Content-Type: text/plain; charset=UTF-8\r\n" .
                "Content-Transfer-Encoding: 8bit\r\n" .
                "\r\n" .
                str_replace("\n", "\r\n", $cuerpo) .
                "\r\n.";

            fwrite($socket, $mensaje . "\r\n");
            consorcioSmtpRespuesta($socket, [250]);

            fwrite($socket, "QUIT\r\n");
            consorcioSmtpRespuesta($socket, [221]);
        } finally {
            fclose($socket);
        }
    }

    /**
     * Comprueba si ya se envió esta alerta y guarda su identificador.
     */
    function consorcioProcesarAlertaIntentos(
        string $archivoAuditoria,
        string $usuarioIntentado,
        string $fechaIntento,
        string $directorioDatos
    ): void {
        $configPath = $directorioDatos . DIRECTORY_SEPARATOR . "correo_config.json";
        $estadoPath = $directorioDatos . DIRECTORY_SEPARATOR . "alertas_enviadas.json";

        if (!is_file($configPath) || !is_readable($configPath)) {
            return;
        }

        $ahora = strtotime($fechaIntento);
        if ($ahora === false) {
            return;
        }

        // Contar intentos fallidos del mismo usuario durante los últimos 10 minutos.
        $intentos = [];
        $lineas = @file($archivoAuditoria, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($lineas === false) {
            return;
        }

        foreach ($lineas as $linea) {
            $dato = json_decode($linea, true);

            if (
                !is_array($dato) ||
                ($dato["evento"] ?? "") !== "INTENTO_INICIO_SESION" ||
                ($dato["estado"] ?? "") !== "NO_AUTENTICADO" ||
                (string)($dato["usuario_intentado"] ?? "") !== $usuarioIntentado
            ) {
                continue;
            }

            $marcaTiempo = strtotime((string)($dato["fecha"] ?? ""));
            if ($marcaTiempo !== false && $marcaTiempo >= ($ahora - 600) && $marcaTiempo <= $ahora) {
                $intentos[] = $marcaTiempo;
            }
        }

        // La alerta se dispara exactamente al alcanzar el tercer intento.
        if (count($intentos) < 3) {
            return;
        }

        sort($intentos);
        // Se usa el tercer intento para conservar el mismo ID y reintentar
        // el envío si Gmail no estuvo disponible en la primera ocasión.
        $idAlerta = hash("sha256", $usuarioIntentado . "|" . $intentos[2] . "|3|10");

        $estado = ["enviadas" => []];
        if (is_file($estadoPath)) {
            $contenidoEstado = json_decode((string)@file_get_contents($estadoPath), true);
            if (is_array($contenidoEstado) && isset($contenidoEstado["enviadas"]) && is_array($contenidoEstado["enviadas"])) {
                $estado = $contenidoEstado;
            }
        }

        if (in_array($idAlerta, $estado["enviadas"], true)) {
            return;
        }

        $config = json_decode((string)@file_get_contents($configPath), true);
        if (!is_array($config)) {
            return;
        }

        $remitente = trim((string)($config["remitente"] ?? ""));
        $destinatario = trim((string)($config["destinatario"] ?? ""));
        $contrasena = preg_replace('/\s+/', '', (string)($config["contrasena_aplicacion"] ?? ""));

        if (
            !filter_var($remitente, FILTER_VALIDATE_EMAIL) ||
            !filter_var($destinatario, FILTER_VALIDATE_EMAIL) ||
            $contrasena === ""
        ) {
            return;
        }

        $asunto = "Alerta de seguridad - Consorcio de Bancas";
        $cuerpo =
            "Se detectaron tres intentos fallidos de inicio de sesión para el mismo usuario.\n\n" .
            "Usuario intentado: " . $usuarioIntentado . "\n" .
            "Intentos fallidos: 3\n" .
            "Ventana de detección: 10 minutos\n" .
            "Primer intento: " . date(DATE_ATOM, $intentos[0]) . "\n" .
            "Tercer intento: " . date(DATE_ATOM, $intentos[2]) . "\n\n" .
            "Recomendación: revisar los registros de auditoría. Esta alerta no confirma por sí sola un ataque.\n\n" .
            "Mensaje automático del módulo de Seguridad del Consorcio de Bancas.";

        try {
            consorcioEnviarGmail($remitente, $destinatario, $contrasena, $asunto, $cuerpo);

            $estado["enviadas"][] = $idAlerta;
            $estado["enviadas"] = array_values(array_unique($estado["enviadas"]));

            @file_put_contents(
                $estadoPath,
                json_encode($estado, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
                LOCK_EX
            );
        } catch (Throwable $e) {
            // No mostrar datos SMTP ni credenciales al usuario del sistema.
            error_log("Consorcio de Bancas: no se pudo enviar alerta de auditoría: " . $e->getMessage());
        }
    }

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
            $falloCredenciales = !empty($_SESSION["auditar_intento_fallido"]);
            unset($_SESSION["auditar_intento_fallido"]);

            if ($falloCredenciales) {
                $evento = "INTENTO_INICIO_SESION";
                $estado = "NO_AUTENTICADO";
            } elseif (!empty($userId)) {
                $evento = "INICIO_SESION";
                $estado = "EXITOSO";
            } else {
                // Por ejemplo, token CSRF inválido: se registra, pero no cuenta
                // como intento de contraseña fallida.
                $evento = "FORMULARIO_LOGIN_RECHAZADO";
                $estado = "NO_CONTABILIZADO";
            }

            // Se guarda únicamente el nombre de usuario; nunca la contraseña.
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

        $directorioDatos = dirname(__DIR__) . DIRECTORY_SEPARATOR . "datos";
        $archivoAuditoria = $directorioDatos . DIRECTORY_SEPARATOR . "auditoria.jsonl";

        if (!is_dir($directorioDatos)) {
            @mkdir($directorioDatos, 0750, true);
        }

        $json = json_encode(
            $registro,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        if ($json !== false) {
            $guardado = @file_put_contents(
                $archivoAuditoria,
                $json . PHP_EOL,
                FILE_APPEND | LOCK_EX
            );

            // La alerta se procesa únicamente después de guardar el intento fallido.
            if (
                $guardado !== false &&
                $evento === "INTENTO_INICIO_SESION" &&
                $estado === "NO_AUTENTICADO" &&
                $usuarioIntentado !== ""
            ) {
                consorcioProcesarAlertaIntentos(
                    $archivoAuditoria,
                    $usuarioIntentado,
                    $registro["fecha"],
                    $directorioDatos
                );
            }
        }
    });
}
