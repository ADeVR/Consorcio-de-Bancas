<?php
declare(strict_types=1);
require __DIR__ . '/../../app/bootstrap.php';

use Caja\Core\Contenedor;


$titulo  = 'Reporte de caja';
$permiso = 'reporte.consultar';
$scripts = ['reportes/movimientos.js'];
require __DIR__ . '/../includes/header.php';

$hoy = Contenedor::puerto('hora')->ahora()->format('Y-m-d');
$tipos = ['venta' => 'Venta', 'pago' => 'Pago', 'anulacion' => 'Anulación', 'entrega' => 'Entrega', 'retiro' => 'Retiro',
          'intento_pago_rechazado' => 'Pago sin fondos', 'relevo' => 'Relevo', 'cierre' => 'Cierre'];
?>
<div class="context-banner"><span class="context-banner-icon">≡</span><div><strong>Libro cronológico de caja</strong><span>Consulta el historial de operaciones autorizadas. Los eventos informativos y relevos no modifican el saldo.</span></div><span class="context-banner-tag">6 columnas</span></div>
<div class="report-overview" id="rep-resumen" aria-live="polite">
  <div class="report-metric"><span>ENTRADAS CONFIRMADAS</span><strong id="rep-entradas">—</strong><small>Aumentan el efectivo</small></div>
  <div class="report-metric"><span>SALIDAS CONFIRMADAS</span><strong id="rep-salidas">—</strong><small>Reducen el efectivo</small></div>
  <div class="report-metric"><span>VARIACIÓN NETA</span><strong id="rep-neto">—</strong><small>Entradas menos salidas</small></div>
  <div class="report-metric"><span>REGISTROS FILTRADOS</span><strong id="rep-cantidad">—</strong><small>Movimientos y eventos</small></div>
</div>
<form id="form-filtros" class="card tarjeta mb-3 no-imprimir" novalidate data-hoy="<?= $h($hoy) ?>">
  <div class="card-body"><div class="section-head"><div><span class="eyebrow">BUSCADOR</span><h2 class="h6 mb-0">Filtrar movimientos</h2></div><span class="small text-secondary">Puedes combinar varios filtros</span></div>
    <div class="row g-2 align-items-end">
      <div class="col-6 col-lg-2"><label class="form-label small mb-1" for="f-desde">Desde</label>
        <input type="date" class="form-control form-control-sm" id="f-desde" name="desde" value="<?= $h($hoy) ?>"></div>
      <div class="col-6 col-lg-2"><label class="form-label small mb-1" for="f-hasta">Hasta</label>
        <input type="date" class="form-control form-control-sm" id="f-hasta" name="hasta" value="<?= $h($hoy) ?>"></div>
      <div class="col-6 col-lg-2"><label class="form-label small mb-1" for="f-loteria">Lotería</label>
        <select class="form-select form-select-sm" id="f-loteria" name="id_loteria"><option value="">Todas</option></select></div>
      <div class="col-6 col-lg-2"><label class="form-label small mb-1" for="f-ticket">Ticket (inicio)</label>
        <input type="text" class="form-control form-control-sm" id="f-ticket" name="ticket" maxlength="30" placeholder="T-<?= $h(str_replace('-', '', $hoy)) ?>-"></div>
      <div class="col-6 col-lg-2"><label class="form-label small mb-1" for="f-tipo">Tipo</label>
        <select class="form-select form-select-sm" id="f-tipo" name="tipo"><option value="">Todos</option>
          <?php foreach ($tipos as $v => $t): ?><option value="<?= $h($v) ?>"><?= $h($t) ?></option><?php endforeach; ?>
        </select></div>
      <div class="col-6 col-lg-2"><label class="form-label small mb-1" for="f-estado">Estado</label>
        <select class="form-select form-select-sm" id="f-estado" name="estado"><option value="">Todos</option>
          <option value="confirmado">Confirmado</option><option value="informativo">Informativo</option></select></div>
      <div class="col-6 col-lg-2"><label class="form-label small mb-1" for="f-usuario">Usuario</label>
        <select class="form-select form-select-sm" id="f-usuario" name="id_usuario"><option value="">Todos</option></select></div>
      <div class="col-6 col-lg-2"><label class="form-label small mb-1" for="f-por-pagina">Por página</label>
        <select class="form-select form-select-sm" id="f-por-pagina" name="por_pagina">
          <option value="25">25</option><option value="50" selected>50</option><option value="100">100</option></select></div>
      <div class="col-12 col-lg-8 d-flex flex-wrap gap-2 justify-content-between mt-2">
        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-primary btn-sm" id="btn-filtrar">Filtrar</button>
          <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-limpiar">Limpiar</button>
        </div>
        <div class="d-flex gap-2" data-requiere-permiso="reporte.exportar">
          <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-excel">Excel</button>
          <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-csv">CSV</button>
          <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-imprimir">Imprimir</button>
        </div>
      </div>
    </div>
  </div>
</form>

<div id="rep-carga"></div>
<div id="rep-zona" class="d-none">
  <div class="section-head mt-4"><div><span class="eyebrow">RESULTADOS</span><h2 class="h5 mb-0">Detalle de operaciones</h2></div><span class="small text-secondary">Selecciona una fila para revisar sus detalles</span></div>
  <div class="tabla-scroll">
    <table class="table table-sm align-middle caja-tabla">
      <caption class="visually-hidden">Movimientos de caja en orden cronológico. Pulsa una fila para ver el detalle.</caption>
      <thead><tr>
        <th scope="col">Fecha y hora</th><th scope="col">Ticket</th><th scope="col">Operación</th>
        <th scope="col" class="text-end">Importe</th><th scope="col">Usuario</th><th scope="col" class="text-end">Saldo resultante</th>
      </tr></thead>
      <tbody id="rep-cuerpo"></tbody>
    </table>
  </div>
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 border-top pt-2">
    <div id="rep-totales" class="fw-semibold"></div>
    <nav class="d-flex align-items-center gap-2 no-imprimir" aria-label="Paginación">
      <button type="button" class="btn btn-outline-secondary btn-sm" id="pag-ant">Anterior</button>
      <span class="small" id="pag-texto" aria-live="polite"></span>
      <button type="button" class="btn btn-outline-secondary btn-sm" id="pag-sig">Siguiente</button>
    </nav>
  </div>
</div>
<div id="rep-vacio" class="d-none"></div>
<div class="explain-card no-imprimir"><strong>¿Cómo leer este reporte?</strong><p>Las entradas y salidas confirmadas afectan el saldo. Los intentos rechazados son informativos; los relevos únicamente separan turnos. El archivo exportado corresponde a la página visible, no a todo el historial filtrado.</p></div>
<div id="rep-error" class="d-none">
  <div class="alert alert-danger d-flex align-items-center justify-content-between" role="alert">
    <span id="rep-error-texto"></span>
    <button type="button" class="btn btn-outline-danger btn-sm" id="rep-reintentar">Reintentar</button>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php';
