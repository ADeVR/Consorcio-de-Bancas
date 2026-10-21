<?php
require_once __DIR__ . "/conexion.php";
require_once __DIR__ . "/funciones.php";
exigirAdmin();
$mensaje = "";
$tipo = "error";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!csrfValido()) {
        http_response_code(403);
        exit("Solicitud no válida. Recarga la página.");
    }
    $accion = $_POST["accion"] ?? "";
    if ($accion === "registrar") {
        $nombre = trim((string)($_POST["usuario"] ?? ""));
        $clave = (string)($_POST["clave"] ?? "");
        $rol = (string)($_POST["rol"] ?? "agente");
        if (!preg_match('/^[a-zA-Z0-9_.-]{3,50}$/', $nombre)) $mensaje = "El usuario debe tener de 3 a 50 caracteres: letras, números, punto, guion o guion bajo.";
        elseif (strlen($clave) < 8) $mensaje = "La contraseña debe tener al menos 8 caracteres.";
        elseif (!in_array($rol, ["admin", "agente"], true)) $mensaje = "Rol no válido.";
        else {
            $q = $conexion->prepare("SELECT id FROM usuarios WHERE usuario=?");
            $q->execute([$nombre]);
            if ($q->fetch()) $mensaje = "Ese usuario ya existe.";
            else {
                $q = $conexion->prepare("INSERT INTO usuarios(usuario,clave,rol,activo) VALUES(?,?,?,1)");
                $q->execute([$nombre, password_hash($clave, PASSWORD_DEFAULT), $rol]);
                $mensaje = "Usuario registrado correctamente.";
                $tipo = "exito";
            }
        }
    } elseif ($accion === "estado") {
        $id = filter_var($_POST["id"] ?? null, FILTER_VALIDATE_INT);
        $estado = filter_var($_POST["nuevo_estado"] ?? null, FILTER_VALIDATE_INT);
        if (!$id || !in_array($estado, [0, 1], true)) $mensaje = "Datos inválidos.";
        elseif ((int)$id === (int)$_SESSION["usuario_id"] && $estado === 0) $mensaje = "No puedes desactivar tu propia cuenta.";
        else {
            try {
                $conexion->beginTransaction();
                $q = $conexion->query("SELECT id,activo FROM usuarios WHERE rol='admin' FOR UPDATE");
                $admins = $q->fetchAll();
                $q = $conexion->prepare("SELECT id,rol,activo FROM usuarios WHERE id=? FOR UPDATE");
                $q->execute([$id]);
                $objetivo = $q->fetch();
                if (!$objetivo) $mensaje = "No se encontró el usuario.";
                elseif ($objetivo["rol"] === "admin" && $estado === 0 && (int)$objetivo["activo"] === 1) {
                    $activos = count(array_filter($admins, fn($a) => (int)$a["activo"] === 1));
                    if ($activos <= 1) $mensaje = "No puedes desactivar al último administrador activo.";
                }
                if ($mensaje === "") {
                    $q = $conexion->prepare("UPDATE usuarios SET activo=? WHERE id=?");
                    $q->execute([$estado, $id]);
                    $mensaje = $estado === 1 ? "Usuario activado correctamente." : "Usuario desactivado correctamente.";
                    $tipo = "exito";
                }
                $conexion->commit();
            } catch (Throwable $e) {
                if ($conexion->inTransaction()) $conexion->rollBack();
                $mensaje = "No se pudo actualizar la cuenta.";
            }
        }
    } else $mensaje = "Acción desconocida.";
    $_SESSION["token_csrf"] = bin2hex(random_bytes(32));
}
$t = tokenCsrf();
$q = $conexion->query("SELECT id,usuario,rol,activo,fecha_creacion FROM usuarios ORDER BY id DESC");
$usuarios = $q->fetchAll();
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Administración de usuarios | Consorcio de Bancas</title>
    <link rel="stylesheet" href="/Seguridad/css/seguridad.css">
</head>

<body>
    <main class="admin-contenedor">
        <header class="barra-superior">
            <div>
                <p class="sobrelinea">CONSORCIO DE BANCAS</p>
                <h1>Administración de usuarios</h1>
                <p class="texto-secundario">Bienvenido, <?= escapar($_SESSION["usuario"]) ?>.</p>
            </div>
            <form action="/Seguridad/php/cerrar_sesion.php" method="post"><input type="hidden" name="token_csrf" value="<?= escapar($t) ?>"><button class="boton boton-secundario">Cerrar sesión</button></form>
        </header>
        <?php if ($mensaje !== ""): ?><p class="mensaje <?= $tipo ?>" role="status"><?= escapar($mensaje) ?></p><?php endif; ?>
        <section class="tarjeta">
            <h2>Registrar usuario</h2>
            <form method="post" action="/Seguridad/php/usuarios.php" class="form-grid">
                <input type="hidden" name="token_csrf" value="<?= escapar($t) ?>"><input type="hidden" name="accion" value="registrar">
                <div class="campo"><label for="usuario">Nombre de usuario</label><input id="usuario" name="usuario" minlength="3" maxlength="50" pattern="[a-zA-Z0-9_.-]+" required></div>
                <div class="campo"><label for="clave">Contraseña (mínimo 8 caracteres)</label><input type="password" id="clave" name="clave" minlength="8" required></div>
                <div class="campo"><label for="rol">Rol</label><select id="rol" name="rol">
                        <option value="agente">Agente</option>
                        <option value="admin">Administrador</option>
                    </select></div>
                <div class="campo"><button class="boton boton-principal">Registrar usuario</button></div>
            </form>
        </section>
        <section class="tarjeta">
            <h2>Usuarios registrados</h2>
            <div class="tabla-contenedor">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $u): ?><tr>
                                <td><?= (int)$u["id"] ?></td>
                                <td><?= escapar($u["usuario"]) ?></td>
                                <td><?= escapar(ucfirst($u["rol"])) ?></td>
                                <td><span class="estado <?= ((int)$u["activo"] === 1) ? "activo" : "inactivo" ?>"><?= ((int)$u["activo"] === 1) ? "Activo" : "Inactivo" ?></span></td>
                                <td><?= escapar($u["fecha_creacion"] ?? "") ?></td>
                                <td>
                                    <?php if ((int)$u["id"] === (int)$_SESSION["usuario_id"]): ?>Tu cuenta<?php else: ?><form method="post" action="/Seguridad/php/usuarios.php">
                                        <input type="hidden" name="token_csrf" value="<?= escapar($t) ?>"><input type="hidden" name="accion" value="estado"><input type="hidden" name="id" value="<?= (int)$u["id"] ?>"><input type="hidden" name="nuevo_estado" value="<?= ((int)$u["activo"] === 1) ? 0 : 1 ?>">
                                        <button class="boton <?= ((int)$u["activo"] === 1) ? "boton-peligro" : "boton-principal" ?>"><?= ((int)$u["activo"] === 1) ? "Desactivar" : "Activar" ?></button>
                                    </form><?php endif; ?></td>
                            </tr><?php endforeach; ?>
                        <?php if (!$usuarios): ?><tr>
                                <td colspan="6">No hay usuarios registrados.</td>
                            </tr><?php endif; ?></tbody>
                </table>
            </div>
        </section>
    </main>
</body>

</html>