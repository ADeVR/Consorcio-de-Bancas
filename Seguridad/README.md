 Integración del módulo de seguridad

El proyecto utiliza un único módulo de seguridad compartido. Cada integrante debe utilizarlo para proteger las páginas PHP de su módulo, sin crear otro login ni implementar un sistema de permisos independiente.

 1. Incluir los archivos comunes

Al principio de cada página PHP protegida, se deben incluir `conexion.php` y `funciones.php` utilizando las rutas relativas correctas.

Ejemplo para una página ubicada directamente dentro de una carpeta de módulo, si la estructura confirma que esa ruta relativa es correcta:


<?php
require_once __DIR__ . "/../Seguridad/php/conexion.php";
require_once __DIR__ . "/../Seguridad/php/funciones.php";


La ruta debe ajustarse según la ubicación real del archivo PHP.

2. Comprobar el permiso requerido

Después de incluir los archivos comunes, se debe comprobar el permiso correspondiente:

- Configuración: `exigirPermisoModulo("Configuracion");`
- Apertura: `exigirPermisoModulo("Apertura");`
- Jugar: `exigirPermisoModulo("Jugar");`
- Cierre: `exigirPermisoModulo("Cierre");`
- Pagos: `exigirPermisoModulo("Pagos");`

Por ejemplo, una página protegida del módulo Apertura debe comenzar así, adaptando las rutas a su ubicación real:


<?php
require_once __DIR__ . "/../Seguridad/php/conexion.php";
require_once __DIR__ . "/../Seguridad/php/funciones.php";

exigirPermisoModulo("Apertura");

// A partir de aquí comienza el código del módulo.


3. Proteger todas las operaciones

La comprobación no debe colocarse únicamente en la página principal del módulo. También debe aplicarse a las páginas que permitan consultar, registrar, modificar, eliminar o procesar información.

Ocultar un enlace o botón no constituye una protección suficiente.

 4. Usar los permisos asignados desde el panel

El administrador asigna los permisos desde la pantalla de administración de usuarios. Cada módulo debe utilizar exactamente el nombre definido para su permiso.

No se deben crear roles o permisos paralelos sin coordinación con el equipo responsable de seguridad.

 5. No modificar la autenticación compartida

Los módulos deben reutilizar la sesión y la autenticación existentes. No deben crear un login propio ni almacenar contraseñas de MySQL en sus archivos.

6. Comprobar el funcionamiento

Cada integrante debe verificar que:

- Un usuario sin sesión no pueda acceder al módulo.
- Un usuario autorizado pueda utilizarlo.
- Un usuario sin permiso reciba una respuesta de acceso denegado.
- El administrador conserve el acceso previsto por el sistema.
