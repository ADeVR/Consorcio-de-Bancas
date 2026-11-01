<?php
require_once __DIR__ . "/conexion.php";
require_once __DIR__ . "/funciones.php";

exigirAdmin();

$base = "/ConsorcioDeBancas/Seguridad";
$modulos = modulosSistema();
$mensaje = "";
$tipo = "error";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id) {
    header("Location: " . $base . "/php/usuarios.php");
    exit;
}

$q = $conexion->prepare(
    "SELECT id, usuario, rol, activo FROM usuarios WHERE id = ? LIMIT 1"
);
$q->execute([$id]);
$usuario = $q->fetch();

if (!$usuario) {
    http_response_code(404);
    exit("El usuario solicitado no existe.");
}

// Guardar permisos seleccionados.
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!csrfValido()) {
        http_response_code(403);
        exit("Solicitud no válida. Recarga la página.");
    }

    if ($usuario["rol"] === "admin") {
        $mensaje = "Los administradores tienen acceso a todos los módulos.";
        $tipo = "exito";
    } else {
        $seleccionados = $_POST["modulos"] ?? [];

        if (!is_array($seleccionados)) {
            $seleccionados = [];
        }

        // Aceptar solamente nombres del catálogo definido.
        $seleccionados = array_values(array_unique(array_filter(
            $seleccionados,
            fn($m) => is_string($m) && array_key_exists($m, $modulos)
        )));

        try {
            $conexion->beginTransaction();

            $q = $conexion->prepare(
                "DELETE FROM usuario_permisos WHERE usuario_id = ?"
            );
            $q->execute([$id]);

            $q = $conexion->prepare(
                "INSERT INTO usuario_permisos (usuario_id, modulo)
                 VALUES (?, ?)"
            );

            foreach ($seleccionados as $modulo) {
                $q->execute([$id, $modulo]);
            }

            $conexion->commit();

            $mensaje = "Permisos guardados correctamente.";
            $tipo = "exito";
        } catch (Throwable $e) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }

            error_log("Error al guardar permisos de usuario.");
            $mensaje = "No se pudieron guardar los permisos.";
        }
    }
}

// Consultar permisos guardados para el usuario seleccionado.
// Los administradores siempre tienen acceso completo.
if ($usuario["rol"] === "admin") {
    $asignados = array_keys($modulos);
} else {
    $q = $conexion->prepare(
        "SELECT modulo FROM usuario_permisos WHERE usuario_id = ?"
    );
    $q->execute([$id]);
    $asignados = $q->fetchAll(PDO::FETCH_COLUMN);
}

$t = tokenCsrf();
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Permisos de usuario | Consorcio de Bancas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= escapar($base) ?>/css/estilos.css?v=3">
</head>

<body class="dashboard-body">
    <main class="container py-4 py-lg-5">
        <header class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <div class="small fw-bold text-success text-uppercase">Consorcio de Bancas</div>
                <h1 class="h2 fw-bold mt-2 mb-1">Permisos de acceso</h1>
                <p class="text-secondary mb-0">Usuario: <?= escapar($usuario["usuario"]) ?></p>
            </div>
            <a class="btn btn-outline-success" href="<?= escapar($base) ?>/php/usuarios.php">← Volver a usuarios</a>
        </header>
        <?php if ($mensaje !== ""): ?><div class="alert alert-<?= $tipo === "exito" ? "success" : "danger" ?>" role="status"><?= escapar($mensaje) ?></div><?php endif; ?>
        <section class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h2 class="h5 fw-bold mb-3">Módulos autorizados</h2>
                <?php if ($usuario["rol"] === "admin"): ?><div class="alert alert-success mb-0">Este usuario tiene el rol de administrador y dispone de acceso a todos los módulos.</div>
                <?php else: ?><p class="text-secondary">Selecciona los módulos a los que podrá acceder este usuario.</p>
                    <form method="post" action="<?= escapar($base) ?>/php/permisos.php?id=<?= (int)$id ?>"><input type="hidden" name="token_csrf" value="<?= escapar($t) ?>">
                        <div class="row g-3 mb-4"><?php foreach ($modulos as $codigo => $nombre): ?><div class="col-12 col-md-6">
                                    <label class="permission-option d-flex align-items-center gap-3 p-3 border rounded-3 h-100">
                                        <input class="form-check-input mt-0" type="checkbox" name="modulos[]" value="<?= escapar($codigo) ?>" <?= in_array($codigo, $asignados, true) ? "checked" : "" ?>>
                                        <span class="fw-medium"><?= escapar($nombre) ?></span></label>
                                </div><?php endforeach; ?></div>
                        <button class="btn btn-success" type="submit">Guardar permisos</button>
                    </form><?php endif; ?>
            </div>
        </section>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>