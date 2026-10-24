
<?php
require_once __DIR__ . "/conexion.php";
require_once __DIR__ . "/funciones.php";

// Ruta principal del módulo de seguridad.
$rutaBase = "/ConsorcioDeBancas/Seguridad";

// Si ya inició sesión, enviarlo a su página.
if (!empty($_SESSION["usuario_id"])) {

    if (($_SESSION["rol"] ?? "") === "admin") {
        header("Location: " . $rutaBase . "/php/usuarios.php");
    } else {
        header("Location: " . $rutaBase . "/php/inicio.php");
    }

    exit;
}

$mensaje = "";

// Procesar el formulario.
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Verificar el token de seguridad.
    if (!csrfValido()) {

        $mensaje = "El formulario venció. Recarga la página e inténtalo otra vez.";

    } else {

        $nombre = trim((string)($_POST["usuario"] ?? ""));
        $clave = (string)($_POST["clave"] ?? "");

        // Buscar el usuario en MySQL remoto.
        $consulta = $conexion->prepare(
            "SELECT id, usuario, clave, rol, activo
             FROM usuarios
             WHERE usuario = ?
             LIMIT 1"
        );

        $consulta->execute([$nombre]);
        $usuario = $consulta->fetch();

        // Comprobar credenciales y estado de la cuenta.
        if (
            $usuario &&
            password_verify($clave, (string)$usuario["clave"]) &&
            (int)$usuario["activo"] === 1 &&
            in_array($usuario["rol"], ["admin", "agente"], true)
        ) {

            // Renovar la sesión.
            session_regenerate_id(true);

            $_SESSION["usuario_id"] = (int)$usuario["id"];
            $_SESSION["usuario"] = $usuario["usuario"];
            $_SESSION["rol"] = $usuario["rol"];

            // Generar un nuevo token CSRF.
            $_SESSION["token_csrf"] = bin2hex(random_bytes(32));

            // Redirigir según el rol.
            if ($usuario["rol"] === "admin") {

                header(
                    "Location: " . $rutaBase . "/php/usuarios.php"
                );

            } else {

                header(
                    "Location: " . $rutaBase . "/php/inicio.php"
                );

            }

            exit;

        } else {

            $mensaje = "Usuario o contraseña incorrectos, o cuenta inactiva.";

        }
    }
}

// Generar token para el formulario.
$token = tokenCsrf();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Iniciar sesión | Consorcio de Bancas</title>

    <link
        rel="stylesheet"
        href="<?= escapar($rutaBase) ?>/css/seguridad.css"
    >
</head>

<body class="pagina-login">

    <main class="login-contenedor">

        <section class="login-tarjeta">

            <div class="marca">CB</div>

            <h1>Consorcio de Bancas</h1>

            <p class="subtitulo">
                Inicia sesión para continuar
            </p>

            <?php if ($mensaje !== ""): ?>

                <p class="mensaje error" role="alert">
                    <?= escapar($mensaje) ?>
                </p>

            <?php endif; ?>

            <form
                method="POST"
                action="<?= escapar($rutaBase) ?>/php/login.php"
            >

                <input
                    type="hidden"
                    name="token_csrf"
                    value="<?= escapar($token) ?>"
                >

                <div class="campo">

                    <label for="usuario">
                        Usuario
                    </label>

                    <input
                        type="text"
                        id="usuario"
                        name="usuario"
                        maxlength="50"
                        autocomplete="username"
                        required
                        autofocus
                    >

                </div>

                <div class="campo">

                    <label for="clave">
                        Contraseña
                    </label>

                    <div class="campo-clave">

                        <input
                            type="password"
                            id="clave"
                            name="clave"
                            autocomplete="current-password"
                            required
                        >

                        <button
                            type="button"
                            id="mostrarClave"
                            class="boton-mostrar"
                        >
                            Ver
                        </button>

                    </div>

                </div>

                <button
                    class="boton boton-principal boton-ancho"
                    type="submit"
                >
                    Iniciar sesión
                </button>

            </form>

        </section>

        <footer>
            Sistema de administración de bancas
        </footer>

    </main>

    <script
        src="<?= escapar($rutaBase) ?>/js/seguridad.js"
        defer
    ></script>

</body>
</html>
