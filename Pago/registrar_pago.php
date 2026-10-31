<?php
// Registra el pago de un ticket ganador.
// El premio lo calcula el servidor con los datos de la base, nunca el navegador.
require_once __DIR__ . "/../Seguridad/php/conexion.php";
require_once __DIR__ . "/../Seguridad/php/funciones.php";

exigirPermisoModulo("Pagos");

header('Content-Type: application/json; charset=utf-8');

function responder(bool $ok, array $datos = [], int $codigo = 200): void
{
    http_response_code($codigo);
    echo json_encode(array_merge(["ok" => $ok], $datos));
    exit;
}

// ---- Solo POST y con token CSRF válido ----
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(false, ["mensaje" => "Método no permitido."], 405);
}
if (!csrfValido()) {
    responder(false, ["mensaje" => "Sesión no válida. Recarga la página."], 403);
}

// ---- Datos que llegan de la pantalla ----
$numero    = trim($_POST['numero'] ?? '');
$metodo    = $_POST['metodo'] ?? '';
$cuenta    = trim($_POST['cuenta'] ?? '');
$usuarioId = (int) ($_SESSION['usuario_id'] ?? 0);

if ($numero === '') {
    responder(false, ["mensaje" => "Falta el número de boleto."], 400);
}
if (!in_array($metodo, ['efectivo', 'transferencia'], true)) {
    responder(false, ["mensaje" => "Método de pago no válido."], 400);
}
if ($metodo === 'transferencia' && $cuenta === '') {
    responder(false, ["mensaje" => "Debes escribir la cuenta del cliente."], 400);
}
if ($metodo === 'efectivo') {
    $cuenta = null;
}
if ($usuarioId <= 0) {
    responder(false, ["mensaje" => "No se pudo identificar al usuario."], 401);
}

try {
    $conexion->beginTransaction();

    // 1. Premio total del ticket, calculado en el servidor
    $q = $conexion->prepare("
        SELECT SUM(d.monto * pj.valor_por_peso) AS premio
        FROM numero_ganador g
        JOIN jugada_detalle d ON d.id_detalle = g.id_detalle
        JOIN ticket t         ON t.id_ticket  = d.id_ticket
        JOIN premios_juego pj ON pj.id        = g.id_premio
        WHERE t.codigo_ticket = ? AND t.estado = 'activo'
    ");
    $q->execute([$numero]);
    $premio = $q->fetchColumn();

    if ($premio === false || $premio === null) {
        $conexion->rollBack();
        responder(false, ["mensaje" => "Este boleto no es ganador o está anulado."], 404);
    }

    // 2. Guardar el pago (numero_boleto es único: no permite pagar dos veces)
    $q = $conexion->prepare("
        INSERT INTO pagos
            (numero_boleto, premio, tipo_pago, cuenta_destino, estado, fecha_pago, hora_pago, usuario_id)
        VALUES (?, ?, ?, ?, 'pagado', CURDATE(), CURTIME(), ?)
    ");
    $q->execute([$numero, $premio, $metodo, $cuenta, $usuarioId]);

    // 3. Marcar las líneas ganadoras del ticket como pagadas
    $q = $conexion->prepare("
        UPDATE jugada_detalle d
        JOIN ticket t         ON t.id_ticket  = d.id_ticket
        JOIN numero_ganador g ON g.id_detalle = d.id_detalle
        SET d.estado = 'pagada'
        WHERE t.codigo_ticket = ?
    ");
    $q->execute([$numero]);

    $conexion->commit();

    responder(true, [
        "numero" => $numero,
        "premio" => (float) $premio,
        "metodo" => $metodo,
        "cuenta" => $cuenta
    ]);

} catch (PDOException $e) {
    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }
    // 1062 = valor duplicado en numero_boleto: ya se pagó
    if (($e->errorInfo[1] ?? 0) === 1062) {
        responder(false, ["mensaje" => "Este boleto ya fue pagado."], 409);
    }
    error_log('registrar_pago: ' . $e->getMessage());
    responder(false, ["mensaje" => "Error al registrar el pago."], 500);
}