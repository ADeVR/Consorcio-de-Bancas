/* Servidor simulado (fase visual, sin BD). Responde {ok,data,error} con la MISMA forma que enviará el API real (§12),
   para que las pantallas no cambien al conectar la BD. Se elimina con fuente_datos = 'db'.
   Se enchufa a jQuery como transporte AJAX (ver el final del archivo).
   Escenarios de prueba visual: ?mock=lento | sin_turno | error | vacio
   La aritmética de dinero de aquí (sumas de totales) imita al servidor y NO debe copiarse a las pantallas. */
(() => {
  const D = MockDatos;
  const escenario = new URLSearchParams(window.location.search).get('mock') || '';
  const activo = () => String($('body').data('fuente')) === 'mock';

  const ERRORES = {
    SIN_PERMISO:          ['error', 'No tienes permiso para realizar esta acción.'],
    CAJA_SIN_TURNO:       ['advertencia', 'No hay un turno activo en la caja. Registra el relevo para continuar.'],
    PARAMETROS_INVALIDOS: ['error', 'Faltan datos. Revisa el formulario.'],
    ERROR_INTERNO:        ['error', 'No se pudo completar la operación. Tus datos no se perdieron. Intenta de nuevo.'],
  };

  const responder_ = (cuerpo) => new Promise((res) => setTimeout(() => res(cuerpo), escenario === 'lento' ? 2500 : 250));
  const ok = (data) => responder_({ ok: true, data });
  const fallo = (codigo, campo) => responder_({ ok: false, error: Object.assign(
    { codigo, severidad: ERRORES[codigo][0], mensaje: ERRORES[codigo][1] }, campo ? { campo } : {}) });

  // --- dinero como centavos enteros, solo dentro del mock ---
  const aCent = (t) => { const [e, d = ''] = String(t).split('.'); return parseInt(e, 10) * 100 + parseInt(d.padEnd(2, '0'), 10); };
  const aTexto = (c) => Math.floor(c / 100) + '.' + String(c % 100).padStart(2, '0');

  // --- sesión simulada ---
  const usuario = () => {
    const id = Number($('body').data('idUsuario'));
    return D.usuarios.find((u) => u.id === id) || null;
  };
  const turnoActivo = () => D.turnos.find((t) => t.estado === 'activo') || null;
  const puede = (permiso) => String($('body').data('permisos')).split(',').includes(permiso);

  /** D-05: el admin ve todo; el agente solo las filas de sus turnos. */
  function visibles(u) {
    if (u.rol === 'admin') return D.movimientos;
    const mios = D.turnos.filter((t) => t.id_usuario === u.id).map((t) => t.id);
    return D.movimientos.filter((f) => f.turnos.some((id) => mios.includes(id)));
  }

  const publica = (f) => { const { turnos, loterias, ...resto } = f; return resto; };
  const porHora = (a, b) => a.fecha_hora < b.fecha_hora ? -1 : a.fecha_hora > b.fecha_hora ? 1 : 0;

  // --- GET api/caja/estado.php ---
  function estado() {
    const u = usuario(), t = turnoActivo();
    if (!u || !puede('caja.ver_saldo')) return fallo('SIN_PERMISO');
    if (escenario === 'error') return fallo('ERROR_INTERNO');
    if (escenario === 'sin_turno' || !t) return fallo('CAJA_SIN_TURNO');
    const veSaldo = u.rol === 'admin' || t.id_usuario === u.id;
    const ultimos = veSaldo ? visibles(u).slice().sort(porHora).slice(-5).reverse().map(publica) : [];
    const ultimo = D.movimientos.filter((f) => f.tipo !== 'relevo' && f.tipo !== 'cierre').slice(-1)[0];
    return ok({
      banca: D.banca,
      ve_saldo: veSaldo,
      saldo: veSaldo ? D.saldo : null,
      actualizado: veSaldo ? ultimo.fecha_hora : null,
      turno: { id: t.id, usuario: t.usuario, desde: t.inicio, fondo_inicial: veSaldo ? t.fondo_inicial : null },
      ultimos,
      pendientes: veSaldo ? { efectivo: D.pendientesEfectivo.length, cierres: 0 } : { efectivo: 0, cierres: 0 },
    });
  }

  // --- GET api/reporte/movimientos.php ---
  function movimientos(q) {
    const u = usuario();
    if (!u || !puede('reporte.consultar')) return fallo('SIN_PERMISO');
    if (escenario === 'error') return fallo('ERROR_INTERNO');
    const desde = q.desde || D.hoy, hasta = q.hasta || D.hoy;
    if (desde > hasta) return fallo('PARAMETROS_INVALIDOS', 'hasta');
    if (q.ticket && !/^[A-Za-z0-9-]{1,30}$/.test(q.ticket)) return fallo('PARAMETROS_INVALIDOS', 'ticket');

    const base = visibles(u);
    let filas = escenario === 'vacio' ? [] : base.filter((f) => f.fecha_hora.slice(0, 10) >= desde && f.fecha_hora.slice(0, 10) <= hasta);
    if (q.id_loteria) filas = filas.filter((f) => f.loterias.includes(Number(q.id_loteria)));            // oculta relevos y cierres
    if (q.ticket)     filas = filas.filter((f) => f.ticket && f.ticket.toUpperCase().startsWith(q.ticket.toUpperCase()));
    if (q.tipo)       filas = filas.filter((f) => f.tipo === q.tipo);
    if (q.estado)     filas = filas.filter((f) => f.estado === q.estado);
    if (q.id_usuario) filas = filas.filter((f) => f.id_usuario === Number(q.id_usuario));
    filas = filas.slice().sort(porHora);

    const suma = (lista, efecto) => aTexto(lista.filter((f) => f.efecto === efecto && f.estado === 'confirmado')
      .reduce((s, f) => s + aCent(f.importe), 0));
    const porPagina = [25, 50, 100].includes(Number(q.por_pagina)) ? Number(q.por_pagina) : 50;
    const paginas = Math.max(1, Math.ceil(filas.length / porPagina));
    const pagina = Math.min(Math.max(1, Number(q.pagina) || 1), paginas);
    const pag = filas.slice((pagina - 1) * porPagina, pagina * porPagina);

    return ok({
      filas: pag.map(publica),
      totales: {
        filtro: { entradas: suma(filas, 'entrada'), salidas: suma(filas, 'salida') },
        pagina: { entradas: suma(pag, 'entrada'),   salidas: suma(pag, 'salida') },
      },
      paginacion: { pagina, por_pagina: porPagina, total: filas.length, paginas },
      filtros_disponibles: {
        loterias: D.loterias,
        usuarios: D.usuarios.filter((x) => base.some((f) => f.id_usuario === x.id)).map((x) => ({ id: x.id, usuario: x.usuario })),
      },
    });
  }

  const RUTAS = {
    'GET api/caja/estado.php': estado,
    'GET api/reporte/movimientos.php': movimientos,
  };

  /** Transporte AJAX de jQuery: con fuente_datos = 'mock' atiende las rutas simuladas como si fuera el servidor.
   *  Las pantallas siguen llamando a $.ajax (core/api.js); las rutas no simuladas siguen al servidor real. */
  $.ajaxTransport('json', (opciones, original) => {
    const ruta = /api\/[^?]+/.exec(opciones.url);
    const atender = activo() && ruta ? RUTAS[String(opciones.type || 'GET').toUpperCase() + ' ' + ruta[0]] : null;
    if (!atender) return undefined;
    let cancelado = false;
    return {
      send(_cabeceras, completar) {
        let datos = original.data;
        if (typeof datos === 'string') { try { datos = JSON.parse(datos); } catch (e) { datos = {}; } }
        atender(datos || {}).then((cuerpo) => { if (!cancelado) completar(200, 'OK', { json: cuerpo }); });
      },
      abort() { cancelado = true; },
    };
  });
})();
