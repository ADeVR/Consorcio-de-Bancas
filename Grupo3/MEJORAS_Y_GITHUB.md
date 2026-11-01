# Caja y Reportes (antes «Apertura») — mejoras incrementales

## Alcance
Este aporte mantiene PHP, HTML, CSS, JS/jQuery, Bootstrap, SQL y XML, los mismos directorios y contratos existentes. **No está terminado ni integrado con los demás módulos**. Las páginas siguen usando datos simulados por defecto.

## Cambios incluidos
- API de reporte: validación de fechas (incluido rango), ticket, tipo, estado y parámetros numéricos antes de consultar.
- API de reporte: se rechaza explícitamente el filtro de lotería mientras su integración real esté pendiente, en vez de ofrecer resultados engañosos.
- Interfaz: selector de loterías deshabilitado cuando no hay catálogo; se limpian los totales y controles de paginación al recibir un reporte vacío.
- Interfaz: controles de paginación protegidos ante clics en límites.
- Exportación: cabeceras y columnas originales preservadas; CSV protege texto que pueda interpretarse como fórmula en hojas de cálculo; se revoca la URL temporal tras descargar.
- Git: `.gitignore` para evitar publicar configuración de BD, logs y copias de datos.

## Pendiente, no implementado
- Conexión a la base de datos compartida en Hostinger. **phpMyAdmin no es el host de MySQL**; las credenciales y el acceso remoto los proporciona el administrador.
- La tabla real `usuarios` observada en phpMyAdmin usa `id`, mientras que `config/integracion.php` todavía apunta a `sim_usuario` y `MovimientoRepo.php` espera `id_usuario`. Hace falta acuerdo de integración y mapeo; no se alteró esa lógica.
- Integración de loterías, cierre, relevo, apertura, autorización, movimientos y exportación PDF real. Imprimir permite generar PDF desde el navegador, no es una API PDF.
- `PLAN_MAESTRO.md` citado en el README original no estaba en el ZIP.

## Seguridad de la base compartida
**NO importar `database/schema.sql` en Hostinger**: contiene `DROP TABLE` y podría borrar tablas si coinciden sus nombres. No ejecutar `seed_demo.sql` o `sim_externos.sql` en la BD compartida sin autorización. Pruebas primero en modo simulado.

## Ejecución local sin conectar a Hostinger
1. Abrir la carpeta en Visual Studio/VS Code.
2. Mantener `config/app.php` con `fuente_datos = 'simulado'`.
3. Desde la carpeta raíz ejecutar `php -S 127.0.0.1:8000 -t .` con PHP instalado, o servir la carpeta con Apache configurado solo para localhost.
4. En el servidor PHP integrado, el `base_url` predeterminado `/caja-reportes/public` solo funciona si la carpeta se sirve desde su carpeta padre con esa ruta. Ejemplo: abrir una terminal en el directorio **padre** de `caja-reportes`, ejecutar `php -S 127.0.0.1:8000`, y visitar `http://127.0.0.1:8000/caja-reportes/public/`.
5. No publicar el servidor de pruebas en Internet.

## GitHub (desde la carpeta raíz)
```bash
git init
git switch -c mejora-reportes
git add .
git status
git commit -m "Mejorar validacion y experiencia de reportes de caja"
```
Si el proyecto ya usa Git, **no ejecutar `git init`** y trabajar en una rama desde la versión compartida. Revisar con `git status` que no aparezcan credenciales, dumps ni datos personales antes de publicar. Configurar `git remote` solo con el repositorio autorizado del equipo.

## Pruebas manuales sugeridas
1. Reporte normal: filtro por fecha, paginación y exportación.
2. Filtro `desde > hasta`: error visible sin borrar valores.
3. `api/reporte/movimientos.php?desde=2026-02-30`: error de validación.
4. Rango sin filas: estado vacío, sin totales ni paginación antiguos.
5. Sin catálogo de loterías: filtro deshabilitado.
6. Verificar que los archivos de credenciales no se incluyan en `git status`.

## Responsabilidad y autoría
Estas son mejoras incrementales al proyecto original del equipo. Mantener historial de commits para distinguir el trabajo existente de las modificaciones.
