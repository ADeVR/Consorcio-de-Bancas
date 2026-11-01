# Caja y Reportes (M3, anteriormente Apertura) — mejoras de interfaz y preparación

## Alcance de esta versión

Se conserva PHP, HTML, Bootstrap, CSS, JavaScript, jQuery, AJAX y SQL/MySQL. Se mantiene la arquitectura original y los contratos de integración. No se ejecutaron cambios en Hostinger.

### Cambios implementados
- Cabecera de módulo renovada con estado del entorno.
- Pantalla Estado de Caja con presentación jerarquizada de saldo, turno y pendientes.
- Reporte cronológico: cuatro indicadores derivados de los totales enviados por el servidor, filtros reordenados visualmente, ayuda de lectura y detalle de seis columnas.
- Corrección de los datos demostrativos: un intento rechazado se etiqueta como `intento_pago_rechazado`, no como pago confirmado.
- Mejora de responsividad y estilos para impresión.

### Limitaciones reales y dependencias pendientes
- El catálogo de loterías y las agregaciones por lotería dependen del contrato M2/M5 y de su integración.
- Aperturas, cierres autorizados, relevos con dos confirmaciones y pagos requieren flujos PHP y tablas aprobados por el grupo. No se anuncian como listos.
- La exportación disponible cubre la página visible; para exportar todas las páginas se requiere endpoint seguro con los filtros y permisos del servidor.
- El rol `encargado` descrito en la guía todavía no existe en el `ENUM` de la tabla remota `usuarios` mostrado en la captura. Debe acordarse con M1 antes de modificar permisos.
- La base de datos de Hostinger no debe importarse desde `database/schema.sql` sin revisión: el esquema puede contener instrucciones destructivas.

### GitHub
No subir `config/database.php`, contraseñas ni credenciales. Crear rama separada para este conjunto de cambios y revisar el diff antes de fusionar. El archivo `.gitignore` excluye la configuración de base local.

### Pruebas pendientes en equipo
Abrir el proyecto a través de Apache/PHP, confirmar permisos simulados, comprobar filtros y paginación, verificar que eventos informativos no afectan al saldo, contrastar exportaciones, ejecutar las pruebas financieras acordadas solo cuando exista un entorno de desarrollo aislado.

### Requisitos de referencia
`MODULO CAJA Y REPORTES.pdf` (guía de 26 páginas). Este documento es una propuesta académica; requiere acuerdos con los demás grupos antes de integrar datos reales.
