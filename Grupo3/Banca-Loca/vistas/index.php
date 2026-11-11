
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Centro de Reportes | Banca Loca</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
          rel="stylesheet">

    <!-- Estilos propios -->
    <link rel="stylesheet" href="/banca_loca/reportes.css?v=5">

</head>

<body>

<main class="container-fluid reportes-container">

    <!-- PANEL DE BIENVENIDA -->

    <section class="welcome-panel">

        <div>

            <span class="welcome-label">
                PANEL DE ADMINISTRACIÓN
            </span>

            <h2>GRUPO->3 ¡CENTRO DE REPORTES!</h2>

            <p>
                Consulta las operaciones, ventas, sorteos y movimientos
                financieros de tu sistema de loterías.
            </p>

        </div>

        <i class="bi bi-clipboard-data welcome-icon"></i>

    </section>


    <!-- MÓDULOS DISPONIBLES -->

    <section class="modules-section">

        <div class="section-heading">

            <h3>Módulos disponibles</h3>

            <p>
                Selecciona el tipo de información que deseas consultar.
            </p>

        </div>


        <div class="row g-4">


            <!-- REPORTE DE VENTAS -->

            <div class="col-xl-4 col-md-6">

                <article class="report-card">

                    <div class="report-icon icon-blue">
                        <i class="bi bi-cart-check"></i>
                    </div>

                    <h4>Reporte de ventas</h4>

                    <p>
                        Consulta las ventas por fecha, usuario,
                        lotería, sorteo y modalidad.
                    </p>

                    <a href="/banca_loca/reporte_ventas.php"
                       class="btn btn-primary mt-3">

                        <i class="bi bi-box-arrow-in-right me-1"></i>
                        Abrir reporte

                    </a>

                </article>

            </div>


            <!-- REPORTE DE TICKETS -->

            <div class="col-xl-4 col-md-6">

                <article class="report-card">

                    <div class="report-icon icon-green">
                        <i class="bi bi-ticket-perforated"></i>
                    </div>

                    <h4>Reporte de tickets</h4>

                    <p>
                        Consulta tickets activos, anulados,
                        historial y detalles de cada boleto.
                    </p>

                    <span class="report-status">

                        <a href="/banca_loca/reporte_tickets.php"
                           class="btn btn-success mt-3">

                            <i class="bi bi-box-arrow-in-right me-1"></i>
                            Abrir reporte

                        </a>

                    </span>

                </article>

            </div>


            <!-- REPORTE DE SORTEOS -->

            <div class="col-xl-4 col-md-6">

                <article class="report-card">

                    <div class="report-icon icon-orange">
                        <i class="bi bi-trophy"></i>
                    </div>

                    <h4>Reporte de sorteos</h4>

                    <p>
                        Consulta sorteos, fechas, resultados
                        y números ganadores.
                    </p>

                    <span class="report-status">

                        <a href="/banca_loca/reporte_sorteos.php"
                           class="btn btn-warning mt-3">

                            <i class="bi bi-box-arrow-in-right me-1"></i>
                            Abrir reporte

                        </a>

                    </span>

                </article>

            </div>


            <!-- REPORTE DE PREMIOS -->

            <div class="col-xl-4 col-md-6">

                <article class="report-card">

                    <div class="report-icon icon-purple">
                        <i class="bi bi-award"></i>
                    </div>

                    <h4>Reporte de premios</h4>

                    <p>
                        Consulta premios generados,
                        modalidades e importes correspondientes.
                    </p>

                    <a href="/banca_loca/reporte_premios.php"
                       class="btn btn-success mt-3">

                        <i class="bi bi-box-arrow-in-right me-1"></i>
                        Abrir reporte

                    </a>

                </article>

            </div>


            <!-- REPORTE DE PAGOS -->

            <div class="col-xl-4 col-md-6">

                <article class="report-card">

                    <div class="report-icon icon-red">
                        <i class="bi bi-wallet2"></i>
                    </div>

                    <h4>Reporte de pagos</h4>

                    <p>
                        Consulta pagos pendientes,
                        realizados y su historial.
                    </p>

                    <a href="/banca_loca/reporte_pagos.php"
                       class="btn btn-danger mt-3">

                        <i class="bi bi-box-arrow-in-right me-1"></i>
                        Abrir reporte

                    </a>

                </article>

            </div>


            <!-- REPORTE GENERAL -->

            <div class="col-xl-4 col-md-6">

                <article class="report-card">

                    <div class="report-icon icon-dark">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>

                    <h4>Reporte general</h4>

                    <p>
                        Visualiza el resumen financiero,
                        ventas, tickets, premios y pagos del sistema.
                    </p>

                    <a href="/banca_loca/reporte_general.php"
                       class="btn btn-dark mt-3">

                        <i class="bi bi-box-arrow-in-right me-1"></i>
                        Abrir reporte

                    </a>

                </article>

            </div>


        </div>

    </section>


    <!-- PIE DE PÁGINA -->

    <footer class="reportes-footer">

        <span>
            Sistema de Administración de Loterías
        </span>

        <span>
            Centro de Reportes
        </span>

    </footer>

</main>

</body>
</html>
