<?php
/** Menú lateral por permisos (§14.2). Solo muestra pantallas que ya existen y a las que el usuario tiene acceso. */
declare(strict_types=1);

// [ruta relativa a public/, etiqueta, permisos (cualquiera), rol exclusivo|null, solo modo simulado]
$items = [
    ['index.php',                    'Inicio',                    [],                                              null,    false],
    ['caja/estado.php',              'Estado de caja',            ['caja.ver_saldo'],                              null,    false],
    ['reportes/movimientos.php',     'Reporte',                   ['reporte.consultar'],                           null,    false],
    ['reportes/totales_loteria.php', 'Totales por lotería',       ['reporte.consultar'],                           'admin', false],
    ['caja/pago_premio.php',         'Pagar premio',              ['pago.registrar'],                              null,    false],
    ['caja/efectivo.php',            'Entregas y retiros',        ['caja.iniciar_efectivo', 'caja.confirmar_efectivo'], null, false],
    ['caja/relevo.php',              'Relevo de turno',           ['caja.relevo'],                                 null,    false],
    ['caja/cierre.php',              'Cierre de caja',            ['caja.cerrar', 'caja.autorizar_cierre'],        null,    false],
    ['caja/parametros.php',          'Parámetros',                ['caja.autorizar_cierre'],                       null,    false],
    ['dev/simulador_m4.php',         'Simulador M4 (dev)',        [],                                              null,    true],
];
$raizPublic = dirname(__DIR__);
$actual     = $_SERVER['SCRIPT_NAME'] ?? '';
?>
<nav class="nav flex-column" aria-label="Navegación principal">
<?php foreach ($items as [$ruta, $etiqueta, $permisosItem, $rolItem, $soloSim]):
    if (!is_file($raizPublic . '/' . $ruta)) continue;
    if ($soloSim && !$esSimulado) continue;
    if ($permisosItem !== [] && ($usuario === null || array_intersect($permisosItem, $permisosUsuario) === [])) continue;
    if ($rolItem !== null && ($usuario === null || $usuario->rol !== $rolItem)) continue;
    $activo = str_ends_with($actual, '/' . $ruta);
?>
  <a class="nav-link <?= $activo ? 'active' : '' ?>" href="<?= $h($base . '/' . $ruta) ?>" <?= $activo ? 'aria-current="page"' : '' ?>><?= $h($etiqueta) ?></a>
<?php endforeach; ?>
</nav>
