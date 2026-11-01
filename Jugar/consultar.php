<?php require __DIR__ . '/includes/header.php'; ?>

<div class="card shadow">
    <div class="card-header bg-light">
        <h4 class="mb-0">Consultar Jugadas</h4>
    </div>
    <div class="card-body">

        <div class="row g-3 align-items-end mb-3">
            <div class="col-md-4">
                <label class="form-label" for="fecha">Fecha</label>
                <input type="date" id="fecha" class="form-control" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="codigo">Código de ticket</label>
                <input type="text" id="codigo" class="form-control" placeholder="TK0000...">
            </div>
            <div class="col-md-4">
                <button type="button" class="btn btn-secondary" id="btnBuscar">Buscar</button>
                <a href="index.php" class="btn btn-secondary ms-2">Volver al Menú</a>
            </div>
        </div>

        <div id="mensaje"></div>
        <p class="text-muted" id="resumen"></p>

        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Código</th><th>Hora</th><th>Agente</th>
                        <th>Total</th><th>Estado</th><th></th>
                    </tr>
                </thead>
                <tbody id="listaTickets"></tbody>
            </table>
        </div>
    </div>
</div>

<script src="js/consultar.js"></script>

<?php require __DIR__ . '/includes/footer.php'; ?>