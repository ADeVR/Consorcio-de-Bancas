<?php
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json; charset=utf-8');

$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : '';
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    $fecha = date('Y-m-d');
}
$codigo = isset($_GET['codigo']) ? trim($_GET['codigo']) : '';

$sql = "SELECT t.id_ticket, t.codigo_ticket, t.fecha_hora, t.total, t.estado, u.usuario
        FROM ticket t
        JOIN usuarios u ON u.id = t.id_usuario
        WHERE DATE(t.fecha_hora) = ?";
$params = [$fecha];

// Un agente solo ve sus tickets; el admin ve todos
if ($_SESSION['rol'] !== 'admin') {
    $sql .= " AND t.id_usuario = ?";
    $params[] = $_SESSION['usuario_id'];
}
if ($codigo !== '') {
    $sql .= " AND t.codigo_ticket LIKE ?";
    $params[] = '%' . $codigo . '%';
}
$sql .= " ORDER BY t.id_ticket DESC LIMIT 200";

$st = $pdo->prepare($sql);
$st->execute($params);
$tickets = $st->fetchAll();

if ($tickets) {
    $ids = array_column($tickets, 'id_ticket');
    $in  = implode(',', array_fill(0, count($ids), '?'));

    $sd = $pdo->prepare(
        "SELECT d.id_ticket, d.numeros, d.monto, d.estado,
                j.modalidad, l.nombre AS loteria, s.nombre AS sorteo
         FROM jugada_detalle d
         JOIN juegos j   ON j.id = d.id_tipo
         JOIN loterias l ON l.id = d.id_loteria
         JOIN sorteos s  ON s.id = d.id_sorteo
         WHERE d.id_ticket IN ($in)
         ORDER BY d.id_detalle"
    );
    $sd->execute($ids);

    $porTicket = [];
    foreach ($sd->fetchAll() as $r) {
        $porTicket[$r['id_ticket']][] = $r;
    }
    foreach ($tickets as &$t) {
        $t['jugadas'] = isset($porTicket[$t['id_ticket']]) ? $porTicket[$t['id_ticket']] : [];
    }
    unset($t);
}

echo json_encode($tickets);