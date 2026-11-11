
<?php

/**
 * ==========================================================
 * VISTA DEL REPORTE GENERAL
 * Sistema Banca Loca
 * ==========================================================
 */

$desde = $desde ?? date('Y-m-01');
$hasta = $hasta ?? date('Y-m-d');

$ventas = $resumen['ventas'] ?? [];
$premios = $resumen['premios'] ?? [];
$pagos = $resumen['pagos'] ?? [];

$error = $error ?? '';

function generalDinero($valor): string
{
    return 'RD$ ' . number_format((float)$valor, 2);
}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Reporte General | Banca Loca</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="/banca_loca/reportes.css?v=4">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="/banca_loca/general.css?v=1">

</head>

<body>

<div class="container-fluid py-4">

    <!-- ENCABEZADO -->

    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">

        <div>
            <h3 class="fw-bold mb-1">
                <i class="bi bi-bar-chart-line text-success"></i>
                Reporte General
            </h3>

            <p class="text-muted mb-0">
                Resumen general de operaciones de Banca Loca
            </p>
        </div>

        <div class="d-flex gap-2 no-print">

            <a href="/banca_loca/reportes.php"
               class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i>
                Volver a reportes
            </a>

            <button onclick="window.print()"
                    class="btn btn-success">
                <i class="bi bi-printer"></i>
                Imprimir
            </button>

        </div>

    </div>


    <!-- MENSAJE DE ERROR -->

    <?php if ($error !== ''): ?>

        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle"></i>
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>


    <!-- FILTROS -->

    <div class="card shadow-sm border-0 mb-4 no-print">

        <div class="card-body">

            <form method="GET"
                  action="/banca_loca/reporte_general.php">

                <div class="row g-3 align-items-end">

                    <div class="col-md-4">

                        <label class="form-label fw-semibold">
                            Fecha desde
                        </label>

                        <input type="date"
                               name="desde"
                               class="form-control"
                               value="<?= htmlspecialchars($desde) ?>"
                               required>

                    </div>

                    <div class="col-md-4">

                        <label class="form-label fw-semibold">
                            Fecha hasta
                        </label>

                        <input type="date"
                               name="hasta"
                               class="form-control"
                               value="<?= htmlspecialchars($hasta) ?>"
                               required>

                    </div>

                    <div class="col-md-4">

                        <button type="submit"
                                class="btn btn-success w-100">

                            <i class="bi bi-search"></i>
                            Consultar reporte

                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>


    <!-- RESUMEN DE VENTAS -->

    <h5 class="fw-bold mb-3">
        <i class="bi bi-cash-stack text-success"></i>
        Resumen de ventas
    </h5>

    <div class="row g-3 mb-4">

        <div class="col-md-6 col-xl-3">

            <div class="card general-card shadow-sm h-100">

                <div class="card-body">

                    <div class="general-title">
                        Ventas válidas
                    </div>

                    <div class="general-value text-success">
                        <?= generalDinero($ventas['ventas_validas'] ?? 0) ?>
                    </div>

                    <i class="bi bi-cash-stack general-icon text-success"></i>

                </div>

            </div>

        </div>

        <div class="col-md-6 col-xl-3">

            <div class="card general-card shadow-sm h-100">

                <div class="card-body">

                    <div class="general-title">
                        Ventas anuladas
                    </div>

                    <div class="general-value text-danger">
                        <?= generalDinero($ventas['ventas_anuladas'] ?? 0) ?>
                    </div>

                    <i class="bi bi-cash general-icon text-danger"></i>

                </div>

            </div>

        </div>

        <div class="col-md-6 col-xl-3">

            <div class="card general-card shadow-sm h-100">

                <div class="card-body">

                    <div class="general-title">
                        Tickets activos
                    </div>

                    <div class="general-value text-primary">
                        <?= number_format((int)($ventas['tickets_activos'] ?? 0)) ?>
                    </div>

                    <i class="bi bi-receipt general-icon text-primary"></i>

                </div>

            </div>

        </div>

        <div class="col-md-6 col-xl-3">

            <div class="card general-card shadow-sm h-100">

                <div class="card-body">

                    <div class="general-title">
                        Tickets anulados
                    </div>

                    <div class="general-value text-danger">
                        <?= number_format((int)($ventas['tickets_anulados'] ?? 0)) ?>
                    </div>

                    <i class="bi bi-receipt-cutoff general-icon text-danger"></i>

                </div>

            </div>

        </div>

    </div>


    <!-- RESUMEN DE PREMIOS -->

    <h5 class="fw-bold mb-3">
        <i class="bi bi-trophy text-warning"></i>
        Resumen de premios
    </h5>

    <div class="row g-3 mb-4">

        <div class="col-md-6 col-xl-3">

            <div class="card general-card shadow-sm h-100">

                <div class="card-body">

                    <div class="general-title">
                        Total de premios registrados
                    </div>

                    <div class="general-value text-primary">
                        <?= number_format((int)($premios['total_premios'] ?? 0)) ?>
                    </div>

                    <i class="bi bi-award general-icon text-primary"></i>

                </div>

            </div>

        </div>

        <div class="col-md-6 col-xl-3">

            <div class="card general-card shadow-sm h-100">

                <div class="card-body">

                    <div class="general-title">
                        Premios ganadores
                    </div>

                    <div class="general-value text-warning">
                        <?= number_format((int)($premios['premios_ganadores'] ?? 0)) ?>
                    </div>

                    <i class="bi bi-trophy general-icon text-warning"></i>

                </div>

            </div>

        </div>

        <div class="col-md-6 col-xl-3">

            <div class="card general-card shadow-sm h-100">

                <div class="card-body">

                    <div class="general-title">
                        Premios pagados
                    </div>

                    <div class="general-value text-success">
                        <?= number_format((int)($premios['premios_pagados'] ?? 0)) ?>
                    </div>

                    <i class="bi bi-check-circle general-icon text-success"></i>

                </div>

            </div>

        </div>

        <div class="col-md-6 col-xl-3">

            <div class="card general-card shadow-sm h-100">

                <div class="card-body">

                    <div class="general-title">
                        Premios pendientes estimados
                    </div>

                    <div class="general-value text-danger">
                        <?= generalDinero($premios['monto_pendiente_estimado'] ?? 0) ?>
                    </div>

                    <i class="bi bi-hourglass-split general-icon text-danger"></i>

                </div>

            </div>

        </div>

    </div>


    <!-- RESUMEN DE PAGOS -->

    <h5 class="fw-bold mb-3">
        <i class="bi bi-wallet2 text-success"></i>
        Resumen de pagos
    </h5>

    <div class="row g-3 mb-4">

        <div class="col-md-6 col-xl-3">

            <div class="card general-card shadow-sm h-100">

                <div class="card-body">

                    <div class="general-title">
                        Total de pagos
                    </div>

                    <div class="general-value text-primary">
                        <?= number_format((int)($pagos['total_pagos'] ?? 0)) ?>
                    </div>

                    <i class="bi bi-wallet general-icon text-primary"></i>

                </div>

            </div>

        </div>

        <div class="col-md-6 col-xl-3">

            <div class="card general-card shadow-sm h-100">

                <div class="card-body">

                    <div class="general-title">
                        Pagos realizados
                    </div>

                    <div class="general-value text-success">
                        <?= number_format((int)($pagos['pagos_realizados'] ?? 0)) ?>
                    </div>

                    <i class="bi bi-check-circle general-icon text-success"></i>

                </div>

            </div>

        </div>

        <div class="col-md-6 col-xl-3">

            <div class="card general-card shadow-sm h-100">

                <div class="card-body">

                    <div class="general-title">
                        Monto pagado
                    </div>

                    <div class="general-value text-success">
                        <?= generalDinero($pagos['monto_pagado'] ?? 0) ?>
                    </div>

                    <i class="bi bi-cash-coin general-icon text-success"></i>

                </div>

            </div>

        </div>

        <div class="col-md-6 col-xl-3">

            <div class="card general-card shadow-sm h-100">

                <div class="card-body">

                    <div class="general-title">
                        Pagos pendientes
                    </div>

                    <div class="general-value text-danger">
                        <?= generalDinero($pagos['monto_pendiente'] ?? 0) ?>
                    </div>

                    <i class="bi bi-clock-history general-icon text-danger"></i>

                </div>

            </div>

        </div>

    </div>


    <!-- INFORMACIÓN DEL PERÍODO -->

    <div class="card shadow-sm border-0 mt-4">

        <div class="card-body">

            <h5 class="fw-bold mb-3">
                <i class="bi bi-calendar-range"></i>
                Período consultado
            </h5>

            <div class="row">

                <div class="col-md-6">

                    <strong>Desde:</strong>
                    <?= htmlspecialchars($desde) ?>

                </div>

                <div class="col-md-6">

                    <strong>Hasta:</strong>
                    <?= htmlspecialchars($hasta) ?>

                </div>

            </div>

        </div>

    </div>


    <div class="text-center text-muted mt-4">

        <small>
            Banca Loca | Reporte General
        </small>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
