-- =====================================================================
-- Modulo de Pagos - tabla propia: pagos
-- Base de datos: u318940728_aderson (compartida)
--
-- Depende de: usuarios (Seguridad), que ya existe.
-- Pendiente: la relacion con la tabla de ganadores (Numeros Ganadores)
-- =====================================================================

CREATE TABLE IF NOT EXISTS `pagos` (
  `id`            int(11)       NOT NULL AUTO_INCREMENT,
  `numeroBoleto`  varchar(20)   NOT NULL,
  `premio`        decimal(12,2) NOT NULL,
  `tipoPago`      enum('efectivo','transferencia') DEFAULT NULL,
  `cuentaDestino` varchar(30)   DEFAULT NULL,
  `estado`        enum('pendiente','pagado') NOT NULL DEFAULT 'pendiente',
  `fechaPago`     date          DEFAULT NULL,
  `horaPago`      time          DEFAULT NULL,
  `usuarioId`     int(11)       DEFAULT NULL,
  PRIMARY KEY (`id`),

  -- Un boleto solo puede tener un pago (evita el pago doble)
  UNIQUE KEY `uq_pagos_boleto` (`numeroBoleto`),
  KEY `idx_pagos_usuario` (`usuarioId`),

  -- Quien registro el pago (tabla real de Seguridad)
  CONSTRAINT `fk_pagos_usuario` FOREIGN KEY (`usuarioId`) REFERENCES `usuarios` (`id`),

  -- Un pago realizado debe tener todos sus datos
  CONSTRAINT `ck_pagado_completo` CHECK (
    `estado` = 'pendiente'
    OR (`tipoPago` IS NOT NULL AND `fechaPago` IS NOT NULL
        AND `horaPago` IS NOT NULL AND `usuarioId` IS NOT NULL)
  ),
  -- Una transferencia necesita cuenta de destino
  CONSTRAINT `ck_transferencia_cuenta` CHECK (
    `tipoPago` IS NULL OR `tipoPago` <> 'transferencia' OR `cuentaDestino` IS NOT NULL
  )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

