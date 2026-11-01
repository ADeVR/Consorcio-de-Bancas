/* DATOS SIMULADOS del §17 (fase visual, sin BD). Se elimina al conectar la BD (fuente_datos = 'db').
   Fechas relativas a «hoy». Importes como texto con 2 decimales, igual que enviará el servidor. */
const MockDatos = (() => {
  const pad = (n) => String(n).padStart(2, '0');
  const ymd = (d) => d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
  const hoy = new Date();
  const H = ymd(hoy);
  const A = ymd(new Date(hoy.getFullYear(), hoy.getMonth(), hoy.getDate() - 1));
  const ts = (hm) => H + ' ' + hm + ':00';
  const tk = (fecha, n) => 'T-' + fecha.replace(/-/g, '') + '-' + String(n).padStart(6, '0');

  const usuarios = [
    { id: 1, usuario: 'superadmin1', rol: 'superadmin' },
    { id: 2, usuario: 'admin1',      rol: 'admin' },
    { id: 3, usuario: 'agente1',     rol: 'agente' },
    { id: 4, usuario: 'agente2',     rol: 'agente' },
  ];
  const loterias = [{ id: 1, nombre: 'Leidsa' }, { id: 2, nombre: 'Loteka' }];

  const turnos = [
    { id: 1, id_usuario: 3, usuario: 'agente1', inicio: ts('08:00'), fondo_inicial: '1000.00', estado: 'cerrado' },
    { id: 2, id_usuario: 4, usuario: 'agente2', inicio: ts('13:00'), fondo_inicial: '1550.00', estado: 'activo' },
  ];

  const operacion = { venta: 'Venta', pago: 'Pago', anulacion: 'Anulación', entrega: 'Entrega', retiro: 'Retiro',
                      intento_pago_rechazado: 'Pago sin fondos', relevo: 'Relevo', cierre: 'Cierre' };

  // id, tipo, hora, ticket, efecto, importe, id_usuario, saldo, turnos, loterias, observación, relacionado
  const mov = (id, tipo, hm, ticket, efecto, importe, idUsuario, saldo, turnosIds, loteriasIds, observacion, relacionado) => ({
    id, tipo, fecha_hora: ts(hm), ticket, operacion: operacion[tipo], efecto, importe,
    id_usuario: idUsuario, usuario: usuarios[idUsuario - 1].usuario, saldo_resultante: saldo,
    estado: tipo === 'intento_pago_rechazado' ? 'informativo' : 'confirmado',
    turnos: turnosIds, loterias: loteriasIds, detalle: { observacion, relacionado },
  });

  const movimientos = [
    mov(1, 'venta', '08:30', tk(H, 1), 'entrada', '20.00', 3, '1020.00', [1], [1, 2], 'Cobro de jugada (operación 101).', null),
    mov(2, 'entrega', '09:00', null, 'entrada', '2000.00', 3, '3020.00', [1], [], 'Iniciada por admin1 y confirmada por agente1. Motivo: fondo adicional de cambio.', 'Solicitud de efectivo #1'),
    mov(3, 'pago', '09:30', tk(A, 45), 'salida', '500.00', 3, '2520.00', [1], [2], 'Premio entregado al cliente.', 'Jugada de ayer'),
    mov(4, 'venta', '10:05', tk(H, 2), 'entrada', '50.00', 3, '2570.00', [1], [1], 'Cobro de jugada (operación 102).', null),
    mov(5, 'venta', '10:40', tk(H, 3), 'entrada', '30.00', 3, '2600.00', [1], [1], 'Cobro de jugada (operación 103).', null),
    mov(6, 'anulacion', '11:15', tk(H, 2), 'salida', '50.00', 2, '2550.00', [1], [1], 'Anulación aprobada por admin1.', 'Reverso del movimiento #4'),
    mov(7, 'intento_pago_rechazado', '12:10', tk(A, 51), 'ninguno', '5000.00', 3, '2550.00', [1], [1], 'No había efectivo suficiente para pagar el premio. Acudir a la banca principal.', 'Jugada de ayer'),
    mov(8, 'retiro', '12:30', null, 'salida', '1000.00', 3, '1550.00', [1], [], 'Iniciado por admin1 y confirmado por agente1. Motivo: depósito en banca principal.', 'Solicitud de efectivo #2'),
    { id: 'c1', tipo: 'cierre', fecha_hora: ts('12:55'), ticket: null, operacion: operacion.cierre, efecto: 'ninguno', importe: '0.00',
      id_usuario: 3, usuario: 'agente1', saldo_resultante: null, estado: 'confirmado', turnos: [1], loterias: [],
      detalle: { observacion: 'Cierre del turno 1 sin diferencia.', relacionado: null },
      evento: { esperado: '1550.00', contado: '1550.00', diferencia: '0.00' } },
    { id: 'r1', tipo: 'relevo', fecha_hora: ts('13:00'), ticket: null, operacion: operacion.relevo, efecto: 'ninguno', importe: '0.00',
      id_usuario: 4, usuario: 'agente2', saldo_resultante: null, estado: 'confirmado', turnos: [1, 2], loterias: [],
      detalle: { observacion: 'Relevo confirmado por ambos agentes.', relacionado: null },
      evento: { de: 'agente1', a: 'agente2' } },
    mov(9, 'venta', '13:20', tk(H, 4), 'entrada', '20.00', 4, '1570.00', [2], [2], 'Cobro de jugada (operación 105).', null),
    mov(10, 'venta', '14:05', tk(H, 5), 'entrada', '100.00', 4, '1670.00', [2], [1], 'Cobro de jugada (operación 106).', null),
  ];

  const pendientesEfectivo = [
    { id: 3, tipo: 'entrega', importe: '500.00', inicia: 'admin1', fecha_hora: ts('15:10'), motivo: 'Fondo para cambio de la tarde' },
  ];

  return { hoy: H, usuarios, loterias, turnos, movimientos, pendientesEfectivo, saldo: '1670.00', limite: '100.00', banca: 'Banca Principal' };
})();
