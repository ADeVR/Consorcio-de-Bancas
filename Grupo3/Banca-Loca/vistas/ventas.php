
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Reporte de Ventas | Banca Loca</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
          rel="stylesheet">

</head>

<body class="bg-light">

<main class="container-fluid px-4 py-4">

    <!-- ENCABEZADO -->

    <header class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">

        <div>
            <h2 class="fw-bold text-dark mb-1">
                <i class="bi bi-cart-check me-2 text-primary"></i>
                Reporte de Ventas
            </h2>

            <p class="text-secondary mb-0">
                Consulta y seguimiento de las operaciones comerciales.
            </p>
        </div>

        <div class="d-flex gap-2">

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

    <?php if (!empty($error)): ?>

        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>

    <?php endif; ?>


    <!-- TARJETAS ESTADÍSTICAS -->

    <section class="row g-3 mb-4">

        <!-- TOTAL TICKETS -->

        <div class="col-xl-3 col-md-6">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body d-flex justify-content-between align-items-center">

                    <div>
                        <p class="text-secondary small fw-semibold mb-2">
                            TOTAL DE TICKETS
                        </p>

                        <h3 class="fw-bold mb-0">
                            <?= number_format((int)$resumen['total_tickets']) ?>
                        </h3>
                    </div>

                    <div class="bg-primary-subtle text-primary rounded-3 p-3">
                        <i class="bi bi-receipt fs-3"></i>
                    </div>

                </div>

            </div>

        </div>


        <!-- TICKETS ACTIVOS -->

        <div class="col-xl-3 col-md-6">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body d-flex justify-content-between align-items-center">

                    <div>
                        <p class="text-secondary small fw-semibold mb-2">
                            TICKETS ACTIVOS
                        </p>

                        <h3 class="fw-bold text-success mb-0">
                            <?= number_format((int)$resumen['tickets_activos']) ?>
                        </h3>
                    </div>

                    <div class="bg-success-subtle text-success rounded-3 p-3">
                        <i class="bi bi-check-circle fs-3"></i>
                    </div>

                </div>

            </div>

        </div>


        <!-- TICKETS ANULADOS -->

        <div class="col-xl-3 col-md-6">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body d-flex justify-content-between align-items-center">

                    <div>
                        <p class="text-secondary small fw-semibold mb-2">
                            TICKETS ANULADOS
                        </p>

                        <h3 class="fw-bold text-danger mb-0">
                            <?= number_format((int)$resumen['tickets_anulados']) ?>
                        </h3>
                    </div>

                    <div class="bg-danger-subtle text-danger rounded-3 p-3">
                        <i class="bi bi-x-circle fs-3"></i>
                    </div>

                </div>

            </div>

        </div>


        <!-- VENTAS VÁLIDAS -->

        <div class="col-xl-3 col-md-6">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body d-flex justify-content-between align-items-center">

                    <div>
                        <p class="text-secondary small fw-semibold mb-2">
                            VENTAS VÁLIDAS
                        </p>

                        <h3 class="fw-bold text-success mb-0">
                            RD$ <?= number_format((float)$resumen['ventas_validas'], 2) ?>
                        </h3>
                    </div>

                    <div class="bg-success-subtle text-success rounded-3 p-3">
                        <i class="bi bi-cash-stack fs-3"></i>
                    </div>

                </div>

            </div>

        </div>

    </section>


    <!-- VENTAS ANULADAS -->

    <section class="row mb-4">

        <div class="col-md-4">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <p class="text-secondary small fw-semibold mb-2">
                        IMPORTE DE VENTAS ANULADAS
                    </p>

                    <h4 class="fw-bold text-danger mb-0">
                        RD$ <?= number_format((float)$resumen['ventas_anuladas'], 2) ?>
                    </h4>

                </div>

            </div>

        </div>

    </section>


    <!-- FILTROS DE BÚSQUEDA -->

    <section class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white py-3">

            <h5 class="fw-bold mb-0">
                <i class="bi bi-funnel me-2 text-primary"></i>
                Filtros de búsqueda
            </h5>

        </div>

        <div class="card-body">

            <form action="/banca_loca/reporte_ventas.php" method="GET">

                <div class="row g-3">

                    <!-- FECHA DESDE -->

                    <div class="col-lg-2 col-md-6">

                        <label class="form-label fw-semibold">
                            Fecha desde
                        </label>

                        <input
                            type="date"
                            name="desde"
                            class="form-control"
                            value="<?= htmlspecialchars($desde, ENT_QUOTES, 'UTF-8') ?>"
                            required
                        >

                    </div>


                    <!-- FECHA HASTA -->

                    <div class="col-lg-2 col-md-6">

                        <label class="form-label fw-semibold">
                            Fecha hasta
                        </label>

                        <input
                            type="date"
                            name="hasta"
                            class="form-control"
                            value="<?= htmlspecialchars($hasta, ENT_QUOTES, 'UTF-8') ?>"
                            min="<?= htmlspecialchars($desde, ENT_QUOTES, 'UTF-8') ?>"
                            required
                        >

                    </div>


                    <!-- USUARIO -->

                    <div class="col-lg-2 col-md-6">

                        <label class="form-label fw-semibold">
                            Usuario
                        </label>

                        <select name="usuario" class="form-select">

                            <option value="0">
                                Todos
                            </option>

                            <?php foreach ($usuarios as $item): ?>

                                <option
                                    value="<?= (int)$item['id'] ?>"
                                    <?= $usuario == $item['id'] ? 'selected' : '' ?>
                                >

                                    <?= htmlspecialchars($item['usuario'], ENT_QUOTES, 'UTF-8') ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <!-- LOTERÍA -->

                    <div class="col-lg-3 col-md-6">

                        <label class="form-label fw-semibold">
                            Lotería
                        </label>

                        <select name="loteria" class="form-select">

                            <option value="0">
                                Todas las loterías
                            </option>

                            <?php foreach ($loterias as $item): ?>

                                <option
                                    value="<?= (int)$item['id'] ?>"
                                    <?= $loteria == $item['id'] ? 'selected' : '' ?>
                                >

                                    <?= htmlspecialchars($item['nombre'], ENT_QUOTES, 'UTF-8') ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <!-- SORTEO -->

                    <div class="col-lg-3 col-md-6">

                        <label class="form-label fw-semibold">
                            Sorteo
                        </label>

                        <select name="sorteo" class="form-select">

                            <option value="0">
                                Todos los sorteos
                            </option>

                            <?php foreach ($sorteos as $item): ?>

                                <option
                                    value="<?= (int)$item['id'] ?>"
                                    <?= $sorteo == $item['id'] ? 'selected' : '' ?>
                                >

                                    <?= htmlspecialchars($item['nombre'], ENT_QUOTES, 'UTF-8') ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <!-- BOTONES -->

                    <div class="col-12 d-flex gap-2 mt-3">

                        <button type="submit" class="btn btn-primary px-4">

                            <i class="bi bi-search me-1"></i>
                            Consultar ventas

                        </button>

                       <a href="/banca_loca/reporte_ventas.php"
                    class="btn btn-outline-secondary">
                 <i class="bi bi-arrow-counterclockwise me-1"></i>
                 Limpiar filtros
                    
                        </a>

                    </div>

                </div>

            </form>

        </div>

    </section>


    <!-- DETALLE DE VENTAS -->

    <section class="card border-0 shadow-sm">

        <div class="card-header bg-white py-3">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

                <h5 class="fw-bold mb-0">

                    <i class="bi bi-list-ul me-2 text-primary"></i>
                    Detalle de ventas

                </h5>

                <span class="badge bg-primary">
                    <?= count($ventas) ?> registros
                </span>

            </div>

        </div>


        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover table-striped align-middle mb-0">

                    <thead class="table-light">

                        <tr>

                            <th>Ticket</th>
                            <th>Fecha y hora</th>
                            <th>Usuario</th>
                            <th>Lotería</th>
                            <th>Sorteo</th>
                            <th>Modalidad</th>
                            <th>Números</th>
                            <th class="text-end">Monto jugada</th>
                            <th class="text-end">Total ticket</th>
                            <th>Estado ticket</th>
                            <th>Estado jugada</th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php if (empty($ventas)): ?>

                            <tr>

                                <td colspan="11" class="text-center py-5">

                                    <i class="bi bi-inbox fs-1 text-secondary"></i>

                                    <h6 class="mt-3 fw-bold">
                                        No se encontraron ventas
                                    </h6>

                                    <p class="text-secondary small mb-0">
                                        Modifica los filtros para realizar otra consulta.
                                    </p>

                                </td>

                            </tr>

                        <?php else: ?>

                            <?php foreach ($ventas as $venta): ?>

                                <tr>

                                    <td class="fw-bold text-primary">
                                        <?= htmlspecialchars($venta['codigo_ticket'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>

                                    <td>
                                        <?= date('d/m/Y H:i', strtotime($venta['fecha_hora'])) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($venta['usuario'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($venta['loteria'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($venta['sorteo'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($venta['modalidad'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>

                                    <td class="fw-bold">
                                        <?= htmlspecialchars($venta['numeros'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>

                                    <td class="text-end">
                                        RD$ <?= number_format((float)$venta['monto'], 2) ?>
                                    </td>

                                    <td class="text-end fw-bold">
                                        RD$ <?= number_format((float)$venta['total_ticket'], 2) ?>
                                    </td>

                                    <td>

                                        <?php if ($venta['estado_ticket'] === 'activo'): ?>

                                            <span class="badge bg-success">
                                                Activo
                                            </span>

                                        <?php else: ?>

                                            <span class="badge bg-danger">
                                                Anulado
                                            </span>

                                        <?php endif; ?>

                                    </td>

                                    <td>

                                        <?php if ($venta['estado_jugada'] === 'pagada'): ?>

                                            <span class="badge bg-info text-dark">
                                                Pagada
                                            </span>

                                        <?php elseif ($venta['estado_jugada'] === 'ganadora'): ?>

                                            <span class="badge bg-success">
                                                Ganadora
                                            </span>

                                        <?php elseif ($venta['estado_jugada'] === 'perdedora'): ?>

                                            <span class="badge bg-secondary">
                                                Perdedora
                                            </span>

                                        <?php else: ?>

                                            <span class="badge bg-warning text-dark">
                                                <?= htmlspecialchars($venta['estado_jugada'], ENT_QUOTES, 'UTF-8') ?>
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
            Sistema de Administración de Loterías · Reporte de ventas
        </div>

    </section>


    <!-- PIE DE PÁGINA -->

    <footer class="text-center text-secondary small mt-4">

        Reporte generado el <?= date('d/m/Y h:i A') ?>

    </footer>

</main>

</body>
</html>
