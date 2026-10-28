<?php
declare(strict_types=1);
require __DIR__ . '/../../app/bootstrap.php';

$titulo   = 'Estado de caja';
$permiso  = 'caja.ver_saldo';
$scripts  = ['caja/estado.js'];
require __DIR__ . '/../includes/header.php';

$urlRelevo = in_array('caja.relevo', $permisosUsuario, true) && is_file(__DIR__ . '/relevo.php') ? $base . '/caja/relevo.php' : '';
?>
<div class="context-banner"><span class="context-banner-icon">↗</span><div><strong>Panorama de caja</strong><span>Saldo y movimientos vinculados al turno. La validación financiera siempre corresponde al servidor.</span></div><a class="context-banner-link" href="<?= $h($base) ?>/reportes/movimientos.php">Abrir reportes →</a></div><div id="estado-carga"></div>

<div id="estado-sin-turno" class="d-none">
  <div class="alert alert-warning mb-0" role="status">
    <span id="estado-sin-turno-texto"></span>
    <?php if ($urlRelevo !== ''): ?><a class="btn btn-primary btn-sm ms-2" href="<?= $h($urlRelevo) ?>">Registrar relevo</a><?php endif; ?>
  </div>
</div>

<div id="estado-error" class="d-none">
  <div class="alert alert-danger d-flex align-items-center justify-content-between mb-0" role="alert">
    <span id="estado-error-texto"></span>
    <button type="button" class="btn btn-outline-danger btn-sm" id="estado-reintentar">Reintentar</button>
  </div>
</div>

<div id="estado-vista" class="d-none">
  <p class="text-secondary small mb-3" id="est-banca"></p>
  <div class="row g-3 mb-4">
    <div class="col-lg-5"><div class="card tarjeta tarjeta-saldo h-100"><div class="card-body">
      <div class="text-secondary small kpi-label">SALDO DISPONIBLE</div>
      <div class="saldo-grande" id="est-saldo"></div>
      <div class="text-secondary small" id="est-actualizado"></div>
    </div></div></div>
    <div class="col-md-6 col-lg-4"><div class="card tarjeta tarjeta-dato h-100"><div class="card-body">
      <div class="text-secondary small kpi-label">TURNO EN CURSO</div>
      <div class="fs-5" id="est-turno-usuario"></div>
      <div class="text-secondary small" id="est-turno-desde"></div>
      <div class="text-secondary small" id="est-turno-fondo"></div>
    </div></div></div>
    <div class="col-md-6 col-lg-3"><div class="card tarjeta tarjeta-dato h-100"><div class="card-body">
      <div class="text-secondary small kpi-label">ACCIONES PENDIENTES</div>
      <div id="est-pend-efectivo"></div>
      <div id="est-pend-cierres"></div>
    </div></div></div>
  </div>

  <div id="est-aviso-agente" class="alert alert-info d-none" role="status">
    Solo el agente del turno activo ve el saldo y los movimientos de la caja.
  </div>

  <div id="est-movimientos">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <div><span class="eyebrow">TRAZABILIDAD</span><h2 class="h5 mb-0">Últimos movimientos</h2></div>
      <a class="btn btn-outline-primary btn-sm" data-requiere-permiso="reporte.consultar" href="<?= $h($base) ?>/reportes/movimientos.php">Ver reporte</a>
    </div>
    <div class="tabla-scroll">
      <table class="table table-sm align-middle caja-tabla">
        <caption class="visually-hidden">Últimos movimientos de la caja</caption>
        <thead><tr>
          <th scope="col">Hora</th><th scope="col">Ticket</th><th scope="col">Operación</th>
          <th scope="col" class="text-end">Importe</th><th scope="col">Usuario</th><th scope="col" class="text-end">Saldo</th>
        </tr></thead>
        <tbody id="est-ultimos"></tbody>
      </table>
    </div>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php';
