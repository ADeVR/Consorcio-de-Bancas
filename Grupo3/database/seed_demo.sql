-- Datos de demostración (§17). Fechas relativas a hoy. Requiere schema.sql y sim_externos.sql.
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE movimiento_caja; TRUNCATE cierre_caja; TRUNCATE solicitud_efectivo; TRUNCATE turno_caja; TRUNCATE caja; TRUNCATE banca;
TRUNCATE sim_operacion; TRUNCATE sim_jugada_detalle; TRUNCATE sim_jugada; TRUNCATE sim_sorteo; TRUNCATE sim_loteria; TRUNCATE sim_usuario; TRUNCATE sim_auditoria;
SET FOREIGN_KEY_CHECKS = 1;
SET @hoy := CURDATE(); SET @ayer := @hoy - INTERVAL 1 DAY;
SET @t := DATE_FORMAT(@hoy,'%Y%m%d'); SET @ta := DATE_FORMAT(@ayer,'%Y%m%d');

-- Externos simulados -----------------------------------------------------------
INSERT INTO sim_usuario (id_usuario,nombre,usuario,rol,estado,fecha_creacion) VALUES
 (1,'Superadministrador de prueba','superadmin1','superadmin','activo',NOW()),
 (2,'Administrador de prueba','admin1','admin','activo',NOW()),
 (3,'Agente de prueba 1','agente1','agente','activo',NOW()),
 (4,'Agente de prueba 2','agente2','agente','activo',NOW());
INSERT INTO sim_loteria VALUES (1,'Leidsa','activa'),(2,'Loteka','activa');
INSERT INTO sim_sorteo VALUES (1,1,'Quiniela Leidsa','20:55:00'),(2,2,'Quiniela Loteka','19:55:00'),(3,1,'Loto Pool','20:55:00');
INSERT INTO sim_jugada VALUES
 (1,CONCAT('T-',@t,'-000001'),3,NULL,TIMESTAMP(@hoy,'08:30:00'),'confirmada',20.00),
 (2,CONCAT('T-',@t,'-000002'),3,NULL,TIMESTAMP(@hoy,'10:05:00'),'anulada',50.00),
 (3,CONCAT('T-',@t,'-000003'),3,NULL,TIMESTAMP(@hoy,'10:40:00'),'confirmada',30.00),
 (4,CONCAT('T-',@t,'-000004'),4,NULL,TIMESTAMP(@hoy,'13:20:00'),'confirmada',20.00),
 (5,CONCAT('T-',@t,'-000005'),4,NULL,TIMESTAMP(@hoy,'14:05:00'),'confirmada',100.00),
 (6,CONCAT('T-',@ta,'-000045'),3,NULL,TIMESTAMP(@ayer,'16:00:00'),'pagada',10.00),
 (7,CONCAT('T-',@ta,'-000051'),3,NULL,TIMESTAMP(@ayer,'17:10:00'),'ganadora',100.00),
 (8,CONCAT('T-',@ta,'-000052'),3,NULL,TIMESTAMP(@ayer,'17:30:00'),'ganadora',10.00);
INSERT INTO sim_jugada_detalle (id_detalle,id_jugada,id_sorteo,numeros_jugados,monto_apostado,posible_pago) VALUES
 (1,1,1,'25',10.00,500.00),(2,1,2,'25',10.00,500.00),(3,2,1,'31',50.00,2500.00),
 (4,3,3,'05-12-18-24-30',30.00,NULL),(5,4,2,'40',20.00,1000.00),(6,5,1,'18',100.00,5000.00),
 (7,6,2,'25',10.00,500.00),(8,7,1,'12',100.00,5000.00),(9,8,2,'33',10.00,500.00);
INSERT INTO sim_operacion (id_operacion,tipo,id_jugada,monto,id_usuario,decision,fecha_hora) VALUES
 (101,'cobro',1,20.00,3,NULL,TIMESTAMP(@hoy,'08:30:00')),
 (102,'cobro',2,50.00,3,NULL,TIMESTAMP(@hoy,'10:05:00')),
 (103,'cobro',3,30.00,3,NULL,TIMESTAMP(@hoy,'10:40:00')),
 (104,'anulacion',2,50.00,2,'aprobada',TIMESTAMP(@hoy,'11:15:00')),
 (105,'cobro',4,20.00,4,NULL,TIMESTAMP(@hoy,'13:20:00')),
 (106,'cobro',5,100.00,4,NULL,TIMESTAMP(@hoy,'14:05:00'));

