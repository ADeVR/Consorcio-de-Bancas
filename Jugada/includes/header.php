<?php require_once __DIR__ . '/auth.php'; ?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Módulo 4 - Jugar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../estilosglobal.css">
<link rel="stylesheet" href="estilos.css">
</head>
<body>

<nav class="navbar navbar-light bg-white border-bottom">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold text-dark" href="index.php">
            Sistema de Consorcio de Bancas
        </a>
        <span class="text-dark">
            Módulo 4 - Jugar | <?= htmlspecialchars($_SESSION['usuario']) ?>
        </span>
    </div>
</nav>

<div class="container mt-5">