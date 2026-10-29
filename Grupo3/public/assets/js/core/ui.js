/* Piezas de interfaz reutilizables: alertas, confirmación y selector de usuario simulado. */
const UI = (() => {
  const CLASE = { error: 'danger', advertencia: 'warning', informacion: 'info', exito: 'success' };

  /** tipo: error | advertencia | informacion | exito. El texto se inserta como texto (sin HTML). */
  function alerta(tipo, texto, destino = '#alertas') {
    const $a = $('<div class="alert alert-dismissible fade show" role="alert"></div>')
      .addClass('alert-' + (CLASE[tipo] || 'info'))
      .append($('<span></span>').text(texto))
      .append('<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>');
    $(destino).empty().append($a);
  }

  /** Muestra un error del API y marca el campo si lo indica. No borra lo escrito. */
  function error(err, destino = '#alertas') {
    alerta(err.severidad || 'error', err.mensaje, destino);
    $('.is-invalid').removeClass('is-invalid');
    if (err.campo) $('[name="' + err.campo + '"]').addClass('is-invalid').trigger('focus');
  }

  /** Modal de confirmación. Devuelve Promise<boolean>. */
  function confirmar(titulo, texto, etiqueta = 'Confirmar') {
    return new Promise((resolver) => {
      const el = $('#modal-confirmar')[0];
      const modal = bootstrap.Modal.getOrCreateInstance(el);
      let aceptado = false;
      $('#modal-confirmar-titulo').text(titulo);
      $('#modal-confirmar-texto').text(texto);
      $('#modal-confirmar-ok').text(etiqueta).off('click').on('click', () => { aceptado = true; modal.hide(); });
      $(el).off('hidden.bs.modal').one('hidden.bs.modal', () => resolver(aceptado));
      modal.show();
    });
  }

  /** Esqueleto de carga dentro de `selector` (se reemplaza al pintar el resultado). */
  function cargando(selector) {
    $(selector).html('<div class="skeleton-grupo" role="status" aria-label="Cargando…"><div class="skeleton"></div><div class="skeleton"></div><div class="skeleton"></div></div>');
  }

  /** Estado vacío con texto plano. */
  function vacio(selector, texto) {
    $(selector).empty().append($('<div class="estado-vacio text-center py-5"></div>')
      .append('<span class="ico ico-vacio" aria-hidden="true"></span>').append($('<p class="mb-0"></p>').text(texto)));
  }

  function initSelectorUsuario() {
    $('#selector-usuario').on('change', async function () {
      const r = await Api.post('api/dev/cambiar_usuario.php', { id_usuario: Number(this.value) });
      if (r.ok) window.location.reload(); else error(r.error);
    });
  }

  $(initSelectorUsuario);
  return { alerta, error, confirmar, cargando, vacio };
})();
