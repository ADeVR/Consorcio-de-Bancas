<?php
// Devuelve en JSON los tickets ganadores (uno por ticket) con su premio total.
// REQUIERE: la tabla numero_ganador (módulo Números Ganadores).
require_once __DIR__ . "/../Seguridad/php/conexion.php";
require_once __DIR__ . "/../Seguridad/php/funciones.php";

exigirPermisoModulo("Pagos");

header('Content-Type: application/json; charset=utf-8');

$sql = "
    SELECT
        t.codigo_ticket                                   AS numero,
        GROUP_CONCAT(DISTINCT j.modalidad SEPARATOR ', ') AS modalidad,
        GROUP_CONCAT(DISTINCT d.numeros   SEPARATOR ', ') AS jugada,
        GROUP_CONCAT(DISTINCT s.nombre    SEPARATOR ', ') AS sorteo,
        SUM(d.monto * pj.valor_por_peso)                  AS premio,
        COALESCE(p.estado, 'pendiente')                   AS estado
    FROM numero_ganador g
    JOIN jugada_detalle d ON d.id_detalle = g.id_detalle
    JOIN ticket t         ON t.id_ticket  = d.id_ticket
    JOIN juegos j         ON j.id         = d.id_tipo
    JOIN sorteos s        ON s.id         = d.id_sorteo
    JOIN premios_juego pj ON pj.id        = g.id_premio
    LEFT JOIN pagos p     ON p.numero_boleto = t.codigo_ticket
    WHERE t.estado = 'activo'
    GROUP BY t.id_ticket, t.codigo_ticket, p.estado
    ORDER BY t.fecha_hora DESC
";

try {
    $filas = $conexion->query($sql)->fetchAll();

    foreach ($filas as &$fila) {
        $fila['premio'] = (float) $fila['premio'];
    }
    unset($fila);

    echo json_encode($filas);

} catch (PDOException $e) {
    error_log('listar_ganadores: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(["error" => "No se pudo consultar los boletos ganadores."]);
}