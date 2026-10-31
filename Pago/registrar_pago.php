<?php
// Registra el pago de un ticket ganador.
// El premio lo calcula el servidor con los datos de la base, nunca el navegador.
require_once __DIR__ . "/../Seguridad/php/conexion.php";
require_once __DIR__ . "/../Seguridad/php/funciones.php";

exigirPermisoModulo("Pagos");

header('Content-Type: application/json; charset=utf-8');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function responder($ok, $datos = [], $codigo = 200) {
    http_response_code($codigo);
    echo json_encode(array_merge(["ok" => $ok], $datos));
    exit;
}

// ---- Datos que llegan de la pantalla ----
$numero = trim($_POST['numero'] ?? '');
$metodo = $_POST['metodo'] ?? '';
$cuenta = trim($_POST['cuenta'] ?? '');

// SUPUESTO: el login de Seguridad guarda el id del usuario en $_SESSION['usuario_id'].
// Si usan otro nombre, cámbialo aquí.
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
    responder(false, ["mensaje" => "No se pudo identificar al usuario que registra el pago."], 401);
}

try {
    $conn->begin_transaction();

    // 1. Premio total del ticket, calculado en el servidor
    $stmt = $conn->prepare("
        SELECT SUM(d.monto * pj.valor_por_peso) AS premio
        FROM numero_ganador g
        JOIN jugada_detalle d ON d.id_detalle = g.id_detalle
        JOIN ticket t         ON t.id_ticket  = d.id_ticket
        JOIN premios_juego pj ON pj.id        = g.id_premio
        WHERE t.codigo_ticket = ? AND t.estado = 'activo'
    ");
    $stmt->bind_param("s", $numero);
    $stmt->execute();
    $premio = $stmt->get_result()->fetch_assoc()['premio'] ?? null;
    $stmt->close();

    if ($premio === null) {
        $conn->rollback();
        responder(false, ["mensaje" => "Este boleto no es ganador o está anulado."], 404);
    }

    // 2. Guardar el pago (numero_boleto es único: no permite pagar dos veces)
    $stmt = $conn->prepare("
        INSERT INTO pagos
            (numero_boleto, premio, tipo_pago, cuenta_destino, estado, fecha_pago, hora_pago, usuario_id)
        VALUES (?, ?, ?, ?, 'pagado', CURDATE(), CURTIME(), ?)
    ");
    $stmt->bind_param("sdssi", $numero, $premio, $metodo, $cuenta, $usuarioId);
    $stmt->execute();
    $stmt->close();

    // 3. Marcar las líneas ganadoras del ticket como pagadas
    //    (pendiente de acordar con Jugadas que Pagos haga este cambio)
    $stmt = $conn->prepare("
        UPDATE jugada_detalle d
        JOIN ticket t          ON t.id_ticket  = d.id_ticket
        JOIN numero_ganador g  ON g.id_detalle = d.id_detalle
        SET d.estado = 'pagada'
        WHERE t.codigo_ticket = ?
    ");
    $stmt->bind_param("s", $numero);
    $stmt->execute();
    $stmt->close();

    $conn->commit();

    responder(true, [
        "numero" => $numero,
        "premio" => (float) $premio,
        "metodo" => $metodo,
        "cuenta" => $cuenta
    ]);

} catch (mysqli_sql_exception $e) {
    $conn->rollback();
    if ($e->getCode() === 1062) { // duplicado en numero_boleto
        responder(false, ["mensaje" => "Este boleto ya fue pagado."], 409);
    }
    responder(false, ["mensaje" => "Error al registrar el pago."], 500);
}
