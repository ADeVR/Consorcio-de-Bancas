# Base de datos — Consorcio de Bancas

## 1. Propósito

Esta carpeta contendra los scripts SQL y la documentación relacionados con la estructura de la base de datos del sistema Consorcio de Bancas.

El proyecto utiliza una base de datos MySQL compartida mediante acceso remoto. 

## 2. Organización

- `esquema.sql`: estructura de las tablas principales del sistema.
- `permisos.sql`: creación de la tabla que almacena los permisos individuales de cada usuario.
- `cambios/`: documentación de modificaciones futuras a la estructura, si se utiliza esta carpeta.

Los nombres anteriores representan la organización recomendada. Deben coincidir con los archivos SQL que realmente existan en el repositorio.

## 3. Tabla de usuarios

La tabla `usuarios` almacena la información necesaria para la autenticación, incluyendo:

- Identificador del usuario.
- Nombre de usuario.
- Contraseña almacenada mediante un hash seguro.
- Rol (`admin` o `agente`).
- Estado de la cuenta.
- Fecha de creación, si está disponible en el esquema.

Las contraseñas no deben guardarse en texto plano.

## 4. Tabla de permisos

La tabla `usuario_permisos` relaciona cada usuario con los módulos a los que tiene acceso.

Los módulos definidos actualmente son:

- `Configuracion`
- `Apertura`
- `Jugar`
- `Cierre`
- `Pagos`

Cada permiso se identifica mediante el usuario y el nombre del módulo. La combinación debe ser única para evitar asignaciones duplicadas.

Los administradores tienen acceso a todos los módulos según las reglas de autorización de la aplicación. Los agentes solo deben acceder a los módulos que tengan autorizados.

## 5. Base de datos compartida

La base de datos utilizada actualmente se llama `u318940728_aderson`.

La conexión se configura en el entorno local o en el servidor, mediante variables de entorno. Las credenciales no deben incluirse en este repositorio.

Los scripts SQL deben utilizarse para crear o actualizar la estructura cuando sea necesario. No se deben importar nuevamente sobre la base de datos compartida sin revisar primero sus efectos.

## 6. Seguridad

- No subir contraseñas de MySQL al repositorio.
- No subir exportaciones con datos personales o credenciales.
- No modificar la estructura de producción sin respaldo y coordinación.
- Aplicar los cambios estructurales mediante scripts SQL revisados.
- Mantener la estructura documentada sincronizada con la base de datos real.

## 7. Responsabilidad del equipo

Cada integrante debe respetar las tablas existentes y coordinar cualquier cambio que afecte a otros módulos. Las modificaciones estructurales deben compartirse por Git para que todos conozcan los cambios realizados.
