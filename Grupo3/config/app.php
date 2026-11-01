<?php
declare(strict_types=1);
return [
    'modo'                   => 'simulado',            // simulado | real
    'fuente_datos'           => 'simulado',            // mock = datos en JS, sin servidor | simulado = PHP con datos de ejemplo | db = MySQL
    'base_url'               => '/ConsorcioDeBancas/Grupo3/public',
    'zona_horaria'           => 'America/Santo_Domingo',
    'id_banca_defecto'       => 1,
    'importe_maximo'         => '1000000.00',
    'por_pagina'             => 50,
    'hora_fija'              => null,                  // SOLO pruebas, p. ej. '2026-10-20 15:00:00'
    'sesion_inactividad_min' => 30,
];
