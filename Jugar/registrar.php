<?php require __DIR__ . '/includes/header.php'; ?>

<div class="card shadow">
    <div class="card-header bg-light">
        <h4 class="mb-0">Registrar Jugada</h4>
    </div>
    <div class="card-body">

        <div id="mensaje"></div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label" for="sorteo">Lotería / Sorteo</label>
                <select id="sorteo" class="form-select">
                    <option value="">Cargando...</option>
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label" for="juego">Tipo de jugada</label>
                <select id="juego" class="form-select" disabled>
                    <option value="">Seleccione un sorteo primero</option>
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">Número(s)</label>
                <div id="numeros" class="d-flex gap-2"></div>
                <div class="form-text" id="ayudaNumeros"></div>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label" for="monto">Monto (RD$)</label>
                <input type="number" id="monto" class="form-control" disabled>
                <div class="form-text" id="ayudaMonto"></div>
            </div>
        </div>

        <button type="button" class="btn btn-secondary" id="btnAgregar">Agregar jugada</button>
        <a href="index.php" class="btn btn-secondary ms-2">Volver al Menú</a>
    </div>
</div>

<div class="card shadow mt-4">
    <div class="card-header bg-light">
        <h5 class="mb-0">Jugadas del ticket</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                        <button type="button" class="btn btn-success" id="btnGuardar">Registrar ticket</button>
                <thead>
                    <tr>
                        <th>Sorteo</th><th>Tipo</th><th>Números</th><th>Monto</th><th></th>
                    </tr>
                </thead>
                <tbody id="listaJugadas"></tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-end">Total</th>
                        <th id="total">RD$ 0.00</th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<script src="js/registrar.js"></script>

<?php require __DIR__ . '/includes/footer.php'; ?>