<?php
require_once __DIR__ . "/conexion.php";
require_once __DIR__ . "/funciones.php";

exigirAdmin();

$base = "/ConsorcioDeBancas/Seguridad";
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

        if (!preg_match('/^[a-zA-Z0-9_.-]{3,50}$/', $nombre)) {
            $mensaje = "El usuario debe tener entre 3 y 50 caracteres válidos.";
        } elseif (strlen($clave) < 8) {
            $mensaje = "La contraseña debe tener al menos 8 caracteres.";
        } elseif (!in_array($rol, ["admin", "agente"], true)) {
            $mensaje = "Rol no válido.";
        } else {
            $q = $conexion->prepare("SELECT id FROM usuarios WHERE usuario = ?");
            $q->execute([$nombre]);

            if ($q->fetch()) {
                $mensaje = "Ese usuario ya existe.";
            } else {
                $q = $conexion->prepare(
                    "INSERT INTO usuarios (usuario, clave, rol, activo)
                     VALUES (?, ?, ?, 1)"
                );

                $q->execute([
                    $nombre,
                    password_hash($clave, PASSWORD_DEFAULT),
                    $rol
                ]);

                $mensaje = "Usuario registrado correctamente.";
                $tipo = "exito";
            }
        }
    } elseif ($accion === "estado") {
        $id = filter_var($_POST["id"] ?? null, FILTER_VALIDATE_INT);
        $estado = filter_var($_POST["nuevo_estado"] ?? null, FILTER_VALIDATE_INT);

        if (!$id || !in_array($estado, [0, 1], true)) {
            $mensaje = "Datos inválidos.";
        } elseif ((int)$id === (int)$_SESSION["usuario_id"] && $estado === 0) {
            $mensaje = "No puedes desactivar tu propia cuenta.";
        } else {
            try {
                $conexion->beginTransaction();

                $q = $conexion->query(
                    "SELECT id, activo FROM usuarios WHERE rol = 'admin' FOR UPDATE"
                );
                $admins = $q->fetchAll();

                $q = $conexion->prepare(
                    "SELECT id, rol, activo FROM usuarios WHERE id = ? FOR UPDATE"
                );
                $q->execute([$id]);
                $objetivo = $q->fetch();

                if (!$objetivo) {
                    $mensaje = "No se encontró el usuario.";
                } elseif (
                    $objetivo["rol"] === "admin" &&
                    $estado === 0 &&
                    (int)$objetivo["activo"] === 1
                ) {
                    $activos = count(array_filter(
                        $admins,
                        fn($a) => (int)$a["activo"] === 1
                    ));

                    if ($activos <= 1) {
                        $mensaje = "No puedes desactivar al último administrador activo.";
                    }
                }

                if ($mensaje === "") {
                    $q = $conexion->prepare(
                        "UPDATE usuarios SET activo = ? WHERE id = ?"
                    );
                    $q->execute([$estado, $id]);

                    $mensaje = $estado
                        ? "Usuario activado correctamente."
                        : "Usuario desactivado correctamente.";
                    $tipo = "exito";
                }

                $conexion->commit();
            } catch (Throwable $e) {
                if ($conexion->inTransaction()) {
                    $conexion->rollBack();
                }

                error_log("Error al cambiar el estado de un usuario.");
                $mensaje = "No se pudo actualizar la cuenta.";
            }
        }
    } else {
        $mensaje = "Acción desconocida.";
    }

    $_SESSION["token_csrf"] = bin2hex(random_bytes(32));
}

$t = tokenCsrf();

$q = $conexion->query(
    "SELECT id, usuario, rol, activo, fecha_creacion
     FROM usuarios
     ORDER BY id DESC"
);
$usuarios = $q->fetchAll();
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Administración de usuarios | Consorcio de Bancas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= escapar($base) ?>/css/estilos.css?v=3">
</head>

