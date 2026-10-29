
<?php
header("Content-Type: application/json; charset=utf-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "ok" => false,
        "mensaje" => "Método no permitido."
    ]);
    exit;
}

require_once __DIR__ . "/conexion.php";

$datos = json_decode(file_get_contents("php://input"), true);
$nombre = trim($datos["nombre"] ?? "");

if ($nombre === "") {
    http_response_code(400);
    echo json_encode([
        "ok" => false,
        "mensaje" => "Escriba el nombre de la lotería."
    ]);
    exit;
}

$sql = "INSERT INTO loterias (nombre) VALUES (?)";
$stmt = $conexion->prepare($sql);

if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        "ok" => false,
        "mensaje" => "No se pudo preparar el registro."
    ]);
    exit;
}

$stmt->bind_param("s", $nombre);

if ($stmt->execute()) {
    echo json_encode([
        "ok" => true,
        "mensaje" => "Lotería registrada correctamente.",
        "id" => $conexion->insert_id,
        "nombre" => $nombre
    ]);
} else {
    if ($stmt->errno === 1062) {
        http_response_code(409);
        $mensaje = "Esta lotería ya está registrada.";
    } else {
        error_log("Error al registrar lotería: " . $stmt->error);
        http_response_code(500);
        $mensaje = "No fue posible registrar la lotería.";
    }

    echo json_encode([
        "ok" => false,
        "mensaje" => $mensaje
    ]);
}

$stmt->close();
$conexion->close();
?>
