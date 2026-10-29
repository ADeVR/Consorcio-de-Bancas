<?php
declare(strict_types=1);
// Un puerto por módulo de otro grupo. 'simulado' = adaptador de prueba (lo elige fuente_datos en app.php).
// Al conectar el módulo real de otro grupo: poner 'real' y crear app/Integracion/Adaptadores/Real/<Puerto>Real.php.
return [
    'identidad'   => 'real',   // M1
    'jugada'      => 'simulado',   // M2
    'operaciones' => 'simulado',   // M4
    'ventas_base' => 'simulado',   // M5
    'auditoria'   => 'simulado',   // M5
    'hora'        => 'simulado',   // M5
    'catalogo'    => 'simulado',   // por reasignar (D-18)
    'tablas_externas' => [         // nombres reales cuando se conecte M5
        'usuario'        => 'sim_usuario',
        'jugada'         => 'sim_jugada',
        'jugada_detalle' => 'sim_jugada_detalle',
        'operacion'      => 'sim_operacion',
        'loteria'        => 'sim_loteria',
        'sorteo'         => 'sim_sorteo',
        'auditoria'      => 'sim_auditoria',
    ],
];
