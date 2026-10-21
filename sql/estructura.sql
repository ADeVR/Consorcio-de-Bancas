-- Ejecutar solo si la tabla usuarios aún no existe.
CREATE DATABASE IF NOT EXISTS consorcio_bancas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE consorcio_bancas;
CREATE TABLE IF NOT EXISTS usuarios (
 id INT UNSIGNED NOT NULL AUTO_INCREMENT,
 usuario VARCHAR(50) NOT NULL,
 clave VARCHAR(255) NOT NULL,
 rol ENUM('admin','agente') NOT NULL DEFAULT 'agente',
 activo TINYINT(1) NOT NULL DEFAULT 1,
 fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(id), UNIQUE KEY uq_usuarios_usuario(usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- No elimina datos ni crea una cuenta administrador. Conserva las cuentas existentes.
