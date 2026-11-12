
<?php

/**
 * ==========================================================
 * VISTA - REPORTE DE TICKETS
 * Sistema Banca Loca
 * ==========================================================
 */

// Inicializar variables de seguridad.
$resumen = $resumen ?? [];
$tickets = $tickets ?? [];
$usuarios = $usuarios ?? [];
$error = $error ?? '';

$desde = $desde ?? date('Y-m-01');
$hasta = $hasta ?? date('Y-m-d');
$usuario = $usuario ?? 0;
$estado = $estado ?? '';
$codigo = $codigo ?? '';

// Función para proteger la salida HTML.
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

    <title>Reporte de Tickets | Banca Loca</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
          rel="stylesheet">

    <style>

        body {
            background: #f4f6f9;
            color: #263445;
        }

        .report-container {
            padding: 30px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 28px;
        }

        .page-header h2 {
            font-weight: 750;
            margin-bottom: 6px;
        }

        .page-header p {
            color: #718096;
            margin: 0;
        }

        .summary-card {
            border: 0;
            border-radius: 14px;
            box-shadow: 0 3px 15px rgba(0,0,0,.05);
            height: 100%;
        }

        .summary-card .card-body {
            padding: 23px;
        }

        .summary-title {
            font-size: 12px;
            font-weight: 700;
            color: #778397;
            letter-spacing: .5px;
        }

        .summary-number {
            font-size: 27px;
            font-weight: 750;
            margin: 8px 0 0;
        }

        .summary-icon {
            width: 52px;
            height: 52px;
            border-radius: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 23px;
        }

        .filter-card,
        .table-card {
            border: 0;
            border-radius: 14px;
            box-shadow: 0 3px 15px rgba(0,0,0,.05);
        }

        .filter-card .card-header,
        .table-card .card-header {
            background: #fff;
            padding: 20px;
            border-bottom: 1px solid #edf0f4;
            border-radius: 14px 14px 0 0;
        }

        .form-label {
            font-weight: 600;
            font-size: 13px;
        }

        .table thead th {
            background: #f7f9fc;
            color: #64748b;
            font-size: 12px;
            text-transform: uppercase;
            white-space: nowrap;
            padding: 15px;
        }

        .table tbody td {
            padding: 15px;
            vertical-align: middle;
            font-size: 13px;
        }

        .ticket-code {
            font-weight: 700;
            color: #2563eb;
        }

        .empty-state {
            padding: 55px 20px;
            text-align: center;
            color: #8793a5;
        }

        .empty-state i {
            font-size: 45px;
        }

        footer {
            color: #8993a2;
            font-size: 12px;
            text-align: center;
            margin-top: 25px;
        }

        @media print {

            body {
                background: white !important;
            }

            .no-print {
                display: none !important;
            }

            .report-container {
                padding: 0;
            }

            .summary-card,
            .table-card {
                box-shadow: none;
                border: 1px solid #ddd;
            }

            .table {
                font-size: 10px;
            }

        }

        @media (max-width: 768px) {

            .report-container {
                padding: 15px;
            }

            .summary-number {
                font-size: 23px;
            }

        }

    </style>

</head>

<body>

