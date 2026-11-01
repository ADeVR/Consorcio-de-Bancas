<?php
declare(strict_types=1);
spl_autoload_register(function (string $clase): void {
    $prefijo = 'Caja\\';
    if (strncmp($clase, $prefijo, strlen($prefijo)) !== 0) return;
    $ruta = __DIR__ . '/' . str_replace('\\', '/', substr($clase, strlen($prefijo))) . '.php';
    if (is_file($ruta)) require $ruta;
});
