<?php
require_once __DIR__ . "/conexion.php";
require_once __DIR__ . "/funciones.php";
if (!empty($_SESSION["usuario_id"])) {
    header("Location: " . (($_SESSION["rol"] ?? "") === "admin" ? "/Seguridad/php/usuarios.php" : "/Seguridad/php/inicio.php"));
    exit;
}
$mensaje = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!csrfValido()) {
        $mensaje = "El formulario venció. Recarga la página e inténtalo otra vez.";
    } else {
        $nombre = trim((string)($_POST["usuario"] ?? ""));
        $clave = (string)($_POST["clave"] ?? "");
        $q = $conexion->prepare("SELECT id,usuario,clave,rol,activo FROM usuarios WHERE usuario=? LIMIT 1");
        $q->execute([$nombre]);
        $u = $q->fetch();
        if ($u && password_verify($clave, (string)$u["clave"]) && (int)$u["activo"] === 1 && in_array($u["rol"], ["admin", "agente"], true)) {
            session_regenerate_id(true);
            $_SESSION["usuario_id"] = (int)$u["id"];
            $_SESSION["usuario"] = $u["usuario"];
            $_SESSION["rol"] = $u["rol"];
            $_SESSION["token_csrf"] = bin2hex(random_bytes(32));
            header("Location: " . ($u["rol"] === "admin" ? "/Seguridad/php/usuarios.php" : "/Seguridad/php/inicio.php"));
            exit;
        }
        $mensaje = "Usuario o contraseña incorrectos, o cuenta inactiva.";
    }
}
$t = tokenCsrf();
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Iniciar sesión | Consorcio de Bancas</title>
    <link rel="stylesheet" href="/Seguridad/css/seguridad.css">
</head>

<body class="pagina-login">
    <main class="login-contenedor">
        <section class="login-tarjeta">
            <div class="marca">CB</div>
            <h1>Consorcio de Bancas</h1>
            <p class="subtitulo">Inicia sesión para continuar</p>
            <?php if ($mensaje !== ""): ?><p class="mensaje error" role="alert"><?= escapar($mensaje) ?></p><?php endif; ?>
            <form method="post" action="/Seguridad/php/login.php"><input type="hidden" name="token_csrf" value="<?= escapar($t) ?>">
                <div class="campo"><label for="usuario">Usuario</label><input id="usuario" name="usuario" maxlength="50" autocomplete="username" required autofocus></div>
                <div class="campo"><label for="clave">Contraseña</label>
                    <div class="campo-clave"><input type="password" id="clave" name="clave" autocomplete="current-password" required><button type="button" id="mostrarClave" class="boton-mostrar">Ver</button></div>
                </div>
                <button class="boton boton-principal boton-ancho" type="submit">Iniciar sesión</button>
            </form>
        </section>
        <footer>Sistema de administración de bancas</footer>
    </main>
    <script src="/Seguridad/js/seguridad.js" defer></script>
</body>

</html>