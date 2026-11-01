/* Render de filas del libro de caja (6 columnas, §16.1). Compartido por «Estado de caja» y «Reporte». Solo presenta; no calcula. */
const FilasCaja = (() => {
  const $td = (texto, clase) => $('<td></td>').text(texto).addClass(clase || '');

  /** Icono SVG (máscara CSS, ver caja.css). nombre: alerta | check | relevo | candado | reloj | vacio. */
  const ico = (nombre) => $('<span class="ico" aria-hidden="true"></span>').addClass('ico-' + nombre);

  const estadoTexto = (estado) => estado === 'informativo' ? 'Informativo — sin cambio de saldo' : 'Confirmado';

  /** Signo de una diferencia de cierre ("-10.00" → salida, "5.00" → entrada, "0.00" → ninguno). */
  const efectoDe = (texto) => /^-/.test(texto) ? 'salida' : (/^0+(\.0+)?$/.test(texto) ? 'ninguno' : 'entrada');

  function filaEvento(f) {
    const $tr = $('<tr></tr>').attr('data-id', f.id);
    const $c  = $('<td colspan="6"></td>');
    if (f.tipo === 'relevo') {
      $tr.addClass('fila-relevo');
      $c.append(ico('relevo')).append(document.createTextNode(' Relevo: ' + f.evento.de + ' → ' + f.evento.a + ' · ' + Formato.hora(f.fecha_hora)));
    } else {
      const d = f.evento;
      $tr.addClass('fila-cierre');
      $c.append(ico('candado')).append($('<strong></strong>').text(' Cierre · ' + Formato.hora(f.fecha_hora) + ' · ' + f.usuario + ': '))
        .append(document.createTextNode('esperado ' + Formato.moneda(d.esperado) + ' · contado ' + Formato.moneda(d.contado)
          + ' · diferencia ' + Formato.movimiento(d.diferencia, efectoDe(d.diferencia))));
    }
    return $tr.append($c);
  }

  function filaMovimiento(f) {
    const info = f.estado === 'informativo';
    const $imp = $td('', 'text-end ' + (info ? '' : (f.efecto === 'entrada' ? 'monto-entrada' : f.efecto === 'salida' ? 'monto-salida' : '')));
    $imp.text(info ? Formato.moneda(f.importe) : Formato.movimiento(f.importe, f.efecto));
    if (info) $imp.append($('<div class="small texto-advertencia"></div>').append(ico('alerta')).append(document.createTextNode(' Informativo — sin cambio de saldo')));
    return $('<tr></tr>').attr('data-id', f.id).toggleClass('fila-informativa', info)
      .append($td(Formato.hora(f.fecha_hora)))
      .append($td(f.ticket || 'No aplica', f.ticket ? '' : 'text-secondary'))
      .append($td(f.operacion))
      .append($imp)
      .append($td(f.usuario))
      .append($td(info ? 'Sin cambio (' + Formato.moneda(f.saldo_resultante) + ')' : Formato.moneda(f.saldo_resultante), 'text-end'));
  }

  const tr = (f) => (f.tipo === 'relevo' || f.tipo === 'cierre') ? filaEvento(f) : filaMovimiento(f);

  /** Fila de detalle (observación, estado, movimiento relacionado) que se abre al pulsar una fila. */
  function detalle(f) {
    const $d = $('<dl class="row mb-0 small"></dl>');
    const par = (t, v) => $d.append($('<dt class="col-sm-3"></dt>').text(t)).append($('<dd class="col-sm-9"></dd>').text(v));
    par('Estado', estadoTexto(f.estado));
    par('Observación', f.detalle && f.detalle.observacion ? f.detalle.observacion : 'Sin observación');
    if (f.detalle && f.detalle.relacionado) par('Relacionado con', f.detalle.relacionado);
    return $('<tr class="fila-detalle"></tr>').append($('<td colspan="6"></td>').append($d));
  }

  return { tr, detalle, estadoTexto, ico };
})();
