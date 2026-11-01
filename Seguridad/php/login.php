<?php
require_once __DIR__ . "/conexion.php";
require_once __DIR__ . "/funciones.php";

// Ruta principal del módulo de seguridad.
$rutaBase = "/ConsorcioDeBancas/Seguridad";

// Si ya inició sesión, enviarlo a su página.
if (!empty($_SESSION["usuario_id"])) {

    if (($_SESSION["rol"] ?? "") === "admin") {
        header("Location: " . $rutaBase . "/php/inicio.php");
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
                    "Location: " . $rutaBase . "/php/inicio.php"
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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión | Consorcio de Bancas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= escapar($rutaBase) ?>/css/estilos.css?v=3">
</head>

<body class="pagina-login">
    <main class="container min-vh-100 d-flex flex-column justify-content-center align-items-center py-4">
        <section class="card login-card border-0 shadow-lg w-100">
            <div class="card-body p-4 p-md-5">
                <div class="brand-mark mx-auto mb-3">CB</div>
                <h1 class="h3 text-center fw-bold mb-2">Consorcio de Bancas</h1>
                <p class="text-secondary text-center mb-4">Inicia sesión para continuar</p>
                <?php if ($mensaje !== ""): ?><div class="alert alert-danger" role="alert"><?= escapar($mensaje) ?></div><?php endif; ?>
                <form method="POST" action="<?= escapar($rutaBase) ?>/php/login.php">
                    <input type="hidden" name="token_csrf" value="<?= escapar($token) ?>">
                    <div class="mb-3"><label for="usuario" class="form-label fw-semibold">Usuario</label>
                        <input class="form-control form-control-lg" type="text" id="usuario" name="usuario" maxlength="50" autocomplete="username" required autofocus>
                    </div>
                    <div class="mb-4"><label for="clave" class="form-label fw-semibold">Contraseña</label>
                        <div class="input-group input-group-lg"><input class="form-control" type="password" id="clave" name="clave" autocomplete="current-password" required>
                            <button class="btn btn-outline-secondary" type="button" id="mostrarClave">Ver</button>
                        </div>
                    </div>
                    <button class="btn btn-success btn-lg w-100" type="submit">Iniciar sesión</button>
                </form>
            </div>
        </section>
        <footer class="small text-secondary mt-4">Sistema de administración de bancas</footer>
    </main>
    <script src="<?= escapar($rutaBase) ?>/js/seguridad.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>