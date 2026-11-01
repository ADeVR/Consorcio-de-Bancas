<?php require __DIR__ . '/includes/header.php'; ?>

<h1 class="text-center fw-bold">Módulo 4 - Jugar</h1>
<p class="text-center text-muted mb-5">Registro y consulta de jugadas</p>

<div class="row g-4">

    <div class="col-md-4">
        <div class="card shadow-sm h-100 text-center">
            <div class="card-body">
                <div class="fs-1 mb-3">🎟️</div>
                <h4>Registrar Jugada</h4>
                <p class="text-muted">Registrar una nueva jugada.</p>
                <a href="registrar.php" class="btn btn-secondary">Acceder</a>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm h-100 text-center">
            <div class="card-body">
                <div class="fs-1 mb-3">🔍</div>
                <h4>Consultar Jugadas</h4>
                <p class="text-muted">Consultar las jugadas registradas.</p>
                <a href="consultar.php" class="btn btn-secondary">Acceder</a>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm h-100 text-center">
            <div class="card-body">
                <div class="fs-1 mb-3">📄</div>
                <h4>Solicitar Anulación</h4>
                <p class="text-muted">Gestionar solicitudes de anulación.</p>
                <a href="anulacion.php" class="btn btn-secondary">Acceder</a>
            </div>
        </div>
    </div>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>