-- Tablas de Caja ---------------------------------------------------------------
INSERT INTO banca VALUES (1,'Banca Principal','activa');
INSERT INTO caja VALUES (1,1,1670.00,100.00,TIMESTAMP(@hoy,'14:05:00'));
INSERT INTO turno_caja (id_turno,id_caja,id_usuario_saliente,id_usuario_entrante,hora_inicio,hora_fin,confirmado_saliente,confirmado_entrante,estado,fondo_inicial,fecha_solicitud) VALUES
 (1,1,NULL,3,TIMESTAMP(@hoy,'08:00:00'),TIMESTAMP(@hoy,'12:55:00'),1,1,'cerrado',1000.00,TIMESTAMP(@hoy,'08:00:00')),
 (2,1,3,4,TIMESTAMP(@hoy,'13:00:00'),NULL,1,1,'activo',1550.00,TIMESTAMP(@hoy,'12:58:00'));
INSERT INTO solicitud_efectivo (id_solicitud_efectivo,id_caja,tipo,importe,motivo,id_usuario_inicia,id_usuario_confirma,estado,fecha_inicio,fecha_confirmacion) VALUES
 (1,1,'entrega',2000.00,'Fondo adicional',2,3,'confirmada',TIMESTAMP(@hoy,'08:55:00'),TIMESTAMP(@hoy,'09:00:00')),
 (2,1,'retiro',1000.00,'Retiro parcial',2,3,'confirmada',TIMESTAMP(@hoy,'12:25:00'),TIMESTAMP(@hoy,'12:30:00')),
 (3,1,'entrega',500.00,'Refuerzo de tarde',2,NULL,'pendiente',TIMESTAMP(@hoy,'14:30:00'),NULL);
INSERT INTO movimiento_caja (id_movimiento,id_caja,id_turno,tipo_movimiento,efecto,importe,id_jugada,id_operacion,id_solicitud_efectivo,id_movimiento_relacionado,id_usuario,estado,saldo_resultante,observacion,clave_idempotencia,fecha_hora) VALUES
 (1,1,1,'venta','entrada',20.00,1,101,NULL,NULL,3,'confirmado',1020.00,NULL,'op:101',TIMESTAMP(@hoy,'08:30:00')),
 (2,1,1,'entrega','entrada',2000.00,NULL,NULL,1,NULL,3,'confirmado',3020.00,'Fondo adicional','sol:1',TIMESTAMP(@hoy,'09:00:00')),
 (3,1,1,'pago','salida',500.00,6,NULL,NULL,NULL,3,'confirmado',2520.00,'Premio entregado','pago:6',TIMESTAMP(@hoy,'09:30:00')),
 (4,1,1,'venta','entrada',50.00,2,102,NULL,NULL,3,'confirmado',2570.00,NULL,'op:102',TIMESTAMP(@hoy,'10:05:00')),
 (5,1,1,'venta','entrada',30.00,3,103,NULL,NULL,3,'confirmado',2600.00,NULL,'op:103',TIMESTAMP(@hoy,'10:40:00')),
 (6,1,1,'anulacion','salida',50.00,2,104,NULL,4,2,'confirmado',2550.00,'Anulación aprobada','rev:4',TIMESTAMP(@hoy,'11:15:00')),
 (7,1,1,'intento_pago_rechazado','ninguno',5000.00,7,NULL,NULL,NULL,3,'informativo',2550.00,'Saldo insuficiente',NULL,TIMESTAMP(@hoy,'12:10:00')),
 (8,1,1,'retiro','salida',1000.00,NULL,NULL,2,NULL,3,'confirmado',1550.00,'Retiro parcial','sol:2',TIMESTAMP(@hoy,'12:30:00')),
 (9,1,2,'venta','entrada',20.00,4,105,NULL,NULL,4,'confirmado',1570.00,NULL,'op:105',TIMESTAMP(@hoy,'13:20:00')),
 (10,1,2,'venta','entrada',100.00,5,106,NULL,NULL,4,'confirmado',1670.00,NULL,'op:106',TIMESTAMP(@hoy,'14:05:00'));
INSERT INTO cierre_caja (id_cierre,id_caja,id_turno,fondo_inicial,total_entradas,total_salidas,saldo_esperado,efectivo_contado,diferencia,saldo_conciliado,justificacion,id_usuario,requiere_autorizacion,id_administrador_autoriza,estado,fecha_hora,fecha_autorizacion) VALUES
 (1,1,1,1000.00,2100.00,1550.00,1550.00,1550.00,0.00,1550.00,NULL,3,0,NULL,'cerrado',TIMESTAMP(@hoy,'12:55:00'),NULL);
