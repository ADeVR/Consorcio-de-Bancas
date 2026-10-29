<?php
/** Cierra <main>, carga los scripts y los de la página ($scripts). */
declare(strict_types=1);
?>
    </main>
  </div>
</div>

<div class="modal fade" id="modal-confirmar" tabindex="-1" aria-labelledby="modal-confirmar-titulo" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header"><h2 class="modal-title h5" id="modal-confirmar-titulo"></h2></div>
      <div class="modal-body" id="modal-confirmar-texto"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="modal-confirmar-ok">Confirmar</button>
      </div>
    </div>
  </div>
</div>

<script src="<?= $base ?>/assets/vendor/jquery/jquery.min.js"></script>
<script src="<?= $base ?>/assets/vendor/bootstrap/bootstrap.bundle.min.js"></script>
<script src="<?= $base ?>/assets/js/core/api.js"></script>
<script src="<?= $base ?>/assets/js/core/formato.js"></script>
<script src="<?= $base ?>/assets/js/core/ui.js"></script>
<script src="<?= $base ?>/assets/js/core/permisos.js"></script>
<script src="<?= $base ?>/assets/js/core/filas.js"></script>
<script src="<?= $base ?>/assets/js/core/exportar.js"></script>
<?php if (($app['fuente_datos'] ?? 'db') === 'mock'): ?>
<script src="<?= $base ?>/assets/js/mock/datos.js"></script>
<script src="<?= $base ?>/assets/js/mock/api_mock.js"></script>
<?php endif; ?>
<?php foreach ($scripts as $js): ?>
<script src="<?= $base ?>/assets/js/modulos/<?= $h($js) ?>"></script>
<?php endforeach; ?>
</body>
</html>
