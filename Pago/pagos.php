<?php
require_once __DIR__ . "/../Seguridad/php/conexion.php";
require_once __DIR__ . "/../Seguridad/php/funciones.php";

exigirPermisoModulo("Pagos");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagos</title>
    <meta name="csrf" content="<?= escapar(tokenCsrf()) ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../estilosglobal.css">
    <link rel="stylesheet" href="estilos.css">

</head>

<body>
  <!-- Barra superior del módulo -->
    <div class="barra-superior">
        <span class="barra-superior-titulo">Sistema de Consorcio de Bancas</span>

        <div class="barra-superior-acciones">
           <span class="barra-superior-usuario">Módulo 6 - Pagos | <?= escapar($_SESSION["usuario"] ?? "") ?></span>

             <a class="btn btn-outline-secondary btn-sm" href="/ConsorcioDeBancas/Seguridad/php/inicio.php">Panel</a>

            <?php if (tienePermisoModulo($conexion, "Jugar")): ?>
                <a class="btn btn-outline-secondary btn-sm" href="/ConsorcioDeBancas/Jugar/">Jugar</a>
            <?php endif; ?>

            <form method="post" action="/ConsorcioDeBancas/Seguridad/php/cerrar_sesion.php" class="d-inline">
                <input type="hidden" name="token_csrf" value="<?= escapar(tokenCsrf()) ?>">
                <button type="submit" class="btn btn-outline-secondary btn-sm">Cerrar sesión</button>
            </form>
        </div>
    </div>
    <!-- fin de la barra superior del módulo -->
    <header class="masthead">
        <img src="image/logo.jpeg" alt="logo de modulo" class="logo">
    </header>
 
    <!-- Aqui se encuentra el formulario para consultar el boleto y realizar el pago del premio correspondiente. -->
    <h1 class="ticket-title">Pantalla de Pagos</h1>

    <div class="ticket">

        <section>
            <h2>Boletos ganadores</h2>

            <div class="form-group">
                <label for="numero-boleto">Buscar boleto:</label>
                <input
                    type="search"
                    id="numero-boleto"
                    class="form-control buscar-boleto"
                    placeholder="Escribe el número del boleto">
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Número</th>
                            <th scope="col">Modalidad</th>
                            <th scope="col">Jugada</th>
                            <th scope="col">Sorteo asociado</th>
                            <th scope="col">Estado</th>
                            <th scope="col">Premio</th>
                            <th scope="col">Método de pago</th>
                            <th scope="col">Acción</th>
                        </tr>
                    </thead>
                    <tbody id="lista-boletos"></tbody>
                </table>
            </div>

            <p id="resultado-reclamacion" class="resultado"></p>
        </section>

        <section>
            <h2>Comprobante</h2>
            <div id="comprobante" class="comprobante-box"></div>
        </section>

        
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="Script.js"></script>
</body>
</html>

