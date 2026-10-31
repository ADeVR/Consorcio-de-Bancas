<?php
require_once __DIR__ . "/conexion.php";
require_once __DIR__ . "/funciones.php";

$baseSeguridad = "/ConsorcioDeBancas/Seguridad";

// Verificar que el usuario haya iniciado sesión.
if (empty($_SESSION["usuario_id"])) {
    header("Location: " . $baseSeguridad . "/php/login.php");
    exit;
}

$rol = $_SESSION["rol"] ?? "";
$nombre = $_SESSION["usuario"] ?? "Usuario";

if (!in_array($rol, ["admin", "agente"], true)) {
    $_SESSION = [];
    session_destroy();

    header("Location: " . $baseSeguridad . "/php/login.php");
    exit;
}

// Módulos disponibles y sus direcciones.
// Ajustaremos estas rutas a los archivos reales de cada módulo.
$modulos = [
    "Configuracion" => [
        "nombre" => "Configuración",
        "descripcion" => "Administrar la configuración de las loterías.",
        "ruta" => "/ConsorcioDeBancas/Configuracion/"
    ],
    "Apertura" => [
        "nombre" => "Apertura",
        "descripcion" => "Acceder al módulo de apertura.",
        "ruta" => "/ConsorcioDeBancas/Apertura/"
    ],
    "Jugar" => [
        "nombre" => "Jugar",
        "descripcion" => "Acceder al módulo de juego.",
        "ruta" => "/ConsorcioDeBancas/Jugar/"
    ],
    "Cierre" => [
        "nombre" => "Cierre",
        "descripcion" => "Acceder al módulo de cierre.",
        "ruta" => "/ConsorcioDeBancas/Cierre/"
    ],
    "Pagos" => [
        "nombre" => "Pagos",
        "descripcion" => "Acceder al módulo de pagos.",
        "ruta" => "/ConsorcioDeBancas/Pago/"
    ]
];

// Mostrar solamente los módulos autorizados.
// Los administradores tienen acceso completo según las funciones actuales.
$modulosPermitidos = [];

foreach ($modulos as $codigo => $modulo) {
    if (tienePermisoModulo($conexion, $codigo)) {
        $modulosPermitidos[$codigo] = $modulo;
    }
}

$token = tokenCsrf();
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Panel principal | Consorcio de Bancas</title>

    <link
        rel="stylesheet"
        href="<?= escapar($baseSeguridad) ?>/css/seguridad.css">
</head>

<body>
    <main class="admin-contenedor">

        <header class="barra-superior">
            <div>
                <p class="sobrelinea">CONSORCIO DE BANCAS</p>
                <h1>Panel principal</h1>
                <p class="texto-secundario">
                    Bienvenido, <?= escapar($nombre) ?>.
                </p>
                <p class="texto-secundario">
                    Rol: <?= escapar(ucfirst($rol)) ?>
                </p>
            </div>

            <form
                action="<?= escapar($baseSeguridad) ?>/php/cerrar_sesion.php"
                method="post">
                <input
                    type="hidden"
                    name="token_csrf"
                    value="<?= escapar($token) ?>">

                <button class="boton boton-secundario" type="submit">
                    Cerrar sesión
                </button>
            </form>
        </header>

        <?php if ($rol === "admin"): ?>
            <section class="tarjeta">
                <h2>Administración</h2>
                <p>Gestiona los usuarios y sus permisos de acceso.</p>

                <a
                    class="boton boton-principal"
                    href="<?= escapar($baseSeguridad) ?>/php/usuarios.php">
                    Administración de usuarios
                </a>
            </section>
        <?php endif; ?>

        <section class="tarjeta">
            <h2>Mis módulos</h2>

            <?php if (!$modulosPermitidos): ?>
                <p>
                    No tienes módulos asignados. Solicita al administrador
                    que configure tus permisos.
                </p>
            <?php else: ?>
                <div class="modulos-grid">
                    <?php foreach ($modulosPermitidos as $modulo): ?>
                        <article class="tarjeta">
                            <h3><?= escapar($modulo["nombre"]) ?></h3>

                            <p><?= escapar($modulo["descripcion"]) ?></p>

                            <a
                                class="boton boton-principal"
                                href="<?= escapar($modulo["ruta"]) ?>">
                                Abrir módulo
                            </a>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

    </main>
</body>

</html>