
<?php
mysqli_report(MYSQLI_REPORT_OFF);

$conexion = new mysqli(
    getenv("DB_HOST"),
    getenv("DB_USER"),
    getenv("DB_PASSWORD"),
    getenv("DB_NAME")
);

if ($conexion->connect_error) {
    error_log("Error de conexión MySQL: " . $conexion->connect_error);
    http_response_code(500);
    exit("No fue posible conectar con la base de datos.");
}

$conexion->set_charset("utf8mb4");
?>
