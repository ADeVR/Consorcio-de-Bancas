-- M3 Caja y Reportes · tablas propias de Caja (propuesta para el diccionario de M5)
-- MySQL 8 / MariaDB >= 10.4 · InnoDB · utf8mb4. Reejecutable: borra y recrea (solo desarrollo).
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS cierre_caja, movimiento_caja, solicitud_efectivo, turno_caja, caja, banca;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE banca (
  id_banca INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  nombre   VARCHAR(80) NOT NULL,
  estado   ENUM('activa','inactiva') NOT NULL DEFAULT 'activa'
);

CREATE TABLE caja (
  id_caja INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  id_banca INT UNSIGNED NOT NULL UNIQUE,
  saldo_actual DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  limite_diferencia DECIMAL(12,2) NOT NULL DEFAULT 100.00,     -- [Propuesta] D-09
  fecha_actualizacion DATETIME NOT NULL,
  CONSTRAINT chk_saldo CHECK (saldo_actual >= 0),
  FOREIGN KEY (id_banca) REFERENCES banca(id_banca)
);

CREATE TABLE turno_caja (
  id_turno INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  id_caja INT UNSIGNED NOT NULL,
  id_usuario_saliente INT UNSIGNED NULL,                        -- NULL = primer turno
  id_usuario_entrante INT UNSIGNED NOT NULL,
  hora_inicio DATETIME NULL,                                    -- al activarse
  hora_fin DATETIME NULL,                                       -- al cerrar caja
  confirmado_saliente TINYINT(1) NOT NULL DEFAULT 0,
  confirmado_entrante TINYINT(1) NOT NULL DEFAULT 0,
  estado ENUM('pendiente','activo','cerrado') NOT NULL,         -- [Propuesta]
  fondo_inicial DECIMAL(12,2) NULL,                             -- [Propuesta] saldo al activarse
  fecha_solicitud DATETIME NOT NULL,                            -- [Propuesta]
  INDEX (id_caja, estado),
  FOREIGN KEY (id_caja) REFERENCES caja(id_caja)
);

CREATE TABLE solicitud_efectivo (
  id_solicitud_efectivo INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  id_caja INT UNSIGNED NOT NULL,
  tipo ENUM('entrega','retiro') NOT NULL,
  importe DECIMAL(12,2) NOT NULL,
  motivo VARCHAR(255) NULL,                                     -- [Propuesta]
  id_usuario_inicia INT UNSIGNED NOT NULL,
  id_usuario_confirma INT UNSIGNED NULL,
  estado ENUM('pendiente','confirmada','rechazada') NOT NULL DEFAULT 'pendiente',
  fecha_inicio DATETIME NOT NULL,
  fecha_confirmacion DATETIME NULL,
  motivo_rechazo VARCHAR(255) NULL,                             -- [Propuesta]
  CONSTRAINT chk_sol_importe CHECK (importe > 0),
  FOREIGN KEY (id_caja) REFERENCES caja(id_caja)
);

CREATE TABLE movimiento_caja (
  id_movimiento BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  id_caja INT UNSIGNED NOT NULL,
  id_turno INT UNSIGNED NOT NULL,
  tipo_movimiento ENUM('venta','pago','anulacion','entrega','retiro','intento_pago_rechazado') NOT NULL,
  efecto ENUM('entrada','salida','ninguno') NOT NULL,
  importe DECIMAL(12,2) NOT NULL,
  id_jugada INT UNSIGNED NULL,                                  -- ref. M2 (sin FK real)
  id_operacion INT UNSIGNED NULL UNIQUE,                        -- ref. M4; una operación = un movimiento
  id_solicitud_efectivo INT UNSIGNED NULL,                      -- [Propuesta]
  id_movimiento_relacionado BIGINT UNSIGNED NULL,               -- reverso → venta original
  id_usuario INT UNSIGNED NOT NULL,
  estado ENUM('confirmado','informativo') NOT NULL,
  saldo_resultante DECIMAL(12,2) NOT NULL,
  observacion VARCHAR(255) NULL,
  clave_idempotencia VARCHAR(80) NULL UNIQUE,                   -- [Propuesta] ver §9.1
  fecha_hora DATETIME NOT NULL,
  CONSTRAINT chk_importe CHECK (importe > 0),
  CONSTRAINT chk_info CHECK ((estado='informativo') = (efecto='ninguno')),
  INDEX idx_fecha (id_caja, fecha_hora),
  INDEX idx_jugada (id_jugada),
  INDEX idx_turno (id_turno),
  FOREIGN KEY (id_caja)  REFERENCES caja(id_caja),
  FOREIGN KEY (id_turno) REFERENCES turno_caja(id_turno),
  FOREIGN KEY (id_movimiento_relacionado) REFERENCES movimiento_caja(id_movimiento)
);

CREATE TABLE cierre_caja (
  id_cierre INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  id_caja INT UNSIGNED NOT NULL,
  id_turno INT UNSIGNED NOT NULL,
  fondo_inicial DECIMAL(12,2) NOT NULL,
  total_entradas DECIMAL(12,2) NOT NULL,
  total_salidas DECIMAL(12,2) NOT NULL,
  saldo_esperado DECIMAL(12,2) NOT NULL,
  efectivo_contado DECIMAL(12,2) NOT NULL,
  diferencia DECIMAL(12,2) NOT NULL,                            -- contado − esperado (con signo)
  saldo_conciliado DECIMAL(12,2) NULL,                          -- [Propuesta] D-13
  justificacion VARCHAR(500) NULL,
  id_usuario INT UNSIGNED NOT NULL,
  requiere_autorizacion TINYINT(1) NOT NULL DEFAULT 0,
  id_administrador_autoriza INT UNSIGNED NULL,
  estado ENUM('pendiente_autorizacion','cerrado') NOT NULL,     -- [Propuesta]
  fecha_hora DATETIME NOT NULL,
  fecha_autorizacion DATETIME NULL,                             -- [Propuesta]
  UNIQUE (id_turno),
  FOREIGN KEY (id_caja) REFERENCES caja(id_caja),
  FOREIGN KEY (id_turno) REFERENCES turno_caja(id_turno)
);
