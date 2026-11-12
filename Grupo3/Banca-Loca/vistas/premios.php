
<?php

/**
 * ==========================================================
 * VISTA: REPORTE DE PREMIOS
 * Sistema Banca Loca
 * ==========================================================
 */

if (
    !isset(
        $resumen,
        $premios,
        $loterias,
        $sorteos,
        $tiposPremio,
        $filtros,
        $error
    )
) {
    exit('Error: no se recibieron los datos del reporte.');
}

$moneda = function ($valor) {
    return 'RD$ ' . number_format((float) $valor, 2);
};

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Reporte de Premios | Banca Loca</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
        rel="stylesheet"
    >
    <link rel="stylesheet" href="/banca_loca/reportes.css?v=4">

</head>

<body>

<div class="contenido">

    <!-- ENCABEZADO -->

    <div class="encabezado">

        <div>

            <h2>
                <i class="bi bi-trophy-fill text-success"></i>
                Reporte de Premios
            </h2>

            <p class="text-muted mb-0">
                Consulta y seguimiento de premios ganadores.
            </p>

        </div>

        <div class="no-imprimir">

            <a
                href="/banca_loca/reportes.php"
                class="btn btn-outline-secondary"
            >
                <i class="bi bi-arrow-left"></i>
                Volver
            </a>

            <button
                onclick="window.print()"
                class="btn btn-success"
            >
                <i class="bi bi-printer"></i>
                Imprimir
            </button>

        </div>

    </div>


    <!-- MENSAJE DE ERROR -->

    <?php if (!empty($error)): ?>

        <div class="alert alert-danger">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>

    <?php endif; ?>


    <!-- TARJETAS DE RESUMEN -->

    <div class="row g-3 mb-4">

        <div class="col-md-4 col-lg-2">

            <div class="tarjeta-resumen">

                <div class="icono">
                    <i class="bi bi-trophy"></i>
                </div>

                <h6>Total premios</h6>

                <h3>
                    <?= (int) $resumen['total_premios'] ?>
                </h3>

            </div>

        </div>


        <div class="col-md-4 col-lg-2">

            <div class="tarjeta-resumen">

                <div class="icono">
                    <i class="bi bi-star"></i>
                </div>

                <h6>Ganadoras</h6>

                <h3>
                    <?= (int) $resumen['total_ganadoras'] ?>
                </h3>

            </div>

        </div>


        <div class="col-md-4 col-lg-2">

            <div class="tarjeta-resumen">

                <div class="icono">
                    <i class="bi bi-check-circle"></i>
                </div>

                <h6>Pagadas</h6>

                <h3>
                    <?= (int) $resumen['total_pagadas'] ?>
                </h3>

            </div>

        </div>


        <div class="col-md-4 col-lg-2">

            <div class="tarjeta-resumen">

                <div class="icono">
                    <i class="bi bi-hourglass-split"></i>
                </div>

                <h6>Pendientes</h6>

                <h3>
                    <?= (int) $resumen['total_pendientes_pago'] ?>
                </h3>

            </div>

        </div>


        <div class="col-md-4 col-lg-2">

            <div class="tarjeta-resumen">

                <div class="icono">
                    <i class="bi bi-cash-stack"></i>
                </div>

                <h6>Total apostado</h6>

                <h3 style="font-size:19px">
                    <?= $moneda($resumen['total_apostado']) ?>
                </h3>

            </div>

        </div>


        <div class="col-md-4 col-lg-2">

            <div class="tarjeta-resumen">

                <div class="icono">
                    <i class="bi bi-wallet2"></i>
                </div>

                <h6>Premios estimados</h6>

                <h3 style="font-size:19px">
                    <?= $moneda($resumen['total_premios_estimados']) ?>
                </h3>

            </div>

        </div>

    </div>


    <!-- FILTROS -->

    <div class="panel no-imprimir">

        <h5>
            <i class="bi bi-funnel"></i>
            Filtros de consulta
        </h5>

        <form method="GET" action="">

            <div class="row g-3">

                <div class="col-md-4">

                    <label class="form-label">Desde</label>

                    <input
                        type="date"
                        name="desde"
                        class="form-control"
                        value="<?= htmlspecialchars($desde) ?>"
                    >

                </div>


                <div class="col-md-4">

                    <label class="form-label">Hasta</label>

                    <input
                        type="date"
                        name="hasta"
                        class="form-control"
                        value="<?= htmlspecialchars($hasta) ?>"
                    >

                </div>


                <div class="col-md-4">

                    <label class="form-label">Lotería</label>

                    <select name="loteria" class="form-select">

                        <option value="0">Todas las loterías</option>

                        <?php foreach ($loterias as $item): ?>

                            <option
                                value="<?= (int) $item['id'] ?>"
                                <?= $loteria == $item['id'] ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($item['nombre']) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <div class="col-md-4">

                    <label class="form-label">Sorteo</label>

                    <select name="sorteo" class="form-select">

                        <option value="0">Todos los sorteos</option>

                        <?php foreach ($sorteos as $item): ?>

                            <option
                                value="<?= (int) $item['id'] ?>"
                                <?= $sorteo == $item['id'] ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars(
                                    $item['loteria'] . ' - ' . $item['nombre']
                                ) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <div class="col-md-4">

                    <label class="form-label">Tipo de premio</label>

                    <select name="premio" class="form-select">

                        <option value="">Todos los premios</option>

                        <?php foreach ($tiposPremio as $tipo): ?>

                            <option
                                value="<?= htmlspecialchars($tipo) ?>"
                                <?= $premio === $tipo ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($tipo) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <div class="col-md-4">

                    <label class="form-label">Estado</label>

                    <select name="estado" class="form-select">

                        <option value="">Todos los estados</option>

                        <option
                            value="ganadora"
                            <?= $estado === 'ganadora' ? 'selected' : '' ?>
                        >
                            Ganadora
                        </option>

                        <option
                            value="pagada"
                            <?= $estado === 'pagada' ? 'selected' : '' ?>
                        >
                            Pagada
                        </option>

                    </select>

                </div>


                <div class="col-12">

                    <button type="submit" class="btn btn-success">

                        <i class="bi bi-search"></i>
                        Consultar premios

                    </button>

                    <a
                        href="reporte_premios.php"
                        class="btn btn-outline-secondary"
                    >
                        Limpiar filtros
                    </a>

                </div>

            </div>

        </form>

    </div>


    <!-- TABLA DE PREMIOS -->

    <div class="panel">

        <div class="d-flex justify-content-between align-items-center mb-3">

            <h5 class="mb-0">
                <i class="bi bi-list-check"></i>
                Detalle de premios
            </h5>

            <span class="badge bg-success">
                <?= count($premios) ?> registros
            </span>

        </div>

        <div class="table-responsive">

            <table class="table table-hover table-bordered">

                <thead>

                    <tr>
                        <th>Fecha</th>
                        <th>Ticket</th>
                        <th>Lotería</th>
                        <th>Sorteo</th>
                        <th>Modalidad</th>
                        <th>Números</th>
                        <th>Premio</th>
                        <th>Apostado</th>
                        <th>Valor estimado</th>
                        <th>Estado</th>
                    </tr>

                </thead>

                <tbody>

                    <?php if (empty($premios)): ?>

                        <tr>

                            <td colspan="10" class="text-center py-4">

                                <i class="bi bi-inbox fs-2 text-muted"></i>

                                <p class="mb-0 mt-2 text-muted">
                                    No se encontraron premios para esta consulta.
                                </p>

                            </td>

                        </tr>

                    <?php else: ?>

                        <?php foreach ($premios as $fila): ?>

                            <tr>

                                <td>
                                    <?= htmlspecialchars($fila['fecha_sorteo']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($fila['codigo_ticket']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($fila['loteria']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($fila['sorteo']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($fila['modalidad']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($fila['numeros']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($fila['nombre_premio']) ?>
                                </td>

                                <td>
                                    <?= $moneda($fila['monto']) ?>
                                </td>

                                <td class="fw-bold text-success">
                                    <?= $moneda($fila['premio_estimado']) ?>
                                </td>

                                <td>

                                    <?php if ($fila['estado'] === 'pagada'): ?>

                                        <span class="estado pagada">
                                            Pagada
                                        </span>

                                    <?php else: ?>

                                        <span class="estado ganadora">
                                            Ganadora
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

</div>

</body>
</html>
