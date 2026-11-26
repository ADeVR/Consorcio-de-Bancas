<?php
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json; charset=utf-8');

$sql = "SELECT s.id_solicitud, t.codigo_ticket, s.motivo, s.fecha_hora,
               s.estado, u.usuario AS solicita
        FROM solicitud_anulacion s
        JOIN ticket t   ON t.id_ticket = s.id_ticket
        JOIN usuarios u ON u.id = s.id_usuario_solicita";
$params = [];

if ($_SESSION['rol'] !== 'admin') {
    $sql .= " WHERE s.id_usuario_solicita = ?";
    $params[] = $_SESSION['usuario_id'];
}
$sql .= " ORDER BY s.id_solicitud DESC LIMIT 100";

$st = $pdo->prepare($sql);
$st->execute($params);
$lista = $st->fetchAll();

// NUEVO: avisar al JavaScript cuáles solicitudes puede resolver este usuario
// (solo el admin, y solo las que siguen pendientes)
$esAdmin = ($_SESSION['rol'] === 'admin');
foreach ($lista as &$s) {
    $s['puede_resolver'] = $esAdmin && $s['estado'] === 'pendiente';
}
unset($s);

echo json_encode($lista);
