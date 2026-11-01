# Contrato de datos — Pagos

Actualizado el 30 de octubre de 2026

## Objetivo

El módulo de Pagos muestra los tickets ganadores, calcula el premio de cada uno y registra su pago, generando el comprobante correspondiente.

Está pensado para el banquero: debe ser rápido, con un solo clic por pago.

## Datos que recibe

### De Números Ganadores: tabla `numero_ganador`

Indica qué líneas de jugada ganaron y con qué premio. No repite datos que ya guarda Jugadas.

| Campo | Tipo | Descripción |
|---|---|---|
| id | INT UNSIGNED, clave primaria | Identificador del ganador. |
| id_detalle | INT UNSIGNED, único | Línea de jugada que ganó. Apunta a `jugada_detalle.id_detalle`. |
| id_premio | INT UNSIGNED | Premio obtenido. Apunta a `premios_juego.id`. |
| fecha_sorteo | DATE | Fecha del sorteo. |

El premio elegido debe pertenecer al mismo juego que la línea (`premios_juego.juego_id = jugada_detalle.id_tipo`).

Esta tabla todavía no existe. Mientras tanto, Pagos usa una tabla temporal llamada `numero_ganador_prueba`, con la misma estructura.

### De Jugadas

| Tabla | Campos que usa Pagos |
|---|---|
| ticket | `id_ticket`, `codigo_ticket` (VARCHAR(20), único), `estado` (activo o anulado) |
| jugada_detalle | `id_detalle`, `id_ticket`, `id_tipo` (apunta a `juegos.id`), `monto`, `estado` |

Pagos solo lee estas tablas, con una excepción: al registrar un pago marca como `pagada` las líneas de jugada del ticket. Los tickets anulados no se pagan.

### De Configuración

Pagos solo lee: `premios_juego` (`id`, `juego_id`, `nombre_premio`, `valor_por_peso`), `juegos` y `sorteos`.

### De Seguridad

Pagos usa su conexión a la base de datos, su sistema de permisos, la sesión del usuario y su protección CSRF. El nombre de quien registra el pago se obtiene de `usuarios`.

## Cálculo del premio

```
premio del ticket = suma de (jugada_detalle.monto × premios_juego.valor_por_peso)
                    de sus líneas ganadoras
```

El sistema no aplica retenciones: el premio calculado es el monto que se paga. El cálculo lo hace siempre el servidor, nunca el navegador.

## Datos que genera y guarda

La tabla `pagos` guarda una fila por ticket, y se crea cuando se registra el pago. Un ticket ganador sin fila en `pagos` se considera pendiente.

| Campo | Tipo | Descripción |
|---|---|---|
| id | INT(11), clave primaria | Autoincremental. |
| numero_boleto | VARCHAR(20), único | Apunta a `ticket.codigo_ticket`. Solo un pago por ticket. |
| premio | DECIMAL(12,2) | Monto pagado. |
| tipo_pago | efectivo o transferencia | Lo elige el cajero. |
| cuenta_destino | VARCHAR(30) | Solo se llena si el pago es por transferencia. |
| estado | pendiente o pagado | Por defecto pendiente. |
| fecha_pago, hora_pago | DATE, TIME | Los pone el servidor, en hora de Santo Domingo. |
| usuario_id | INT(11), obligatorio | Apunta a `usuarios.id`. Quién registró el pago. |

Al registrar un pago, en la misma transacción las líneas del ticket pasan a `pagada` en `jugada_detalle`.

## Relación entre módulos

- **Números Ganadores:** entrega la tabla `numero_ganador`.
- **Jugadas:** de ahí salen el ticket y sus líneas. Pagos marca las líneas como `pagada`.
- **Configuración:** de ahí salen los pagos por peso de cada premio.
- **Seguridad:** controla quién puede entrar a Pagos y de quién es la sesión.
- **Caja y Reportes:** podrá consultar `pagos` para reflejar los premios pagados como salida de caja (por confirmar con ese grupo).

## Reglas generales

- Un ticket solo puede pagarse una vez: `numero_boleto` es único y el sistema avisa si ya fue pagado.
- Todo pago se registra con su fecha, su hora y el usuario que lo hizo.
- El usuario se toma de la sesión, nunca de un formulario.
- Si falla cualquier paso del pago, no queda un pago a medias (todo va en una transacción).
- Solo se pagan tickets activos.
- Se genera un comprobante por cada pago, con boleto, premio, tipo de pago, estado, fecha y hora.
- Marcar un premio como entregado no es lo mismo que anular una jugada. Son procesos distintos.
- Para crear relaciones entre tablas, los campos deben tener exactamente el mismo tipo, incluido el signo. Los que apuntan a Configuración o Jugadas son `INT UNSIGNED`. Los que apuntan a `usuarios` son `INT(11)` con signo.

## Pendientes de otros módulos

- **Números Ganadores:** crear la tabla `numero_ganador` con la estructura de este contrato. Cuando exista, Pagos cambia el nombre de la tabla temporal en sus archivos PHP.
- **Jugadas:** confirmar que `ticket.estado` siga siendo activo o anulado y que las líneas puedan quedar en estado `pagada`. No cambiar `id_detalle` ni `codigo_ticket` sin avisar.
- **Caja y Reportes:** confirmar si leerán `pagos` para el cuadre de caja.

## Consideraciones

Este contrato define los datos que intercambian los módulos. No implementa por sí mismo el almacenamiento, el control de acceso ni el cálculo de resultados.

Mientras Números Ganadores no entregue su tabla, hay datos de prueba en la base compartida (un ticket de prueba y las filas de apoyo en Configuración y Jugadas). Son solo para probar y se borran antes de usar datos reales.
