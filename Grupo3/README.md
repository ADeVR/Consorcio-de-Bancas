

## Dónde está cada cosa
```text
caja-reportes/
├── README.md · PLAN_MAESTRO.md
├── config/      Ajustes: app.php, database, integracion.php, errores.xml, permisos.xml
├── database/    schema.sql (tablas) · sim_externos.sql (tablas de otros grupos, de prueba) · seed_demo.sql (datos)
├── app/         BACKEND PHP
│   ├── Core/          Piezas comunes: configuración, base de datos, sesión, permisos, dinero
│   ├── Modulos/
│   │   ├── Caja/      Estado de caja (saldo, turno activo)
│   │   └── Reportes/  Movimientos del reporte
│   └── Integracion/   Conexión con otros grupos: Puertos/ (contratos) · Adaptadores/ Mock, Simulado, Real
├── public/      LO QUE ABRE EL NAVEGADOR
│   ├── caja/ · reportes/        Páginas de cada módulo
│   ├── api/caja/ · api/reporte/ Datos en JSON de cada módulo
│   ├── includes/                Cabecera, menú y pie
│   └── assets/  css/ · vendor/ (Bootstrap, jQuery) · js/core (comunes) · js/modulos/<modulo> · js/mock (datos de prueba)
├── pantallas/   Pantallas HTML sin servidor (doble clic): index · estado · reporte
├── 
└── 
```

