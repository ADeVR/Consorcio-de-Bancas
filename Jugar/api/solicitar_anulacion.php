<?php
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json; charset=utf-8');

// Minutos que tiene el vendedor para anular sin pedir permiso al admin
const MINUTOS_LIMITE = 5;

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

// NUEVO: también traemos fecha_hora para saber cuántos minutos tiene el ticket
$st = $pdo->prepare(
    "SELECT id_ticket, id_usuario, estado, fecha_hora FROM ticket WHERE codigo_ticket = ?"
);
$st->execute([$codigo]);
$t = $st->fetch();

$esAdmin = ($_SESSION['rol'] === 'admin');

// Un agente solo puede pedir anular sus propios tickets
if (!$t || (!$esAdmin && (int) $t['id_usuario'] !== (int) $_SESSION['usuario_id'])) {
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

// NUEVO: ¿cuántos minutos han pasado desde que se hizo el ticket?
$ahora   = date('Y-m-d H:i:s');
$minutos = (time() - strtotime($t['fecha_hora'])) / 60;

// NUEVO: se anula al instante si está dentro del límite o si lo pide un admin
$anularYa = ($minutos <= MINUTOS_LIMITE) || $esAdmin;

try {
    if ($anularYa) {
        // Las 3 operaciones van juntas: o se hacen todas, o ninguna
        $pdo->beginTransaction();

        // 1) Guardar la solicitud ya aprobada (queda en el historial)
        $pdo->prepare(
            "INSERT INTO solicitud_anulacion
                (id_ticket, id_usuario_solicita, motivo, fecha_hora,
                 id_usuario_revisa, fecha_revision, estado)
             VALUES (?, ?, ?, ?, ?, ?, 'aprobada')"
        )->execute([
            $t['id_ticket'],
            $_SESSION['usuario_id'],
            $motivo,
            $ahora,
            $esAdmin ? $_SESSION['usuario_id'] : null, // null = aprobada automática
            $ahora,
        ]);

        // 2) Marcar el ticket como anulado
        $pdo->prepare("UPDATE ticket SET estado = 'anulado' WHERE id_ticket = ?")
            ->execute([$t['id_ticket']]);

        // 3) Marcar sus jugadas como anuladas
        $pdo->prepare("UPDATE jugada_detalle SET estado = 'anulada' WHERE id_ticket = ?")
            ->execute([$t['id_ticket']]);

        $pdo->commit();
        echo json_encode(['ok' => true, 'codigo' => $codigo, 'estado' => 'aprobada']);
    } else {
        // Pasaron más de 5 minutos: queda pendiente para que el admin decida
        $pdo->prepare(
            "INSERT INTO solicitud_anulacion (id_ticket, id_usuario_solicita, motivo, fecha_hora)
             VALUES (?, ?, ?, ?)"
        )->execute([$t['id_ticket'], $_SESSION['usuario_id'], $motivo, $ahora]);

        echo json_encode(['ok' => true, 'codigo' => $codigo, 'estado' => 'pendiente']);
    }
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack(); // deshace lo que se haya hecho a medias
    }
    error_log($e->getMessage());
    fallar(500, 'No se pudo registrar la anulación.');
}