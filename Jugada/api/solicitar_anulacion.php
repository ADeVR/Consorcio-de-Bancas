<?php
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json; charset=utf-8');

function fallar($codigo, $texto) {
    http_response_code($codigo);
    echo json_encode(['ok' => false, 'error' => $texto]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    fallar(405, 'Método no permitido.');
}

$datos  = json_decode(file_get_contents('php://input'), true);
$codigo = isset($datos['codigo']) ? strtoupper(trim($datos['codigo'])) : '';
$motivo = isset($datos['motivo']) ? trim($datos['motivo']) : '';

if ($codigo === '') {
    fallar(400, 'Escriba el código del ticket.');
}
$largo = function_exists('mb_strlen') ? mb_strlen($motivo, 'UTF-8') : strlen($motivo);
if ($largo < 5 || $largo > 200) {
    fallar(400, 'El motivo debe tener entre 5 y 200 caracteres.');
}

$st = $pdo->prepare(
    "SELECT id_ticket, id_usuario, estado FROM ticket WHERE codigo_ticket = ?"
);
$st->execute([$codigo]);
$t = $st->fetch();

// Un agente solo puede pedir anular sus propios tickets
if (!$t || ($_SESSION['rol'] !== 'admin' && (int) $t['id_usuario'] !== (int) $_SESSION['usuario_id'])) {
    fallar(404, 'No se encontró un ticket suyo con ese código.');
}
if ($t['estado'] !== 'activo') {
    fallar(409, 'Ese ticket ya está anulado.');
}

$st = $pdo->prepare(
    "SELECT 1 FROM solicitud_anulacion WHERE id_ticket = ? AND estado = 'pendiente'"
);
$st->execute([$t['id_ticket']]);
if ($st->fetch()) {
    fallar(409, 'Ya existe una solicitud pendiente para ese ticket.');
}

try {
    $pdo->prepare(
        "INSERT INTO solicitud_anulacion (id_ticket, id_usuario_solicita, motivo, fecha_hora)
         VALUES (?, ?, ?, ?)"
    )->execute([$t['id_ticket'], $_SESSION['usuario_id'], $motivo, date('Y-m-d H:i:s')]);

    echo json_encode(['ok' => true, 'codigo' => $codigo]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    fallar(500, 'No se pudo registrar la solicitud.');
}