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

$datos   = json_decode(file_get_contents('php://input'), true);
$jugadas = (isset($datos['jugadas']) && is_array($datos['jugadas'])) ? $datos['jugadas'] : [];

if (count($jugadas) === 0)   fallar(400, 'El ticket no tiene jugadas.');
if (count($jugadas) > 100)   fallar(400, 'Máximo 100 jugadas por ticket.');

$dias = [1 => 'lunes', 2 => 'martes', 3 => 'miercoles', 4 => 'jueves',
         5 => 'viernes', 6 => 'sabado', 7 => 'domingo'];
$hoy      = $dias[date('N')];
$fechaHoy = date('Y-m-d');

// El juego debe existir, estar activo y su sorteo debe jugarse hoy
$stJuego = $pdo->prepare(
    "SELECT j.id, j.cantidad_numeros, j.numero_minimo, j.numero_maximo,
            j.monto_minimo, j.monto_maximo, s.id AS sorteo_id, s.loteria_id
     FROM juegos j
     JOIN sorteos s   ON s.id = j.sorteo_id AND s.activo = 1
     JOIN loterias l  ON l.id = s.loteria_id AND l.activo = 1
     JOIN sorteo_dias d ON d.sorteo_id = s.id AND d.dia = ?
     WHERE j.id = ? AND j.activo = 1"
);
$stBloq = $pdo->prepare(
    "SELECT 1 FROM numero_bloqueado
     WHERE id_loteria = ? AND id_tipo = ? AND numeros = ? AND fecha = ?"
);

$validas = [];
$total   = 0;

foreach ($jugadas as $i => $j) {
    $n      = $i + 1;
    $idTipo = isset($j['id_tipo']) ? (int) $j['id_tipo'] : 0;
    $nums   = (isset($j['numeros']) && is_array($j['numeros'])) ? $j['numeros'] : [];
    $monto  = (isset($j['monto']) && is_numeric($j['monto'])) ? round((float) $j['monto'], 2) : 0;

    $stJuego->execute([$hoy, $idTipo]);
    $g = $stJuego->fetch();
    if (!$g) {
        fallar(422, "Jugada $n: el sorteo o el tipo de jugada no está disponible hoy.");
    }

    if (count($nums) !== (int) $g['cantidad_numeros']) {
        fallar(422, "Jugada $n: debe tener {$g['cantidad_numeros']} número(s).");
    }

    $limpios = [];
    foreach ($nums as $x) {
        $x = (string) $x;
        if (!preg_match('/^\d{2}$/', $x)
            || (int) $x < (int) $g['numero_minimo']
            || (int) $x > (int) $g['numero_maximo']) {
            fallar(422, "Jugada $n: los números deben estar entre {$g['numero_minimo']} y {$g['numero_maximo']}.");
        }
        $limpios[] = $x;
    }
    if (count(array_unique($limpios)) !== count($limpios)) {
        fallar(422, "Jugada $n: los números no pueden repetirse.");
    }

    if ($monto < (float) $g['monto_minimo'] || $monto > (float) $g['monto_maximo']) {
        fallar(422, "Jugada $n: el monto debe estar entre {$g['monto_minimo']} y {$g['monto_maximo']}.");
    }

    $texto = implode('-', $limpios);
    $stBloq->execute([$g['loteria_id'], $idTipo, $texto, $fechaHoy]);
    if ($stBloq->fetch()) {
        fallar(422, "Jugada $n: el número $texto está bloqueado.");
    }

    $validas[] = [
        'id_loteria' => $g['loteria_id'],
        'id_sorteo'  => $g['sorteo_id'],
        'id_tipo'    => $idTipo,
        'numeros'    => $texto,
        'monto'      => $monto
    ];
    $total += $monto;
}

// Guardar todo junto: si algo falla, no se guarda nada
try {
    $pdo->beginTransaction();

        $pdo->prepare(
        "INSERT INTO ticket (codigo_ticket, id_apertura, id_usuario, fecha_hora, total)
         VALUES (?, NULL, ?, ?, ?)"
    )->execute([uniqid('TMP'), $_SESSION['usuario_id'], date('Y-m-d H:i:s'), $total]);  

    $idTicket = (int) $pdo->lastInsertId();
    $codigo   = 'TK' . str_pad($idTicket, 8, '0', STR_PAD_LEFT);

    $pdo->prepare("UPDATE ticket SET codigo_ticket = ? WHERE id_ticket = ?")
        ->execute([$codigo, $idTicket]);

    $ins = $pdo->prepare(
        "INSERT INTO jugada_detalle (id_ticket, id_loteria, id_sorteo, id_tipo, numeros, monto)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    foreach ($validas as $v) {
        $ins->execute([$idTicket, $v['id_loteria'], $v['id_sorteo'],
                       $v['id_tipo'], $v['numeros'], $v['monto']]);
    }

    $pdo->commit();
    echo json_encode(['ok' => true, 'codigo' => $codigo, 'total' => $total]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log($e->getMessage());
    fallar(500, 'No se pudo guardar el ticket.');
}