<body class="dashboard-body">
    <main class="container py-4 py-lg-5">
        <header class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <div class="small fw-bold text-success text-uppercase">Consorcio de Bancas</div>
                <h1 class="h2 fw-bold mt-2 mb-1">Administración de usuarios</h1>
                <p class="text-secondary mb-0">Bienvenido, <?= escapar($_SESSION["usuario"]) ?>.</p>
            </div>
            <div class="d-flex flex-wrap gap-2"><a class="btn btn-outline-success" href="/ConsorcioDeBancas/index.php">← Volver al panel</a>
                <form action="<?= escapar($base) ?>/php/cerrar_sesion.php" method="post"><input type="hidden" name="token_csrf" value="<?= escapar($t) ?>">
                    <button class="btn btn-outline-secondary" type="submit">Cerrar sesión</button>
                </form>
            </div>
        </header>
        <?php if ($mensaje !== ""): ?><div class="alert alert-<?= $tipo === "exito" ? "success" : "danger" ?>" role="status"><?= escapar($mensaje) ?></div><?php endif; ?>
        <section class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <h2 class="h5 fw-bold mb-3">Registrar usuario</h2>
                <form method="post" action="<?= escapar($base) ?>/php/usuarios.php" class="row g-3 align-items-end">
                    <input type="hidden" name="token_csrf" value="<?= escapar($t) ?>"><input type="hidden" name="accion" value="registrar">
                    <div class="col-12 col-md-4"><label for="usuario" class="form-label">Nombre de usuario</label><input class="form-control" id="usuario" name="usuario" minlength="3" maxlength="50" pattern="[a-zA-Z0-9_.-]+" required></div>
                    <div class="col-12 col-md-4"><label for="clave" class="form-label">Contraseña (mínimo 8 caracteres)</label><input class="form-control" type="password" id="clave" name="clave" minlength="8" required></div>
                    <div class="col-12 col-md-2"><label for="rol" class="form-label">Rol</label><select class="form-select" id="rol" name="rol">
                            <option value="agente">Agente</option>
                            <option value="admin">Administrador</option>
                        </select></div>
                    <div class="col-12 col-md-2"><button class="btn btn-success w-100" type="submit">Registrar</button></div>
                </form>
            </div>
        </section>
        <section class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h5 fw-bold mb-0">Usuarios registrados</h2><span class="badge rounded-pill text-bg-light border"><?= count($usuarios) ?> usuarios</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Usuario</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $u): ?><tr>
                                    <td><?= (int)$u["id"] ?></td>
                                    <td class="fw-semibold"><?= escapar($u["usuario"]) ?></td>
                                    <td><?= escapar(ucfirst($u["rol"])) ?></td>
                                    <td><span class="badge rounded-pill <?= (int)$u["activo"] === 1 ? "text-bg-success" : "text-bg-secondary" ?>"><?= (int)$u["activo"] === 1 ? "Activo" : "Inactivo" ?></span></td>
                                    <td><?= escapar($u["fecha_creacion"] ?? "") ?></td>
                                    <td><?php if ((int)$u["id"] === (int)$_SESSION["usuario_id"]): ?><span class="text-secondary small">Tu cuenta</span><?php else: ?>
                                            <div class="d-flex flex-wrap gap-2"><a class="btn btn-sm btn-outline-success" href="<?= escapar($base) ?>/php/permisos.php?id=<?= (int)$u["id"] ?>">Permisos</a>
                                                <form method="post" action="<?= escapar($base) ?>/php/usuarios.php"><input type="hidden" name="token_csrf" value="<?= escapar($t) ?>">
                                                    <input type="hidden" name="accion" value="estado"><input type="hidden" name="id" value="<?= (int)$u["id"] ?>">
                                                    <input type="hidden" name="nuevo_estado" value="<?= (int)$u["activo"] === 1 ? 0 : 1 ?>">
                                                    <button class="btn btn-sm <?= (int)$u["activo"] === 1 ? "btn-outline-danger" : "btn-success" ?>" type="submit"><?= (int)$u["activo"] === 1 ? "Desactivar" : "Activar" ?></button>
                                                </form>
                                            </div><?php endif; ?>
                                    </td>
                                </tr><?php endforeach; ?>
                            <?php if (!$usuarios): ?><tr>
                                    <td colspan="6" class="text-center text-secondary py-4">No hay usuarios registrados.</td>
                                </tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>