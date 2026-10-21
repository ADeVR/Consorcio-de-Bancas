<?php
require_once __DIR__ . "/conexion.php";
require_once __DIR__ . "/funciones.php";
exigirSesion();
$t = tokenCsrf();
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Inicio | Consorcio de Bancas</title>
    <link rel="stylesheet" href="/Seguridad/css/seguridad.css">
</head>

<body>
    <main class="admin-contenedor">
        <header class="barra-superior">
            <div>
                <p class="sobrelinea">CONSORCIO DE BANCAS</p>
                <h1>Menú principal</h1>
                <p>Bienvenido, <?= escapar($_SESSION["usuario"]) ?>.</p>
            </div>
            <form action="/Seguridad/php/cerrar_sesion.php" method="post"><input type="hidden" name="token_csrf" value="<?= escapar($t) ?>"><button class="boton boton-secundario">Cerrar sesión</button></form>
        </header>
        <section class="tarjeta">
            <h2>Sesión iniciada correctamente</h2>
            <p>Esta es una página inicial protegida para agentes. Podemos conectar aquí los módulos reales del proyecto.</p>
        </section>
    </main>
</body>

</html>