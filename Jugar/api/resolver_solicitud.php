<?php
// El admin aprueba o rechaza una solicitud de anulación pendiente
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

// Solo el admin puede decidir
if ($_SESSION['rol'] !== 'admin') {
    fallar(403, 'Solo el administrador puede aprobar o rechazar solicitudes.');
}

$datos  = json_decode(file_get_contents('php://input'), true);
$id     = isset($datos['id_solicitud']) ? (int) $datos['id_solicitud'] : 0;
$accion = isset($datos['accion']) ? $datos['accion'] : '';

if ($id <= 0 || !in_array($accion, ['aprobar', 'rechazar'], true)) {
    fallar(400, 'Datos inválidos.');
}

$ahora = date('Y-m-d H:i:s');

try {
    $pdo->beginTransaction();

    // FOR UPDATE: "aparta" la fila para que nadie más la cambie mientras tanto
    $st = $pdo->prepare(
        "SELECT id_ticket, estado FROM solicitud_anulacion
         WHERE id_solicitud = ? FOR UPDATE"
    );
    $st->execute([$id]);
    $s = $st->fetch();

    if (!$s) {
        $pdo->rollBack();
        fallar(404, 'La solicitud no existe.');
    }
    if ($s['estado'] !== 'pendiente') {
        $pdo->rollBack();
        fallar(409, 'Esa solicitud ya fue resuelta.');
    }

    $nuevoEstado = ($accion === 'aprobar') ? 'aprobada' : 'rechazada';

    // 1) Guardar la decisión: estado, quién revisó y cuándo
    $pdo->prepare(
        "UPDATE solicitud_anulacion
         SET estado = ?, id_usuario_revisa = ?, fecha_revision = ?
         WHERE id_solicitud = ?"
    )->execute([$nuevoEstado, $_SESSION['usuario_id'], $ahora, $id]);

    // 2) Solo si se aprueba: anular el ticket y sus jugadas
    if ($accion === 'aprobar') {
        $pdo->prepare("UPDATE ticket SET estado = 'anulado' WHERE id_ticket = ?")
            ->execute([$s['id_ticket']]);

        $pdo->prepare("UPDATE jugada_detalle SET estado = 'anulada' WHERE id_ticket = ?")
            ->execute([$s['id_ticket']]);
    }

    $pdo->commit();
    echo json_encode(['ok' => true, 'estado' => $nuevoEstado]);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log($e->getMessage());
    fallar(500, 'No se pudo resolver la solicitud.');
}
