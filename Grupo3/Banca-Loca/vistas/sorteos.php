
<?php

/**
 * ==========================================================
 * VISTA - REPORTE DE SORTEOS
 * Sistema Banca Loca
 * ==========================================================
 */

// Inicializar variables.
$resumen = $resumen ?? [];
$sorteos = $sorteos ?? [];
$loterias = $loterias ?? [];
$error = $error ?? '';

$desde = $desde ?? date('Y-m-01');
$hasta = $hasta ?? date('Y-m-d');

$loteria = $loteria ?? 0;
$sorteo = $sorteo ?? 0;
$activo = $activo ?? '';

// Protección de salida HTML.
$e = static function ($valor): string {
    return htmlspecialchars(
        (string)$valor,
        ENT_QUOTES,
        'UTF-8'
    );
};

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Reporte de Sorteos | Banca Loca</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
          rel="stylesheet">
    <link rel="stylesheet" href="/banca_loca/reporte_sorteos.css?v=1">
    

</head>

<body>

<main class="container-fluid report-container">

    <!-- ENCABEZADO -->

    <header class="page-header">

        <div>

            <h2>
                <i class="bi bi-trophy text-warning me-2"></i>
                Reporte de Sorteos
            </h2>

            <p>
                Consulta de sorteos, programación, actividad y resultados registrados.
            </p>

        </div>

        <div class="d-flex gap-2 flex-wrap no-print">

            <a href="/banca_loca/reportes.php"
               class="btn btn-outline-secondary">

                <i class="bi bi-arrow-left me-1"></i>
                Volver a reportes

            </a>

            <button onclick="window.print()"
                    class="btn btn-primary">

                <i class="bi bi-printer me-1"></i>
                Imprimir

            </button>

        </div>

    </header>


    <!-- MENSAJE DE ERROR -->

    <?php if ($error !== ''): ?>

        <div class="alert alert-danger">

            <i class="bi bi-exclamation-triangle me-2"></i>

            <?= $e($error) ?>

        </div>

    <?php endif; ?>


    <!-- TARJETAS ESTADÍSTICAS -->

    <section class="row g-3 mb-4">

        <!-- TOTAL SORTEOS -->

        <div class="col-xl-4 col-md-6">

            <div class="card summary-card">

                <div class="card-body d-flex justify-content-between align-items-center">

                    <div>

                        <div class="summary-title">
                            TOTAL DE SORTEOS
                        </div>

                        <div class="summary-number">
                            <?= number_format((int)($resumen['total_sorteos'] ?? 0)) ?>
                        </div>

                    </div>

                    <div class="summary-icon bg-primary-subtle text-primary">
                        <i class="bi bi-trophy"></i>
                    </div>

                </div>

            </div>

        </div>


        <!-- SORTEOS ACTIVOS -->

        <div class="col-xl-4 col-md-6">

            <div class="card summary-card">

                <div class="card-body d-flex justify-content-between align-items-center">

                    <div>

                        <div class="summary-title">
                            SORTEOS ACTIVOS
                        </div>

                        <div class="summary-number text-success">
                            <?= number_format((int)($resumen['sorteos_activos'] ?? 0)) ?>
                        </div>

                    </div>

                    <div class="summary-icon bg-success-subtle text-success">
                        <i class="bi bi-check-circle"></i>
                    </div>

                </div>

            </div>

        </div>


        <!-- SORTEOS INACTIVOS -->

        <div class="col-xl-4 col-md-6">

            <div class="card summary-card">

                <div class="card-body d-flex justify-content-between align-items-center">

                    <div>

                        <div class="summary-title">
                            SORTEOS INACTIVOS
                        </div>

                        <div class="summary-number text-danger">
                            <?= number_format((int)($resumen['sorteos_inactivos'] ?? 0)) ?>
                        </div>

                    </div>

                    <div class="summary-icon bg-danger-subtle text-danger">
                        <i class="bi bi-pause-circle"></i>
                    </div>

                </div>

            </div>

        </div>


        <!-- TOTAL JUGADAS -->

        <div class="col-xl-4 col-md-6">

            <div class="card summary-card">

                <div class="card-body d-flex justify-content-between align-items-center">

                    <div>

                        <div class="summary-title">
                            TOTAL DE JUGADAS
                        </div>

                        <div class="summary-number text-primary">
                            <?= number_format((int)($resumen['total_jugadas'] ?? 0)) ?>
                        </div>

                    </div>

                    <div class="summary-icon bg-primary-subtle text-primary">
                        <i class="bi bi-ticket-perforated"></i>
                    </div>

                </div>

            </div>

        </div>


        <!-- IMPORTE APOSTADO -->

        <div class="col-xl-4 col-md-6">

            <div class="card summary-card">

                <div class="card-body d-flex justify-content-between align-items-center">

                    <div>

                        <div class="summary-title">
                            IMPORTE TOTAL APOSTADO
                        </div>

                        <div class="summary-number text-success">
                            RD$ <?= number_format((float)($resumen['total_apostado'] ?? 0), 2) ?>
                        </div>

                    </div>

                    <div class="summary-icon bg-success-subtle text-success">
                        <i class="bi bi-cash-stack"></i>
                    </div>

                </div>

            </div>

        </div>


        <!-- JUGADAS GANADORAS -->

        <div class="col-xl-4 col-md-6">

            <div class="card summary-card">

                <div class="card-body d-flex justify-content-between align-items-center">

                    <div>

                        <div class="summary-title">
                            JUGADAS GANADORAS REGISTRADAS
                        </div>

                        <div class="summary-number text-warning">
                            <?= number_format((int)($resumen['total_ganadoras'] ?? 0)) ?>
                        </div>

                    </div>

                    <div class="summary-icon bg-warning-subtle text-warning">
                        <i class="bi bi-award"></i>
                    </div>

                </div>

            </div>

        </div>

    </section>


    <!-- FILTROS -->

    <section class="card filter-card mb-4">

        <div class="card-header">

            <h5 class="fw-bold mb-0">

                <i class="bi bi-funnel text-primary me-2"></i>
                Filtros de búsqueda

            </h5>

        </div>

        <div class="card-body">

            <form action="/banca_loca/reporte_sorteos.php"
                  method="GET">

                <div class="row g-3">

                    <!-- FECHA DESDE -->

                    <div class="col-lg-2 col-md-6">

                        <label class="form-label">
                            Fecha desde
                        </label>

                        <input
                            type="date"
                            name="desde"
                            class="form-control"
                            value="<?= $e($desde) ?>"
                            required
                        >

                    </div>


                    <!-- FECHA HASTA -->

                    <div class="col-lg-2 col-md-6">

                        <label class="form-label">
                            Fecha hasta
                        </label>

                        <input
                            type="date"
                            name="hasta"
                            class="form-control"
                            value="<?= $e($hasta) ?>"
                            min="<?= $e($desde) ?>"
                            required
                        >

                    </div>


                    <!-- LOTERÍA -->

                    <div class="col-lg-3 col-md-6">

                        <label class="form-label">
                            Lotería
                        </label>

                        <select name="loteria" class="form-select">

                            <option value="0">
                                Todas las loterías
                            </option>

                            <?php foreach ($loterias as $item): ?>

                                <option
                                    value="<?= (int)$item['id'] ?>"
                                    <?= (int)$loteria === (int)$item['id'] ? 'selected' : '' ?>
                                >

                                    <?= $e($item['nombre']) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <!-- SORTEO -->

                    <div class="col-lg-3 col-md-6">

                        <label class="form-label">
                            Sorteo
                        </label>

                        <select name="sorteo" class="form-select">

                            <option value="0">
                                Todos los sorteos
                            </option>

                            <?php foreach ($sorteos as $item): ?>

                                <option
                                    value="<?= (int)$item['id'] ?>"
                                    <?= (int)$sorteo === (int)$item['id'] ? 'selected' : '' ?>
                                >

                                    <?= $e($item['nombre']) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <!-- ESTADO -->

                    <div class="col-lg-2 col-md-6">

                        <label class="form-label">
                            Estado
                        </label>

                        <select name="activo" class="form-select">

                            <option value="">
                                Todos
                            </option>

                            <option value="1"
                                <?= $activo === '1' ? 'selected' : '' ?>>

                                Activos

                            </option>

                            <option value="0"
                                <?= $activo === '0' ? 'selected' : '' ?>>

                                Inactivos

                            </option>

                        </select>

                    </div>


                    <!-- BOTONES -->

                    <div class="col-12 d-flex gap-2 flex-wrap no-print">

                        <button type="submit"
                                class="btn btn-primary px-4">

                            <i class="bi bi-search me-1"></i>
                            Consultar sorteos

                        </button>

                        <a href="/banca_loca/reporte_sorteos.php"
                           class="btn btn-outline-secondary">

                            <i class="bi bi-arrow-counterclockwise me-1"></i>
                            Limpiar filtros

                        </a>

                    </div>

                </div>

            </form>

        </div>

    </section>


    <!-- TABLA DE SORTEOS -->

    <section class="card table-card">

        <div class="card-header">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

                <h5 class="fw-bold mb-0">

                    <i class="bi bi-list-ul text-primary me-2"></i>
                    Detalle de sorteos

                </h5>

                <span class="badge bg-primary">

                    <?= count($sorteos) ?> registros

                </span>

            </div>

        </div>


        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead>

                        <tr>

                            <th>Lotería</th>

                            <th>Sorteo</th>

                            <th>Hora</th>

                            <th>Días programados</th>

                            <th class="text-center">Jugadas</th>

                            <th class="text-end">Importe apostado</th>

                            <th class="text-center">Ganadoras</th>

                            <th>Premios registrados</th>

                            <th>Estado</th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php if (empty($sorteos)): ?>

                            <tr>

                                <td colspan="9">

                                    <div class="empty-state">

                                        <i class="bi bi-inbox"></i>

                                        <h6 class="fw-bold mt-3">
                                            No se encontraron sorteos
                                        </h6>

                                        <p class="mb-0">
                                            Modifica los filtros para realizar otra consulta.
                                        </p>

                                    </div>

                                </td>

                            </tr>

                        <?php else: ?>

                            <?php foreach ($sorteos as $item): ?>

                                <tr>

                                    <td class="fw-semibold">

                                        <?= $e($item['loteria']) ?>

                                    </td>


                                    <td class="sorteo-name">

                                        <?= $e($item['nombre']) ?>

                                    </td>


                                    <td>

                                        <span class="badge bg-light text-dark border">

                                            <i class="bi bi-clock me-1"></i>

                                            <?= $e(date(
                                                'h:i A',
                                                strtotime($item['hora'])
                                            )) ?>

                                        </span>

                                    </td>


                                    <td>

                                        <?= $e($item['dias']) ?>

                                    </td>


                                    <td class="text-center">

                                        <?= number_format((int)$item['total_jugadas']) ?>

                                    </td>


                                    <td class="text-end fw-bold">

                                        RD$ <?= number_format(
                                            (float)$item['total_apostado'],
                                            2
                                        ) ?>

                                    </td>


                                    <td class="text-center">

                                        <span class="badge bg-warning-subtle text-warning-emphasis">

                                            <?= number_format((int)$item['total_ganadoras']) ?>

                                        </span>

                                    </td>


                                    <td>

                                        <?= $e($item['premios_registrados']) ?>

                                    </td>


                                    <td>

                                        <?php if ((int)$item['activo'] === 1): ?>

                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle me-1"></i>
                                                Activo
                                            </span>

                                        <?php else: ?>

                                            <span class="badge bg-danger">
                                                <i class="bi bi-pause-circle me-1"></i>
                                                Inactivo
                                            </span>

                                        <?php endif; ?>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>


        <div class="card-footer bg-white text-secondary small">

            Sistema Banca Loca · Reporte de Sorteos

        </div>

    </section>


    <!-- PIE DE PÁGINA -->

    <footer>

        Reporte generado el <?= date('d/m/Y h:i A') ?>

    </footer>

</main>

</body>
</html>
