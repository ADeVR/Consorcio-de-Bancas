# Grupo3 — Caja y Reportes (anteriormente Apertura)

## Qué se integró

- Se conservó el módulo PHP y las pantallas del ZIP rediseñado dentro de `Grupo3/`.
- Se configuró `base_url` en `/ConsorcioDeBancas/Grupo3/public`.
- Se creó un adaptador `IdentidadReal` para **reutilizar** la sesión `usuario_id` de Seguridad y la tabla `usuario_permisos` con el nombre **Apertura**. No se agrega otro login.
- Se cambió únicamente el enlace de **Apertura** en `Seguridad/php/inicio.php` para que abra el módulo de `Grupo3` con el nombre **Caja y Reportes**.
- Se conserva `fuente_datos = simulado` para las demostraciones: **los saldos mostrados no son datos de Hostinger**.

## Instalación y ruta

Este repositorio usa enlaces absolutos que comienzan con `/ConsorcioDeBancas`. Copia la raíz del proyecto a `C:\xampp\htdocs\ConsorcioDeBancas\` (o configura un alias equivalente).

1. Inicia Apache en XAMPP, sin abrir el servicio a redes externas.
2. Configura las variables `DB_HOST`, `DB_NAME`, `DB_USER` y `DB_PASSWORD` para la conexión que usa `Seguridad/php/conexion.php`. **Nunca las subas a GitHub.**
3. Abre `http://localhost/ConsorcioDeBancas/Seguridad/php/login.php`, accede con un usuario autorizado y pulsa **Caja y Reportes** en el panel.
4. Puedes abrir también `http://localhost/ConsorcioDeBancas/Grupo3/pantallas/index.html` sin PHP ni acceso a la base de datos, solo como maqueta visual.

## Límites importantes

- Aunque el inicio de sesión y el permiso **Apertura** se consultan desde la BD compartida, el estado de caja y reportes aún usan **datos simulados**, no transacciones persistidas en MySQL.
- Los ID de usuario, banca y movimientos de las simulaciones no deben confundirse con registros oficiales.
- `usuarios` no contiene una relación usuario-banca. Hasta acordarla, `idBancaDe()` devuelve una banca temporal; **NO usar para transacciones monetarias reales o autorización multibanca**.
- `caja-reportes/database/schema.sql` contiene instrucciones destructivas. **NO ejecutarlo sobre la base compartida**. Revisar primero con los responsables del esquema.
- Se mantienen las otras integraciones como simuladas: Pagos, Jugadas, loterías y auditoría; sus contratos deben acordarse antes de permitir escrituras financieras.
- La autenticación de Seguridad requiere conexión MySQL y permisos reales; no uses el selector de usuario simulado para suplantar sesiones.
- Las rutas del repositorio `Seguridad` son absolutas y su estructura no se ha reformado; la carpeta instalada debe llamarse `ConsorcioDeBancas` para que sus enlaces funcionen.

## Seguridad / revisión previa a producción

Antes de conectar escritura real: verificar mapeo de bancas, compatibilidad de tipos de operaciones, controles contra duplicados, transacciones y permisos por recurso. Ejecutar las pruebas del PDF de 26 páginas en una base **separada de pruebas**.

## GitHub

La única carpeta añadida con implementación de M3 es `Grupo3/`. Fuera de ella, se modificó solo el enlace de Seguridad en `Seguridad/php/inicio.php`. Revisa estos cambios con el grupo de Seguridad antes de integrar al repositorio principal.
