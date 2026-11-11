
<?php

/**
 * ==========================================================
 * VISTA DEL REPORTE DE PAGOS
 * Sistema Banca Loca
 * ==========================================================
 */

if (
    !isset(
        $resumen,
        $pagos,
        $tiposPago,
        $desde,
        $hasta,
        $tipoPago,
        $estado,
        $error
    )
) {
    header('Location: /banca_loca/reporte_pagos.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Reporte de Pagos | Banca Loca</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">

    <link rel="stylesheet" href="/banca_loca/reportes.css?v=4">

</head>

<body>

<div class="container-fluid py-4">

    <!-- ENCABEZADO -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h2 class="fw-bold">
                <i class="bi bi-cash-stack"></i>
                Reporte de Pagos
            </h2>

            <p class="text-muted mb-0">
                Consulta y seguimiento de los pagos realizados y pendientes.
            </p>
        </div>

        <div class="d-flex gap-2">

            <a href="/banca_loca/reportes.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i>
                Volver
            </a>

            <button onclick="window.print()" class="btn btn-success">
                <i class="bi bi-printer"></i>
                Imprimir
            </button>

        </div>

    </div>


    <!-- FILTROS -->

    <div class="card shadow-sm mb-4">

        <div class="card-body">

            <h5 class="fw-bold mb-3">
                <i class="bi bi-funnel"></i>
                Filtrar pagos
            </h5>

            <form method="GET" action="/banca_loca/reporte_pagos.php">

                <div class="row g-3">

                    <div class="col-md-3">

                        <label class="form-label">Desde</label>

                        <input
                            type="date"
                            name="desde"
                            class="form-control"
                            value="<?= htmlspecialchars($desde) ?>"
                            required
                        >

                    </div>

                    <div class="col-md-3">

                        <label class="form-label">Hasta</label>

                        <input
                            type="date"
                            name="hasta"
                            class="form-control"
                            value="<?= htmlspecialchars($hasta) ?>"
                            required
                        >

                    </div>

                    <div class="col-md-3">

                        <label class="form-label">Tipo de pago</label>

                        <select name="tipo_pago" class="form-select">

                            <option value="">Todos</option>

                            <?php foreach ($tiposPago as $tipo): ?>

                                <option
                                    value="<?= htmlspecialchars($tipo) ?>"
                                    <?= $tipoPago === $tipo ? 'selected' : '' ?>
                                >
                                    <?= ucfirst(htmlspecialchars($tipo)) ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <div class="col-md-3">

                        <label class="form-label">Estado</label>

                        <select name="estado" class="form-select">

                            <option value="">Todos</option>

                            <option value="pagado" <?= $estado === 'pagado' ? 'selected' : '' ?>>
                                Pagado
                            </option>

                            <option value="pendiente" <?= $estado === 'pendiente' ? 'selected' : '' ?>>
                                Pendiente
                            </option>

                        </select>

                    </div>

                    <div class="col-12 d-flex gap-2">

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i>
                            Consultar
                        </button>

                        <a href="/banca_loca/reporte_pagos.php" class="btn btn-outline-secondary">
                            Limpiar filtros
                        </a>

                    </div>

                </div>

            </form>

        </div>

    </div>


    <!-- MENSAJE DE ERROR -->

    <?php if (!empty($error)): ?>

        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle"></i>
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>


    <!-- TARJETAS DE RESUMEN -->

    <div class="row g-3 mb-4">

        <div class="col-md-4">

            <div class="card shadow-sm h-100">

                <div class="card-body">

                    <h6 class="text-muted">Total de pagos</h6>

                    <h3 class="fw-bold">
                        <?= number_format($resumen['total_pagos']) ?>
                    </h3>

                </div>

            </div>

        </div>


        <div class="col-md-4">

            <div class="card shadow-sm h-100">

                <div class="card-body">

                    <h6 class="text-muted">Pagos realizados</h6>

                    <h3 class="fw-bold text-success">
                        <?= number_format($resumen['monto_pagado'], 2) ?>
                    </h3>

                    <small>
                        <?= number_format($resumen['total_pagados']) ?> pagos
                    </small>

                </div>

            </div>

        </div>


        <div class="col-md-4">

            <div class="card shadow-sm h-100">

                <div class="card-body">

                    <h6 class="text-muted">Pagos pendientes</h6>

                    <h3 class="fw-bold text-warning">
                        <?= number_format($resumen['monto_pendiente'], 2) ?>
                    </h3>

                    <small>
                        <?= number_format($resumen['total_pendientes']) ?> pagos
                    </small>

                </div>

            </div>

        </div>

    </div>


    <!-- TABLA DE PAGOS -->

    <div class="card shadow-sm">

        <div class="card-body">

            <h5 class="fw-bold mb-3">
                Detalle de pagos
            </h5>

            <div class="table-responsive">

                <table class="table table-hover table-striped align-middle">

                    <thead class="table-dark">

                        <tr>
                            <th>ID</th>
                            <th>Número de boleto</th>
                            <th>Premio</th>
                            <th>Tipo de pago</th>
                            <th>Cuenta destino</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Cajero</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php if (!empty($pagos)): ?>

                            <?php foreach ($pagos as $pago): ?>

                                <tr>

                                    <td>
                                        <?= htmlspecialchars((string) $pago['id']) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($pago['numero_boleto'] ?? '') ?>
                                    </td>

                                    <td>
                                        RD$ <?= number_format((float) $pago['premio'], 2) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars(ucfirst($pago['tipo_pago'] ?? '')) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($pago['cuenta_destino'] ?? 'N/A') ?>
                                    </td>

                                    <td>

                                        <?php if ($pago['estado'] === 'pagado'): ?>

                                            <span class="badge bg-success">
                                                Pagado
                                            </span>

                                        <?php else: ?>

                                            <span class="badge bg-warning text-dark">
                                                Pendiente
                                            </span>

                                        <?php endif; ?>

                                    </td>

                                    <td>
                                        <?= htmlspecialchars($pago['fecha_pago'] ?? 'Pendiente') ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($pago['hora_pago'] ?? 'Pendiente') ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($pago['cajero'] ?? 'N/A') ?>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>

                                <td colspan="9" class="text-center py-4">

                                    <i class="bi bi-inbox fs-3"></i>

                                    <p class="mb-0 mt-2">
                                        No se encontraron pagos para los filtros seleccionados.
                                    </p>

                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

</body>
</html>
