/* P2 · Reporte cronológico (6 columnas + filtros combinables). Sin cálculos: todo viene del servidor. */
$(function () {
  const $form = $('#form-filtros');
  let pagina = 1;
  let catalogoCargado = false;
  let filasActuales = [];                                   // lo que se ve en pantalla (para exportar)

  const filtros = () => {
    const d = {};
    $form.serializeArray().forEach(({ name, value }) => { if (value !== '') d[name] = value; });
    d.pagina = pagina;
    return d;
  };

  function llenarCatalogos(c) {
    $('#f-loteria').prop('disabled', !c.loterias.length).attr('title', c.loterias.length ? '' : 'Disponible cuando se integre el catálogo de loterías');
    c.loterias.forEach((l) => $('#f-loteria').append($('<option></option>').val(l.id).text(l.nombre)));
    c.usuarios.forEach((u) => $('#f-usuario').append($('<option></option>').val(u.id).text(u.usuario)));
    catalogoCargado = true;
  }

  function alternarDetalle($tr, f) {
    const $sig = $tr.next('.fila-detalle');
    const abierto = $sig.length > 0;
    if (abierto) $sig.remove(); else $tr.after(FilasCaja.detalle(f));
    $tr.attr('aria-expanded', String(!abierto));
  }

  function pintar(d) {
    filasActuales = d.filas;
    // La página visualizada es la que se exporta, no el conjunto completo filtrado.
    const $cuerpo = $('#rep-cuerpo').empty();
    d.filas.forEach((f) => {
      const $tr = FilasCaja.tr(f).addClass('fila-click').attr({ tabindex: 0, 'aria-expanded': 'false' });
      $tr.on('click', () => alternarDetalle($tr, f))
         .on('keydown', (e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); alternarDetalle($tr, f); } });
      $cuerpo.append($tr);
    });
    $('#rep-totales').text('Totales del filtro: Entradas ' + Formato.moneda(d.totales.filtro.entradas) + ' · Salidas ' + Formato.moneda(d.totales.filtro.salidas));
    const e = Number(d.totales.filtro.entradas || 0);
    const sal = Number(d.totales.filtro.salidas || 0);
    $('#rep-entradas').text(Formato.moneda(d.totales.filtro.entradas));
    $('#rep-salidas').text(Formato.moneda(d.totales.filtro.salidas));
    $('#rep-neto').text(Formato.moneda((e - sal).toFixed(2)));
    $('#rep-cantidad').text(d.paginacion.total.toLocaleString('es-DO'));
    const p = d.paginacion;
    $('#pag-texto').text('Página ' + p.pagina + ' de ' + p.paginas + ' · ' + p.total + (p.total === 1 ? ' fila' : ' filas'));
    $('#pag-ant').prop('disabled', p.pagina <= 1);
    $('#pag-sig').prop('disabled', p.pagina >= p.paginas);
  }

  function mostrar(sel) { ['#rep-zona', '#rep-vacio', '#rep-error'].forEach((v) => $(v).toggleClass('d-none', v !== sel)); }

  async function cargar() {
    const $btn = $('#btn-filtrar').prop('disabled', true);          // evita doble envío
    $('.is-invalid').removeClass('is-invalid');
    mostrar(null);
    UI.cargando('#rep-carga');
    const r = await Api.get('api/reporte/movimientos.php', filtros());
    $('#rep-carga').empty();
    $btn.prop('disabled', false);
    if (!r.ok) {
      if (r.error.campo) { UI.error(r.error); return mostrar('#rep-zona'); }   // el error de campo no borra lo escrito ni la tabla previa
      $('#rep-error-texto').text(r.error.mensaje);
      return mostrar('#rep-error');
    }
    if (!catalogoCargado) llenarCatalogos(r.data.filtros_disponibles);
    $('#alertas').empty();
    filasActuales = [];
    $('#rep-totales').text('');
    $('#pag-texto').text('');
    $('#pag-ant, #pag-sig').prop('disabled', true);
    if (r.data.paginacion.total === 0) {
      $('#rep-entradas, #rep-salidas, #rep-neto').text(Formato.moneda('0.00'));
      $('#rep-cantidad').text('0');
      UI.vacio('#rep-vacio', 'No hay movimientos con estos filtros. Prueba con otro rango de fechas.');
      return mostrar('#rep-vacio');
    }
    pintar(r.data);
    mostrar('#rep-zona');
  }

  $form.on('submit', (e) => {
    e.preventDefault();
    const desde = $('#f-desde').val(), hasta = $('#f-hasta').val();
    if (desde && hasta && desde > hasta) {
      return UI.error({ severidad: 'error', mensaje: 'La fecha «Hasta» no puede ser anterior a «Desde».', campo: 'hasta' });
    }
    pagina = 1;
    cargar();
  });
  $('#btn-limpiar').on('click', () => {
    $form[0].reset();
    $('#f-desde, #f-hasta').val($form.data('hoy'));
    pagina = 1;
    cargar();
  });
  $('#f-por-pagina').on('change', () => { pagina = 1; cargar(); });
  $('#pag-ant').on('click', () => { if (pagina > 1) { pagina -= 1; cargar(); } });
  $('#pag-sig').on('click', () => { if (!$('#pag-sig').prop('disabled')) { pagina += 1; cargar(); } });
  $('#rep-reintentar').on('click', cargar);
  $('#btn-excel').on('click', () => Exportar.xml(filasActuales));
  $('#btn-csv').on('click', () => Exportar.csv(filasActuales));
  $('#btn-imprimir').on('click', () => window.print());

  cargar();
});
