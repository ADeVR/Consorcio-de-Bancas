/* Modo estático (pantallas/*.html, sin PHP): arma navbar, menú y usuario simulado en el navegador.
   Solo para la fase visual; se elimina junto con mock/. Debe cargarse ANTES de permisos.js y de los scripts de página. */
(() => {
  const USUARIOS = [
    { id: 1, usuario: 'superadmin1', nombre: 'Superadministrador', rol: 'superadmin' },
    { id: 2, usuario: 'admin1',      nombre: 'Administrador Uno',  rol: 'admin' },
    { id: 3, usuario: 'agente1',     nombre: 'Agente Uno',         rol: 'agente' },
    { id: 4, usuario: 'agente2',     nombre: 'Agente Dos',         rol: 'agente' },
  ];
  const ETIQUETA = { superadmin: 'Superadministrador', admin: 'Administrador', agente: 'Agente' };
  // Espejo de config/permisos.xml (permiso → roles con acceso)
  const PERMISOS = {
    'caja.ver_saldo': ['admin', 'agente'], 'caja.iniciar_efectivo': ['admin'], 'caja.confirmar_efectivo': ['agente'],
    'caja.relevo': ['agente'], 'caja.cerrar': ['agente'], 'caja.autorizar_cierre': ['admin'],
    'reporte.consultar': ['admin', 'agente'], 'reporte.exportar': ['admin'], 'pago.registrar': ['admin', 'agente'],
  };
  const MENU = [ // [archivo, etiqueta, permiso]
    ['estado.html', 'Estado de caja', 'caja.ver_saldo'],
    ['reporte.html', 'Reporte', 'reporte.consultar'],
  ];

  const guardar = (id) => { try { localStorage.setItem('caja_usuario_sim', String(id)); } catch (e) { /* sin almacenamiento */ } };
  const leer = () => { try { return Number(localStorage.getItem('caja_usuario_sim')); } catch (e) { return 0; } };

  const u = USUARIOS.find((x) => x.id === leer()) || USUARIOS[1];          // por defecto admin1
  const permisos = Object.keys(PERMISOS).filter((p) => PERMISOS[p].includes(u.rol));
  const b = document.body;
  const permisoPagina = b.dataset.permiso || '';
  const titulo = b.dataset.titulo || 'Caja y Reportes';
  const hoy = new Date(); const pad = (n) => String(n).padStart(2, '0');
  const hoyTxt = hoy.getFullYear() + '-' + pad(hoy.getMonth() + 1) + '-' + pad(hoy.getDate());

  b.dataset.fuente = 'mock'; b.dataset.base = '.'; b.dataset.idUsuario = String(u.id);
  b.dataset.rol = u.rol; b.dataset.permisos = permisos.join(',');
  const form = document.getElementById('form-filtros');
  if (form) {
    form.dataset.hoy = hoyTxt;
    form.querySelectorAll('[name=desde],[name=hasta]').forEach((i) => { i.value = hoyTxt; });
    const t = form.querySelector('[name=ticket]'); if (t) t.placeholder = 'T-' + hoyTxt.replace(/-/g, '') + '-';
  }

  const el = (tag, cls, texto) => { const e = document.createElement(tag); if (cls) e.className = cls; if (texto !== undefined) e.textContent = texto; return e; };
  const contenido = document.getElementById('contenido');

  const nav = el('nav', 'navbar navbar-expand navbar-caja px-3 no-imprimir');
  nav.append(el('span', 'navbar-brand mb-0 h1', 'Consorcio de Banca · Caja y Reportes'));
  const der = el('div', 'ms-auto d-flex align-items-center gap-3');
  const lab = el('label', 'd-flex align-items-center gap-2 mb-0 small'); lab.append(el('span', '', 'Usuario simulado:'));
  const sel = el('select', 'form-select form-select-sm w-auto'); sel.setAttribute('aria-label', 'Usuario simulado');
  USUARIOS.forEach((x) => { const o = el('option', '', x.usuario + ' (' + ETIQUETA[x.rol] + ')'); o.value = x.id; o.selected = x.id === u.id; sel.append(o); });
  sel.addEventListener('change', () => { guardar(sel.value); location.reload(); });
  lab.append(sel); der.append(lab, el('span', 'small', u.nombre + ' · ' + ETIQUETA[u.rol])); nav.append(der);

  const lado = el('aside', 'col-md-2 py-3 sidebar-caja no-imprimir');
  const menu = el('nav', 'nav flex-column'); menu.setAttribute('aria-label', 'Navegación principal');
  MENU.filter((m) => permisos.includes(m[2])).forEach(([f, t]) => {
    const a = el('a', 'nav-link' + (location.pathname.endsWith('/' + f) ? ' active' : ''), t); a.href = f; menu.append(a);
  });
  lado.append(menu);

  const main = el('main', 'col-md-10 py-3 px-4'); main.id = 'contenido-principal';
  main.append(el('h1', 'h4 mb-3', titulo));
  const alertas = el('div'); alertas.id = 'alertas'; alertas.setAttribute('aria-live', 'polite'); main.append(alertas);
  if (permisoPagina && !permisos.includes(permisoPagina)) {
    const a = el('div', 'alert alert-danger'); a.setAttribute('role', 'alert');
    a.append(el('strong', '', 'Acceso denegado. '), document.createTextNode('No tienes permiso para realizar esta acción.'));
    main.append(a); if (contenido) contenido.remove();
  } else if (contenido) { main.append(contenido); }

  const fila = el('div', 'row'); fila.append(lado, main);
  const cont = el('div', 'container-fluid'); cont.append(fila);
  const salto = el('a', 'salto no-imprimir', 'Saltar al contenido'); salto.href = '#contenido-principal';
  b.prepend(cont); b.prepend(nav); b.prepend(salto);
})();
