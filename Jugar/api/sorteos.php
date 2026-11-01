<?php
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json; charset=utf-8');

$dias = [1 => 'lunes', 2 => 'martes', 3 => 'miercoles', 4 => 'jueves',
         5 => 'viernes', 6 => 'sabado', 7 => 'domingo'];

$st = $pdo->prepare(
    "SELECT s.id, s.nombre, s.hora, l.id AS loteria_id, l.nombre AS loteria
     FROM sorteos s
     JOIN loterias l ON l.id = s.loteria_id
     JOIN sorteo_dias d ON d.sorteo_id = s.id AND d.dia = ?
     WHERE s.activo = 1 AND l.activo = 1
     ORDER BY s.hora, l.nombre"
);
$st->execute([$dias[date('N')]]);
echo json_encode($st->fetchAll());