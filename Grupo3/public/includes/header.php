<?php
/**
 * Cabecera común. La página define antes: $titulo (string), $permiso (?string), $scripts (array de rutas bajo assets/js/modulos/, p. ej. 'caja/estado.js').
 * Deja abierto <main>. Si el acceso falla, muestra «acceso denegado» y termina.
 */
declare(strict_types=1);

use Caja\Core\CajaException;
use Caja\Core\Config;
use Caja\Core\Contenedor;
use Caja\Core\Csrf;
use Caja\Core\PermisoGuard;
use Caja\Core\Xml;


$app         = Config::get('app');
$base        = rtrim((string) $app['base_url'], '/');
$titulo      = $titulo ?? 'Caja y Reportes';
$permiso     = $permiso ?? null;
$scripts     = $scripts ?? [];
$ident       = Contenedor::puerto('identidad');
$usuario     = $ident->usuarioActual();
$errorAcceso = null;
$h           = fn(string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');

if ($permiso !== null) {
    try { $usuario = PermisoGuard::exigir($permiso); }
    catch (CajaException $e) { $errorAcceso = $e->codigo; }
}

$permisosUsuario = [];
if ($usuario !== null) {
    foreach (array_keys(Xml::permisos()) as $p) if ($ident->puede($p, $usuario->id_usuario)) $permisosUsuario[] = $p;
}
$esSimulado = $app['modo'] === 'simulado' && in_array(Config::get('integracion')['identidad'], ['simulado', 'mock'], true);
$etiquetaRol = ['superadmin' => 'Superadministrador', 'admin' => 'Administrador', 'agente' => 'Agente'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf" content="<?= $h(Csrf::token()) ?>">
  <title><?= $h($titulo) ?> · Caja y Reportes</title>
  <link rel="stylesheet" href="<?= $base ?>/assets/vendor/bootstrap/bootstrap.min.css">
  <link rel="stylesheet" href="<?= $base ?>/assets/css/tokens.css">
  <link rel="stylesheet" href="<?= $base ?>/assets/css/caja.css">
  <link rel="stylesheet" href="<?= $base ?>/assets/css/print.css" media="print">
</head>
<body data-base="<?= $h($base) ?>" data-fuente="<?= $h((string) ($app['fuente_datos'] ?? 'db')) ?>" data-id-usuario="<?= $usuario?->id_usuario ?? 0 ?>" data-permisos="<?= $h(implode(',', $permisosUsuario)) ?>" data-rol="<?= $h($usuario?->rol ?? '') ?>">
<a class="salto no-imprimir" href="#contenido-principal">Saltar al contenido</a>
<nav class="navbar navbar-expand navbar-caja px-3 no-imprimir">
  <span class="navbar-brand mb-0 h1">Consorcio de Banca · Caja y Reportes</span>
  <div class="ms-auto d-flex align-items-center gap-3">
    <?php if ($esSimulado): ?>
      <label class="d-flex align-items-center gap-2 mb-0 small">
        <span>Usuario simulado:</span>
        <select id="selector-usuario" class="form-select form-select-sm w-auto" aria-label="Usuario simulado">
          <?php if ($usuario === null): ?><option value="" selected disabled>Elegir usuario…</option><?php endif; ?>
          <?php foreach ($ident->usuariosSimulados() as $u): ?>
            <option value="<?= $u['id_usuario'] ?>" <?= $usuario !== null && $usuario->id_usuario === $u['id_usuario'] ? 'selected' : '' ?>>
              <?= $h($u['usuario']) ?> (<?= $h($etiquetaRol[$u['rol']] ?? $u['rol']) ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </label>
    <?php endif; ?>
    <?php if ($usuario !== null): ?>
      <span class="small"><?= $h($usuario->nombre) ?> · <?= $h($etiquetaRol[$usuario->rol] ?? $usuario->rol) ?></span>
    <?php endif; ?>
  </div>
</nav>
<div class="container-fluid">
  <div class="row">
    <aside class="col-md-2 col-lg-2 py-3 sidebar-caja no-imprimir"><?php require __DIR__ . '/navegacion.php'; ?></aside>
    <main id="contenido-principal" class="col-md-10 col-lg-10 py-3 px-4">
      <div class="page-heading"><div><span class="eyebrow">MÓDULO M3 <span class="eyebrow-dot">●</span> CAJA Y REPORTES</span><h1><?= $h($titulo) ?></h1><p class="page-subtitle">Control del efectivo, trazabilidad y operaciones por banca.</p></div><div class="page-heading-status"><span class="status-dot"></span><?= $esSimulado ? "Entorno de demostración" : "Entorno configurado" ?></div></div>
      <div id="alertas" aria-live="polite"></div>
<?php
if ($errorAcceso !== null) {
    require __DIR__ . '/acceso_denegado.php';
    require __DIR__ . '/footer.php';
    exit;
}
