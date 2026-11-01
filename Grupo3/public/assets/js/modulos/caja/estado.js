/* P1 · Estado de caja. Patrón: cargar() → esqueleto → Api.get → pintar; el error conserva la pantalla y ofrece reintento. */
$(function () {
  const vistas = ['#estado-vista', '#estado-sin-turno', '#estado-error'];
  const mostrar = (sel) => { vistas.forEach((v) => $(v).toggleClass('d-none', v !== sel)); };

  function pintar(d) {
    $('#est-banca').text(d.banca);
    $('#est-saldo').text(d.ve_saldo ? Formato.moneda(d.saldo) : 'No disponible');
    $('#est-actualizado').text(d.ve_saldo ? 'Actualizado ' + Formato.hora(d.actualizado) : '');
    $('#est-turno-usuario').text(d.turno.usuario);
    $('#est-turno-desde').text('Desde ' + Formato.hora(d.turno.desde));
    $('#est-turno-fondo').text(d.ve_saldo ? 'Fondo inicial: ' + Formato.moneda(d.turno.fondo_inicial) : '');
    const pendiente = (sel, n, uno, varios) => $(sel).empty().append(FilasCaja.ico('reloj')).append(document.createTextNode(' ' + n + (n === 1 ? uno : varios)));
    pendiente('#est-pend-efectivo', d.pendientes.efectivo, ' entrega o retiro por confirmar', ' entregas o retiros por confirmar');
    pendiente('#est-pend-cierres', d.pendientes.cierres, ' cierre por autorizar', ' cierres por autorizar');
    $('#est-aviso-agente').toggleClass('d-none', d.ve_saldo);
    $('#est-movimientos').toggleClass('d-none', !d.ve_saldo);

    const $cuerpo = $('#est-ultimos').empty();
    if (d.ultimos.length === 0) $cuerpo.append($('<tr><td colspan="6" class="text-center text-secondary py-3">Aún no hay movimientos en este turno.</td></tr>'));
    d.ultimos.forEach((f) => $cuerpo.append(FilasCaja.tr(f)));
    mostrar('#estado-vista');
  }

  async function cargar() {
    mostrar(null);
    UI.cargando('#estado-carga');
    const r = await Api.get('api/caja/estado.php');
    $('#estado-carga').empty();
    if (r.ok) return pintar(r.data);
    if (r.error.codigo === 'CAJA_SIN_TURNO') { $('#estado-sin-turno-texto').text(r.error.mensaje); return mostrar('#estado-sin-turno'); }
    $('#estado-error-texto').text(r.error.mensaje);
    mostrar('#estado-error');
  }

  $('#estado-reintentar').on('click', cargar);
  cargar();
});
