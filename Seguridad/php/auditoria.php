<?php
require_once __DIR__ . "/conexion.php";
require_once __DIR__ . "/funciones.php";

$baseSeguridad = "/ConsorcioDeBancas/Seguridad";

if (empty($_SESSION["usuario_id"])) {
  header("Location: " . $baseSeguridad . "/php/login.php");
  exit;
}
if (($_SESSION["rol"] ?? "") !== "admin") {
  http_response_code(403);
  exit("No tienes permiso para consultar la auditoría.");
}

$archivo = dirname(__DIR__) . DIRECTORY_SEPARATOR . "datos"
  . DIRECTORY_SEPARATOR . "auditoria.jsonl";
$registros = [];

if (is_file($archivo) && is_readable($archivo)) {
  $lineas = file($archivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  if ($lineas !== false) {
    foreach (array_slice(array_reverse($lineas), 0, 300) as $linea) {
      $dato = json_decode($linea, true);
      if (is_array($dato)) $registros[] = $dato;
    }
  }
}

$filtroUsuario = trim((string)($_GET["usuario"] ?? ""));
$filtroEvento = trim((string)($_GET["evento"] ?? ""));
if ($filtroUsuario !== "" || $filtroEvento !== "") {
  $registros = array_values(array_filter($registros, static function ($r) use ($filtroUsuario, $filtroEvento) {
    $okUsuario = $filtroUsuario === "" || stripos((string)($r["usuario"] ?? ""), $filtroUsuario) !== false;
    $okEvento = $filtroEvento === "" || ($r["evento"] ?? "") === $filtroEvento;
    return $okUsuario && $okEvento;
  }));
}
function h($v): string
{
  return htmlspecialchars((string)$v, ENT_QUOTES, "UTF-8");
}
?>
<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Auditoría | Consorcio de Bancas</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= h($baseSeguridad) ?>/css/estilos.css?v=4">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <style>
    body {
      background: #f1f5f9
    }

    .audit-card {
      border: 1px solid #e1e8ef;
      border-radius: 18px
    }

    .audit-heading {
      color: #087443;
      letter-spacing: .08em;
      font-size: .75rem;
      font-weight: 800
    }

    .table thead th {
      color: #64748b;
      font-size: .75rem;
      text-transform: uppercase;
      white-space: nowrap
    }

    .table td {
      vertical-align: middle
    }

    .event-badge {
      background: #e8f5ed;
      color: #087443
    }
  </style>
</head>

<body>
  <main class="container-fluid px-3 px-lg-4 py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
      <div>
        <div class="audit-heading">SEGURIDAD DEL SISTEMA</div>
        <h1 class="h3 fw-bold mb-1">Auditoría de actividades</h1>
        <p class="text-secondary mb-0">Últimos 300 eventos registrados en el módulo de Seguridad.</p>
      </div>
      <a class="btn btn-outline-success" href="<?= h($baseSeguridad) ?>/php/inicio.php"><i class="bi bi-arrow-left me-1"></i>Volver al panel</a>
    </div>
    <section class="card audit-card shadow-sm mb-4">
      <div class="card-body p-3 p-lg-4">
        <form method="get" class="row g-3 align-items-end">
          <div class="col-12 col-md-5"><label for="usuario" class="form-label">Usuario</label>
            <input class="form-control" id="usuario" name="usuario" value="<?= h($filtroUsuario) ?>" placeholder="Buscar usuario">
          </div>
          <div class="col-12 col-md-5"><label for="evento" class="form-label">Tipo de evento</label>
            <select class="form-select" id="evento" name="evento">
              <option value="">Todos los eventos</option>
              <?php foreach (["ACCESO_PAGINA", "INICIO_SESION", "INTENTO_INICIO_SESION", "CIERRE_SESION", "GESTION_USUARIOS", "CAMBIO_PERMISOS"] as $op): ?>
                <option value="<?= h($op) ?>" <?= $filtroEvento === $op ? "selected" : "" ?>><?= h(str_replace("_", " ", $op)) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-12 col-md-2 d-flex gap-2"><button class="btn btn-success flex-grow-1">Filtrar</button><a class="btn btn-outline-secondary" href="auditoria.php">Limpiar</a></div>
        </form>
      </div>
    </section>
    <section class="card audit-card shadow-sm">
      <div class="card-header bg-white border-0 px-3 px-lg-4 pt-4">
        <div class="d-flex justify-content-between align-items-center">
          <h2 class="h5 fw-bold mb-0">Registro de eventos</h2><span class="badge event-badge rounded-pill"><?= count($registros) ?> registros</span>
        </div>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th class="px-3 px-lg-4">Fecha</th>
                <th>Evento</th>
                <th>Usuario</th>
                <th>Rol</th>
                <th>Página</th>
                <th>Método</th>
                <th>Estado</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!$registros): ?><tr>
                  <td colspan="7" class="text-center text-secondary py-5">Todavía no hay eventos o no existen resultados para esos filtros.</td>
                </tr>
                <?php else: foreach ($registros as $r): ?><tr>
                    <td class="px-3 px-lg-4 text-nowrap"><?= h($r["fecha"] ?? "") ?></td>
                    <td><span class="badge event-badge"><?= h(str_replace("_", " ", $r["evento"] ?? "")) ?></span></td>
                    <td><?= h($r["usuario"] ?? "No autenticado") ?></td>
                    <td><?= h($r["rol"] ?? "—") ?></td>
                    <td><?= h($r["pagina"] ?? "") ?></td>
                    <td><?= h($r["metodo"] ?? "") ?></td>
                    <td><?= h($r["estado"] ?? "") ?></td>
                  </tr><?php endforeach;
                    endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>
    <p class="small text-secondary mt-3">El historial se guarda en formato JSONL en el servidor y no se expone como archivo descargable.</p>
  </main>
</body>

</html>