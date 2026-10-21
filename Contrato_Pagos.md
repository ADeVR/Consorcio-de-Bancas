# Contrato de datos — Pagos

## Objetivo

El módulo de Pagos es responsable de mostrar la lista de boletos ganadores, calcular el premio de cada uno y registrar su pago, generando el comprobante correspondiente.

Está pensado para el banquero: debe ser rápido, con un solo clic por pago.

## Datos que recibe

### De Números Ganadores: lista de ganadores

Números Ganadores entrega la lista de boletos ganadores de los sorteos ya cerrados. Si un boleto no aparece en la lista, no ganó.

| Campo | Descripción |
|---|---|
| numeroBoleto | Número o código único del boleto. |
| loteriaId | Identificador de la lotería. |
| sorteoId | Identificador del sorteo. |
| modalidadId | Identificador de la modalidad jugada (Quiniela, Palé, Súper Palé, Tripleta). |
| jugada | Números jugados, por ejemplo `15-28-36`. |
| montoJugado | Cantidad de pesos apostados en el boleto. |
| posicion | Posición o categoría del premio obtenido. |

### De Configuración: pagos por modalidad

| Campo | Descripción |
|---|---|
| modalidadId | Identificador de la modalidad. |
| posicion | Posición o categoría del premio. |
| pagoPorPeso | Pago configurado por cada peso apostado. |

### De Seguridad

Los permisos que definen qué usuarios pueden entrar a la pantalla de Pagos.

## Cálculo del premio

```
premio = montoJugado × pagoPorPeso
```

El `pagoPorPeso` se toma según la modalidad y la posición del premio. Cada modalidad tiene sus propias reglas.

## Datos que genera y guarda

Cada pago registrado queda en la base de datos con los siguientes campos:

| Campo | Descripción |
|---|---|
| id | Identificador único del pago. |
| numeroBoleto | Boleto que se pagó. |
| premio | Monto pagado al cliente. |
| tipoPago | `efectivo` o `transferencia`. |
| cuentaDestino | Cuenta de destino, solo si el pago es por transferencia. |
| estado | `pendiente` o `pagado`. |
| fechaPago | Fecha en que se realizó el pago. |
| horaPago | Hora en que se realizó el pago. |
| usuarioId | Usuario que registró el pago. |

Todo ganador de la lista nace con estado `pendiente`. Al presionar Pagar pasa a `pagado`.

## Relación entre módulos

- **Números Ganadores:** entrega la lista de ganadores.
- **Caja y Reportes:** podrá consultar los pagos registrados para reflejar los premios pagados como salida en el cuadre de caja del día (por confirmar con ese grupo).
- **Configuración:** Pagos consulta las modalidades y los pagos configurados para calcular los premios.
- **Jugadas:** Pagos no recibe datos directamente de este módulo. El monto jugado de cada boleto ya viene en la lista que entrega Números Ganadores.
- **Seguridad:** controla quién puede entrar a la pantalla de Pagos.

## Reglas generales

- Todo pago tiene un identificador único.
- Un boleto pagado no puede volver a pagarse.
- Todo pago se registra obligatoriamente, para mantener el historial de premios entregados.
- El premio se calcula según la modalidad de la jugada.
- Solo se muestran ganadores de sorteos ya cerrados, porque así los entrega Números Ganadores.
- El pago se refleja como salida en el cuadre de caja del día.
- Se genera un comprobante por cada pago, con boleto, premio, tipo de pago, estado, fecha y hora.
- Marcar un premio como entregado no es lo mismo que anular una jugada. Son procesos distintos.

## Consideraciones

Este contrato define los datos que intercambian los módulos. No implementa por sí mismo el almacenamiento, el control de acceso ni el cálculo automático de resultados.
