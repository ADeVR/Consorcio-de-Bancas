/* Cliente AJAX (jQuery $.ajax). Todas las llamadas devuelven una Promise que SIEMPRE resuelve {ok, data, error}.
   En fuente_datos = 'mock' sigue usando $.ajax: un transporte de jQuery (mock/api_mock.js) responde en lugar del servidor. */
const Api = (() => {
  const csrf = () => $('meta[name="csrf"]').attr('content') || '';
  const base = () => String($('body').data('base') || '');

  function llamar(metodo, url, datos) {
    return new Promise((resolver) => {
      $.ajax({
        url: base() + '/' + url.replace(/^\//, ''),
        method: metodo,
        dataType: 'json',
        contentType: metodo === 'GET' ? undefined : 'application/json; charset=utf-8',
        data: metodo === 'GET' ? datos : JSON.stringify(datos || {}),
        headers: { 'X-CSRF-Token': csrf() },
      })
        .done((r) => resolver(r))
        .fail((xhr) => {
          const r = xhr.responseJSON;
          if (r && r.ok === false && r.error) {
            if (r.error.codigo === 'SESION_EXPIRADA') { window.location.href = base() + '/index.php'; }
            return resolver(r);
          }
          resolver({ ok: false, error: { codigo: 'ERROR_RED', severidad: 'error',
            mensaje: 'No se pudo conectar con el servidor. Tus datos no se perdieron. Intenta de nuevo.' } });
        });
    });
  }

  return {
    get:  (url, datos) => llamar('GET', url, datos),
    post: (url, datos) => llamar('POST', url, datos),
  };
})();
