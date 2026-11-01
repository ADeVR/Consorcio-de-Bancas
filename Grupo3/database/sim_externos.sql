-- Tablas sim_* (simulan módulos M1, M2, M4, M5 y catálogo). Se eliminan al conectar los módulos reales.
DROP TABLE IF EXISTS sim_auditoria, sim_operacion, sim_jugada_detalle, sim_jugada, sim_sorteo, sim_loteria, sim_usuario;

CREATE TABLE sim_usuario (
  id_usuario INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  nombre VARCHAR(80) NOT NULL, usuario VARCHAR(40) NOT NULL UNIQUE,
  rol ENUM('superadmin','admin','agente') NOT NULL,
  estado ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  fecha_creacion DATETIME NOT NULL
);
CREATE TABLE sim_loteria (
  id_loteria INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  nombre VARCHAR(60) NOT NULL, estado ENUM('activa','inactiva') NOT NULL DEFAULT 'activa'
);
CREATE TABLE sim_sorteo (
  id_sorteo INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  id_loteria INT UNSIGNED NOT NULL, nombre VARCHAR(60) NOT NULL, hora_sorteo TIME NOT NULL,
  FOREIGN KEY (id_loteria) REFERENCES sim_loteria(id_loteria)
);
CREATE TABLE sim_jugada (
  id_jugada INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  ticket VARCHAR(30) NOT NULL UNIQUE, id_agente INT UNSIGNED NOT NULL, cliente_nombre VARCHAR(80) NULL,
  fecha_hora_registro DATETIME NOT NULL,
  estado ENUM('confirmada','anulacion_solicitada','anulada','ganadora','perdedora','pagada') NOT NULL,
  total_cobrar DECIMAL(12,2) NOT NULL
);
CREATE TABLE sim_jugada_detalle (
  id_detalle INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  id_jugada INT UNSIGNED NOT NULL, id_sorteo INT UNSIGNED NOT NULL,
  numeros_jugados VARCHAR(40) NOT NULL, monto_apostado DECIMAL(12,2) NOT NULL, posible_pago DECIMAL(12,2) NULL,
  FOREIGN KEY (id_jugada) REFERENCES sim_jugada(id_jugada),
  FOREIGN KEY (id_sorteo) REFERENCES sim_sorteo(id_sorteo)
);
CREATE TABLE sim_operacion (
  id_operacion INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  tipo ENUM('cobro','pago','anulacion') NOT NULL, id_jugada INT UNSIGNED NOT NULL,
  monto DECIMAL(12,2) NOT NULL, id_usuario INT UNSIGNED NOT NULL,
  decision ENUM('aprobada','rechazada') NULL, fecha_hora DATETIME NOT NULL
);
CREATE TABLE sim_auditoria (
  id_auditoria BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  fecha_hora DATETIME NOT NULL, id_usuario INT UNSIGNED NULL, accion VARCHAR(60) NOT NULL,
  entidad VARCHAR(40) NULL, id_registro VARCHAR(40) NULL, detalle TEXT NULL
);