<main class="container-fluid report-container">

    <!-- ENCABEZADO -->

    <header class="page-header">

        <div>

            <h2>
                <i class="bi bi-ticket-perforated text-primary me-2"></i>
                Reporte de Tickets
            </h2>

            <p>
                Consulta, seguimiento e historial de tickets registrados.
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

        <!-- TOTAL DE TICKETS -->

        <div class="col-xl-3 col-md-6">

            <div class="card summary-card">

                <div class="card-body d-flex justify-content-between align-items-center">

                    <div>

                        <div class="summary-title">
                            TOTAL DE TICKETS
                        </div>

                        <div class="summary-number">
                            <?= number_format((int)($resumen['total_tickets'] ?? 0)) ?>
                        </div>

                    </div>

                    <div class="summary-icon bg-primary-subtle text-primary">

                        <i class="bi bi-receipt"></i>

                    </div>

                </div>

            </div>

        </div>


        <!-- TICKETS ACTIVOS -->

        <div class="col-xl-3 col-md-6">

            <div class="card summary-card">

                <div class="card-body d-flex justify-content-between align-items-center">

                    <div>

                        <div class="summary-title">
                            TICKETS ACTIVOS
                        </div>

                        <div class="summary-number text-success">
                            <?= number_format((int)($resumen['tickets_activos'] ?? 0)) ?>
                        </div>

                    </div>

                    <div class="summary-icon bg-success-subtle text-success">

                        <i class="bi bi-check-circle"></i>

                    </div>

                </div>

            </div>

        </div>


        <!-- TICKETS ANULADOS -->

        <div class="col-xl-3 col-md-6">

            <div class="card summary-card">

                <div class="card-body d-flex justify-content-between align-items-center">

                    <div>

                        <div class="summary-title">
                            TICKETS ANULADOS
                        </div>

                        <div class="summary-number text-danger">
                            <?= number_format((int)($resumen['tickets_anulados'] ?? 0)) ?>
                        </div>

                    </div>

                    <div class="summary-icon bg-danger-subtle text-danger">

                        <i class="bi bi-x-circle"></i>

                    </div>

                </div>

            </div>

        </div>


        <!-- IMPORTE ACTIVO -->

        <div class="col-xl-3 col-md-6">

            <div class="card summary-card">

                <div class="card-body d-flex justify-content-between align-items-center">

                    <div>

                        <div class="summary-title">
                            IMPORTE DE VENTAS ACTIVAS
                        </div>

                        <div class="summary-number text-success">
                            RD$ <?= number_format((float)($resumen['importe_activos'] ?? 0), 2) ?>
                        </div>

                    </div>

                    <div class="summary-icon bg-warning-subtle text-warning">

                        <i class="bi bi-cash-stack"></i>

                    </div>

                </div>

            </div>

        </div>

    </section>


    <!-- IMPORTE DE TICKETS ANULADOS -->

    <section class="row mb-4">

        <div class="col-xl-4 col-md-6">

            <div class="card summary-card">

                <div class="card-body">

                    <div class="summary-title">
                        IMPORTE DE TICKETS ANULADOS
                    </div>

                    <h4 class="fw-bold text-danger mt-2 mb-0">

                        RD$ <?= number_format(
                            (float)($resumen['importe_anulados'] ?? 0),
                            2
                        ) ?>

                    </h4>

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

            <form action="/banca_loca/reporte_tickets.php"
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


                    <!-- USUARIO -->

                    <div class="col-lg-3 col-md-6">

                        <label class="form-label">
                            Usuario
                        </label>

                        <select name="usuario" class="form-select">

                            <option value="0">
                                Todos los usuarios
                            </option>

                            <?php foreach ($usuarios as $item): ?>

                                <option
                                    value="<?= (int)$item['id'] ?>"
                                    <?= (int)$usuario === (int)$item['id'] ? 'selected' : '' ?>
                                >

                                    <?= $e($item['usuario']) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <!-- ESTADO -->

                    <div class="col-lg-2 col-md-6">

                        <label class="form-label">
                            Estado
                        </label>

                        <select name="estado" class="form-select">

                            <option value="">
                                Todos
                            </option>

                            <option value="activo"
                                <?= $estado === 'activo' ? 'selected' : '' ?>>
                                Activos
                            </option>

                            <option value="anulado"
                                <?= $estado === 'anulado' ? 'selected' : '' ?>>
                                Anulados
                            </option>

                        </select>

                    </div>


                    <!-- CÓDIGO -->

                    <div class="col-lg-3 col-md-6">

                        <label class="form-label">
                            Código del ticket
                        </label>

                        <input
                            type="text"
                            name="codigo"
                            class="form-control"
                            placeholder="Buscar por código..."
                            maxlength="20"
                            value="<?= $e($codigo) ?>"
                        >

                    </div>


                    <!-- BOTONES -->

                    <div class="col-12 d-flex gap-2 flex-wrap no-print">

                        <button type="submit"
                                class="btn btn-primary px-4">

                            <i class="bi bi-search me-1"></i>
                            Consultar tickets

                        </button>

                        <a href="/banca_loca/reporte_tickets.php"
                           class="btn btn-outline-secondary">

                            <i class="bi bi-arrow-counterclockwise me-1"></i>
                            Limpiar filtros

                        </a>

                    </div>

                </div>

            </form>

        </div>

    </section>


    <!-- TABLA DE TICKETS -->

    <section class="card table-card">

        <div class="card-header">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

                <h5 class="fw-bold mb-0">

                    <i class="bi bi-list-ul text-primary me-2"></i>
                    Historial de tickets

                </h5>

                <span class="badge bg-primary">

                    <?= count($tickets) ?> registros

                </span>

            </div>

        </div>


        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead>

                        <tr>

                            <th>Código</th>

                            <th>Fecha y hora</th>

                            <th>Usuario</th>

                            <th>Apertura de caja</th>

                            <th class="text-center">Jugadas</th>

                            <th class="text-end">Total</th>

                            <th>Estado</th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php if (empty($tickets)): ?>

                            <tr>

                                <td colspan="7">

                                    <div class="empty-state">

                                        <i class="bi bi-inbox"></i>

                                        <h6 class="fw-bold mt-3">
                                            No se encontraron tickets
                                        </h6>

                                        <p class="mb-0">
                                            Modifica los filtros para realizar otra consulta.
                                        </p>

                                    </div>

                                </td>

                            </tr>

                        <?php else: ?>

                            <?php foreach ($tickets as $ticket): ?>

                                <tr>

                                    <td class="ticket-code">

                                        <?= $e($ticket['codigo_ticket']) ?>

                                    </td>


                                    <td>

                                        <?= $e(
                                            date(
                                                'd/m/Y H:i',
                                                strtotime($ticket['fecha_hora'])
                                            )
                                        ) ?>

                                    </td>


                                    <td>

                                        <?= $e($ticket['usuario']) ?>

                                    </td>


                                    <td>

                                        <?php if ($ticket['id_apertura'] !== null): ?>

                                            <span class="badge bg-light text-dark border">

                                                #<?= (int)$ticket['id_apertura'] ?>

                                            </span>

                                        <?php else: ?>

                                            <span class="text-secondary">
                                                No registrada
                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <td class="text-center">

                                        <span class="badge bg-primary-subtle text-primary">

                                            <?= number_format((int)$ticket['cantidad_jugadas']) ?>

                                        </span>

                                    </td>


                                    <td class="text-end fw-bold">

                                        RD$ <?= number_format(
                                            (float)$ticket['total'],
                                            2
                                        ) ?>

                                    </td>


                                    <td>

                                        <?php if ($ticket['estado'] === 'activo'): ?>

                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle me-1"></i>
                                                Activo
                                            </span>

                                        <?php else: ?>

                                            <span class="badge bg-danger">
                                                <i class="bi bi-x-circle me-1"></i>
                                                Anulado
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

            Sistema Banca Loca · Reporte de Tickets

        </div>

    </section>


    <!-- PIE DE PÁGINA -->

    <footer>

        Reporte generado el <?= date('d/m/Y h:i A') ?>

    </footer>

</main>

</body>
</html>
