
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
    <link rel="stylesheet" href="<?= escapar($base) ?>/css/seguridad.css">
</head>

<body>
<main class="admin-contenedor">
    <header class="barra-superior">
        <div>
            <p class="sobrelinea">CONSORCIO DE BANCAS</p>
            <h1>Permisos de acceso</h1>
            <p class="texto-secundario">
                Usuario: <?= escapar($usuario["usuario"]) ?>
            </p>
        </div>

        <a class="boton boton-secundario"
           href="<?= escapar($base) ?>/php/usuarios.php">
            Volver a usuarios
        </a>
    </header>

    <?php if ($mensaje !== ""): ?>
        <p class="mensaje <?= escapar($tipo) ?>" role="status">
            <?= escapar($mensaje) ?>
        </p>
    <?php endif; ?>

    <section class="tarjeta">
        <h2>Módulos autorizados</h2>

        <?php if ($usuario["rol"] === "admin"): ?>
            <p>Este usuario tiene el rol de administrador y dispone de acceso a todos los módulos.</p>
        <?php else: ?>
            <form method="post"
                  action="<?= escapar($base) ?>/php/permisos.php?id=<?= (int)$id ?>">

                <input type="hidden" name="token_csrf"
                       value="<?= escapar($t) ?>">

                <?php foreach ($modulos as $codigo => $nombre): ?>
                    <div class="campo">
                        <label>
                            <input type="checkbox"
                                   name="modulos[]"
                                   value="<?= escapar($codigo) ?>"
                                   <?= in_array($codigo, $asignados, true) ? "checked" : "" ?>>
                            <?= escapar($nombre) ?>
                        </label>
                    </div>
                <?php endforeach; ?>

                <button class="boton boton-principal" type="submit">
                    Guardar permisos
                </button>
            </form>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
