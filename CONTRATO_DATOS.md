# Contrato de datos — Configuración de Loterías

## Objetivo

El módulo de Configuración de Loterías será responsable de definir las loterías, sus sorteos, horarios, modalidades, límites de apuestas y pagos por premio.

Los demás módulos utilizarán esta información para realizar sus respectivas funciones.

## Datos proporcionados

| Campo | Descripción |
|---|---|
| `id` | Identificador único de la lotería, sorteo o modalidad, según corresponda. |
| `nombre` | Nombre del registro. |
| `activa` / `activo` | Indica si la lotería o modalidad está habilitada. |
| `dia` | Día en que se realiza el sorteo. |
| `horaApertura` | Hora desde la que se permite recibir jugadas. |
| `horaCierre` | Hora límite para recibir jugadas. |
| `horaSorteo` | Hora programada del sorteo. |
| `cantidadNumeros` | Cantidad de números que requiere la modalidad. |
| `numeroMinimo` / `numeroMaximo` | Rango permitido para los números. |
| `montoMinimo` / `montoMaximo` | Límites de la apuesta. |
| `posicion` | Posición o categoría del premio. |
| `pagoPorPeso` | Pago configurado por cada peso apostado. |

## Relación entre módulos

- **Apertura y cierre:** utilizará los días, horarios y estados para determinar cuándo aceptar jugadas.
- **Jugadas :** utilizará la lotería, el sorteo, las modalidades, los rangos y los límites de apuesta.
- **Pagos:** consultará las modalidades y los pagos configurados para calcular los premios correspondientes.
- **X :** utilizará los identificadores y nombres de las loterías, sorteos y modalidades.

## Reglas generales

1. Cada registro debe tener un identificador único.
2. Un sorteo pertenece a una lotería.
3. Una modalidad pertenece a un sorteo.
4. Los horarios pueden variar según el día.
5. La configuración de premios debe corresponder a la modalidad aplicable.
6. Desactivar un registro no implica borrar el historial de operaciones.
7. El módulo de Configuración de Loterías estará disponible únicamente para los usuarios autorizados con el rol de administrador.
8. Los cambios de configuración deben coordinarse con los demás módulos para evitar inconsistencias con jugadas ya registradas.

## Consideraciones

Los horarios y pagos deben ser configurados con información verificada. Este contrato define los datos que intercambiarán los módulos, pero no implementa por sí mismo el almacenamiento, el control de acceso ni la apertura y el cierre automáticos.