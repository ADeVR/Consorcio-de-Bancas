<?php
declare(strict_types=1);
require __DIR__ . '/../app/bootstrap.php';

use Caja\Core\Config;
use Caja\Core\Contenedor;


// Redirige a la pantalla inicial de Caja; sin usuario o sin acceso, el header explica qué hacer.
if (Config::get('integracion')['identidad'] === 'real' && empty($_SESSION['usuario_id'])) {
    header('Location: /ConsorcioDeBancas/Seguridad/php/login.php');
    exit;
}
$ident = Contenedor::puerto('identidad');
$u     = $ident->usuarioActual();
if ($u !== null && $ident->puede('caja.ver_saldo', $u->id_usuario)) {
    header('Location: ' . rtrim((string) Config::get('app')['base_url'], '/') . '/caja/estado.php');
    exit;
}

$titulo  = 'Inicio';
$permiso = 'caja.ver_saldo';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/footer.php';
