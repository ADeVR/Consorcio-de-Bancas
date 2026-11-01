<?php
/** Se incluye desde header.php cuando $errorAcceso tiene un código (SIN_PERMISO | SESION_EXPIRADA). */
declare(strict_types=1);

use Caja\Core\Xml;

?>
<div class="alert alert-danger" role="alert">
  <strong>Acceso denegado.</strong> <?= $h(Xml::mensajeDe($errorAcceso)) ?>
  <?php if ($errorAcceso === 'SESION_EXPIRADA' && $esSimulado): ?>
    <br>Elige un usuario en la barra superior.
  <?php endif; ?>
</div>
