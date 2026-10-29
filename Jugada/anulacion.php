<?php require __DIR__ . '/includes/header.php'; ?>

<div class="card shadow">
    <div class="card-header bg-light">
        <h4 class="mb-0">Solicitar Anulación</h4>
    </div>
    <div class="card-body">

        <div id="mensaje"></div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label" for="codigo">Código del ticket</label>
                <input type="text" id="codigo" class="form-control" placeholder="TK00000001">
            </div>
            <div class="col-md-8 mb-3">
                <label class="form-label" for="motivo">Motivo</label>
                <textarea id="motivo" class="form-control" rows="2" maxlength="200"></textarea>
                <div class="form-text"><span id="contador">0</span>/200 caracteres</div>
            </div>
        </div>

        <button type="button" class="btn btn-secondary" id="btnEnviar">Enviar solicitud</button>
        <a href="index.php" class="btn btn-secondary ms-2">Volver al Menú</a>
    </div>
</div>

<div class="card shadow mt-4">
    <div class="card-header bg-light">
        <h5 class="mb-0">Solicitudes</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Ticket</th><th>Agente</th><th>Motivo</th><th>Fecha</th><th>Estado</th>
                    </tr>
                </thead>
                <tbody id="listaSolicitudes"></tbody>
            </table>
        </div>
    </div>
</div>

<script src="js/anulacion.js"></script>

<?php require __DIR__ . '/includes/footer.php'; ?>