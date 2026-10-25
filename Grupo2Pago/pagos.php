<?php
require_once _DIR_ . "/../Seguridad/php/conexion.php";
require_once _DIR_ . "/../Seguridad/php/funciones.php";

exigirPermisoModulo("Pagos");

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../estilosglobal.css">
    <link rel="stylesheet" href="estilos.css">

</head>

<body>
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

