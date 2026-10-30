<?php
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json; charset=utf-8');

$sorteoId = isset($_GET['sorteo_id']) ? (int) $_GET['sorteo_id'] : 0;

$st = $pdo->prepare(
    "SELECT id, modalidad, cantidad_numeros, numero_minimo, numero_maximo,
            monto_minimo, monto_maximo
     FROM juegos
     WHERE sorteo_id = ? AND activo = 1
     ORDER BY cantidad_numeros"
);
$st->execute([$sorteoId]);
echo json_encode($st->fetchAll());