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
        "ruta" => "/ConsorcioDeBancas/Configuracion/Configuracion.html",
        "icon" => "bi-gear-fill"
    ],

    "Caja y Reportes" => [
        "nombre" => "Caja y Reportes",
        "descripcion" => "Acceder al módulo de Caja y Reportes.",
        "ruta" => "/ConsorcioDeBancas/Grupo3/public/index.php",
        "icon" => "bi-cash-stack"
    ],

    "Jugar" => [
        "nombre" => "Jugar",
        "descripcion" => "Acceder al módulo de juego.",
        "ruta" => "/ConsorcioDeBancas/Jugar/",
        "icon" => "bi-controller"
    ],

    "Numeros Ganadore" => [
        "nombre" => "Numeros Ganadores",
        "descripcion" => "Acceder al módulo de Numeros Ganadores.",
        "ruta" => "/ConsorcioDeBancas/Grupo5/Version _1/index.html",
        "icon" => "bi-trophy-fill"
    ],

    "Pagos" => [
        "nombre" => "Pagos",
        "descripcion" => "Acceder al módulo de pagos.",
        "ruta" => "/ConsorcioDeBancas/Pago/pagos.php",
        "icon" => "bi-wallet2"
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

$configuracion = $modulosPermitidos["Configuracion"] ?? null;
unset($modulosPermitidos["Configuracion"]);

$token = tokenCsrf();
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel principal | Consorcio de Bancas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="<?= escapar($baseSeguridad) ?>/css/estilos.css?v=4">
</head>

<body class="dashboard-body">
    <main class="container py-4 py-lg-5">
        <header class="d-flex justify-content-between align-items-center gap-3 mb-5">
            <div class="d-flex align-items-center gap-3">
                <div class="brand-mark m-0">CB</div>
                <div>
                    <div class="fw-bold text-dark">Consorcio de Bancas</div>
                    <div class="small text-secondary">Panel de gestión</div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <?php if ($configuracion): ?>
                    <a class="btn btn-light border btn-icon"
                        href="<?= escapar($configuracion["ruta"]) ?>"
                        title="Configuración"
                        aria-label="Configuración">
                        <i class="bi bi-gear-fill"></i>
                    </a>
                <?php endif; ?>
                <form action="<?= escapar($baseSeguridad) ?>/php/cerrar_sesion.php" method="post">
                    <input type="hidden" name="token_csrf" value="<?= escapar($token) ?>">
                    <button class="btn btn-outline-secondary" type="submit">
                        <i class="bi bi-box-arrow-right"></i>
                        <span class="d-none d-sm-inline">Cerrar sesión</span>
                    </button>
                </form>
            </div>
        </header>
        <section class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div><span class="small fw-bold text-success text-uppercase">Panel principal</span>
                <h1 class="display-6 fw-bold mt-2 mb-2">¡Hola, <?= escapar($nombre) ?>!</h1>
                <p class="text-secondary mb-0">Selecciona el módulo al que deseas acceder.</p>
            </div>
            <div class="user-chip d-flex align-items-center gap-3">
                <div class="user-avatar"><?= escapar(strtoupper(substr(trim($nombre), 0, 1))) ?></div>
                <div>
                    <div class="fw-semibold"><?= escapar($nombre) ?></div>
                    <div class="small text-secondary"><?= escapar(ucfirst($rol)) ?></div>
                </div>
            </div>
        </section>
        <?php if ($rol === "admin"): ?><section class="alert admin-callout d-flex flex-column flex-md-row align-items-md-center gap-3 mb-5">
                <div class="admin-symbol">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="fw-bold">Administración del sistema</div>
                    <div class="small">Gestiona los usuarios y controla sus permisos de acceso.</div>
                </div>
                <a class="btn btn-success" href="<?= escapar($baseSeguridad) ?>/php/usuarios.php">Administrar usuarios →</a>
            </section><?php endif; ?>
        <section>
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-end gap-2 mb-3">
                <div>
                    <h2 class="h4 fw-bold mb-1">Mis módulos</h2>
                    <p class="text-secondary small mb-0">Herramientas disponibles para tu cuenta.</p>
                </div>
                <span class="badge rounded-pill text-bg-light border text-secondary"><?= count($modulosPermitidos) ?> disponibles</span>
            </div>
            <?php if (!$modulosPermitidos): ?><div class="empty-state text-center p-5">
                    <div class="fs-1 mb-2"> 📂 </div>
                    <h3 class="h5 fw-bold">No tienes módulos asignados</h3>
                    <p class="text-secondary mb-0">Comunícate con el administrador para solicitar acceso.</p>
                </div>
            <?php else: ?><div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-3">
                    <?php foreach ($modulosPermitidos as $modulo): ?>

                        <div class="col">
                            <a class="card module-card h-100 text-decoration-none"
                                href="<?= escapar($modulo["ruta"]) ?>">

                                <div class="card-body">

                                    <div class="module-card-top">

                                        <div class="module-icon">
                                            <i class="bi <?= escapar($modulo["icon"]) ?>"></i>
                                        </div>

                                        <span class="module-arrow">
                                            <i class="bi bi-arrow-up-right"></i>
                                        </span>

                                    </div>

                                    <h3>
                                        <?= escapar($modulo["nombre"]) ?>
                                    </h3>

                                    <p>
                                        <?= escapar($modulo["descripcion"]) ?>
                                    </p>

                                    <div class="module-action">
                                        Ingresar al módulo
                                        <i class="bi bi-arrow-right"></i>
                                    </div>

                                </div>

                            </a>
                        </div>

                    <?php endforeach; ?>
                </div><?php endif; ?>
        </section>
        <footer class="d-flex justify-content-between gap-2 border-top mt-5 pt-3 small text-secondary"><span>Consorcio de Bancas</span><span>Sistema de gestión</span></footer>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>