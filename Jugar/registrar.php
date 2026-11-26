
<?php require __DIR__ . '/includes/header.php'; ?>

<style>
.main-layout {
    width: 100%;
    transition: width .3s ease, margin-right .3s ease;
}

/* RIGHT PANEL */
.side-panel {
    position: fixed;
    top: 0;
    right: 0;
    width: 60%;
    max-width: 800px;
    height: 100vh;
    background: #fff;
    z-index: 1051;
    box-shadow: -5px 0 20px rgba(0,0,0,.15);
    transform: translateX(105%);
    transition: transform .3s ease;
    overflow-y: auto;
    visibility: hidden;
}

.side-panel.open {
    transform: translateX(0);
    visibility: visible;
}

.panel-header {
    position: sticky;
    top: 0;
    background: white;
    z-index: 2;
    padding: 15px 20px;
    border-bottom: 1px solid #ddd;
}

.panel-content {
    padding: 20px;
}

.panel-tab {
    border: 1px solid #ced4da;
    background: white;
    padding: 10px 16px;
    border-radius: 8px;
    margin-right: 5px;
}

.panel-tab.active {
    background: #285da8;
    color: white;
    border-color: #285da8;
}

/* DESKTOP: make room for the panel */
@media (min-width: 769px) {
    body.panel-open .main-layout {
        width: 38%;
    }
}

/* MOBILE */
@media (max-width: 768px) {
    .side-panel {
        width: 100%;
        max-width: 100%;
    }

    body.panel-open .main-layout {
        visibility: hidden;
    }
}
</style>

<div class="main-layout">

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h4>Registrar Jugada</h4>

        <div>
            <button type="button"
                    class="btn btn-outline-primary"
                    onclick="abrirPanel('consultar')">
                Consultar jugadas
            </button>

            <button type="button"
                    class="btn btn-primary"
                    onclick="abrirPanel('anulaciones')">
                Anulaciones
            </button>
        </div>
    </div>

    <!-- REGISTRAR -->
    <div class="card shadow">
        <div class="card-header bg-light">
            <h5 class="mb-0">Registrar jugada</h5>
        </div>

        <div class="card-body">

            <div id="mensajeRegistro"></div>

            <div class="row">

                <div class="col-md-6 mb-3">
                    <label class="form-label" for="sorteo">
                        Lotería / Sorteo
                    </label>
                    <select id="sorteo" class="form-select">
                        <option value="">Cargando...</option>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label" for="juego">
                        Tipo de jugada
                    </label>
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
                    <label class="form-label" for="monto">
                        Monto (RD$)
                    </label>
                    <input type="number" id="monto"
                           class="form-control" disabled>
                    <div class="form-text" id="ayudaMonto"></div>
                </div>

            </div>

            <button type="button" class="btn btn-primary"
                    id="btnAgregar">
                + Agregar al ticket
            </button>

        </div>
    </div>

    <!-- LISTA DE JUGADAS -->
    <div class="card shadow mt-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">Jugadas del ticket</h5>
        </div>

        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Sorteo</th>
                            <th>Tipo</th>
                            <th>Números</th>
                            <th>Monto</th>
                            <th></th>
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

            <button type="button" class="btn btn-success"
                    id="btnGuardar">
                Registrar ticket
            </button>

            <a href="index.php" class="btn btn-secondary">
                Volver al menú
            </a>

        </div>
    </div>

</div>

<!-- RIGHT PANEL -->
<div id="sidePanel" class="side-panel">

    <div class="panel-header">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

            <div>
                <button type="button" id="tabConsultar"
                        class="panel-tab active"
                        onclick="mostrarSeccion('consultar')">
                    Consultar jugadas
                </button>

                <button type="button" id="tabAnulaciones"
                        class="panel-tab"
                        onclick="mostrarSeccion('anulaciones')">
                    Anulaciones
                </button>
            </div>

            <button type="button" class="btn btn-light"
                    onclick="cerrarPanel()">
                &times; Cerrar
            </button>

        </div>

    </div>

    <div class="panel-content">

        <!-- CONSULTAR -->
        <section id="seccionConsultar">

            <h5 class="mb-3">Consultar jugadas</h5>

            <div class="row g-3 align-items-end mb-3">

                <div class="col-md-4">
                    <label class="form-label" for="fecha">Fecha</label>
                    <input type="date" id="fecha"
                           class="form-control"
                           value="<?= date('Y-m-d') ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="codigoConsulta">
                        Código de ticket
                    </label>
                    <input type="text" id="codigoConsulta"
                           class="form-control"
                           placeholder="TK0000...">
                </div>

                <div class="col-md-4">
                    <button type="button" class="btn btn-primary"
                            id="btnBuscar">
                        Buscar
                    </button>
                </div>

            </div>

            <div id="mensajeConsulta"></div>

            <p class="text-muted" id="resumen"></p>

            <div class="table-responsive">
                <table class="table table-striped table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Hora</th>
                            <th>Agente</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th></th>
                        </tr>
                    </thead>

                    <tbody id="listaTickets"></tbody>
                </table>
            </div>

        </section>

        <!-- ANULACIONES -->
        <section id="seccionAnulaciones" style="display:none">

            <h5 class="mb-3">Nueva solicitud de anulación</h5>

            <div class="card mb-3">
                <div class="card-body">

                    <div id="mensajeAnulacion"></div>

                    <div class="mb-3">
                        <label class="form-label" for="codigoAnulacion">
                            Ticket a anular
                        </label>

                        <input type="text" id="codigoAnulacion"
                               class="form-control"
                               placeholder="TK00000001">
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="motivo">
                            Motivo
                        </label>

                        <textarea id="motivo"
                                  class="form-control"
                                  rows="3"
                                  maxlength="200"></textarea>

                        <div class="form-text">
                            <span id="contador">0</span>/200 caracteres
                        </div>
                    </div>

                    <button type="button"
                            class="btn btn-danger w-100"
                            id="btnEnviar">
                        Enviar solicitud
                    </button>

                </div>
            </div>

            <h5 class="mb-3">Solicitudes</h5>

            <div class="table-responsive">
                <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th>Ticket</th>
                            <th>Agente</th>
                            <th>Motivo</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                        </tr>
                    </thead>

                    <tbody id="listaSolicitudes"></tbody>
                </table>
            </div>

        </section>

    </div>
</div>

<script>
function abrirPanel(seccion) {
    document.body.classList.add('panel-open');

    document.getElementById('sidePanel').classList.add('open');

    mostrarSeccion(seccion);
}

function cerrarPanel() {
    document.body.classList.remove('panel-open');

    document.getElementById('sidePanel').classList.remove('open');
}

function mostrarSeccion(seccion) {

    const consultar = seccion === 'consultar';

    document.getElementById('seccionConsultar').style.display =
        consultar ? 'block' : 'none';

    document.getElementById('seccionAnulaciones').style.display =
        consultar ? 'none' : 'block';

    document.getElementById('tabConsultar').classList.toggle(
        'active', consultar
    );

    document.getElementById('tabAnulaciones').classList.toggle(
        'active', !consultar
    );
}
</script>

<script src="js/registrar.js"></script>
<script src="js/consultar.js"></script>
<script src="js/anulacion.js"></script>

<?php require __DIR__ . '/includes/footer.php'; ?>
