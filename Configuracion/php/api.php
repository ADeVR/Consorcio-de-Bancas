<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../Seguridad/php/conexion.php';
require_once __DIR__ . '/../../Seguridad/php/funciones.php';

function responder(int $estado, array $datos): never
{
    http_response_code($estado);
    echo json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

if (empty($_SESSION['usuario_id'])) {
    responder(401, ['ok' => false, 'mensaje' => 'Inicie sesión para usar Configuración.']);
}

if (($_SESSION['rol'] ?? '') !== 'admin') {
    responder(403, ['ok' => false, 'mensaje' => 'Solo un administrador puede modificar la configuración.']);
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $loterias = $conexion->query(
            'SELECT id, nombre FROM loterias WHERE activo = 1 ORDER BY nombre'
        )->fetchAll();

        $sorteos = $conexion->query(
            "SELECT s.id, s.loteria_id, l.nombre AS loteria, s.nombre, s.hora
             FROM sorteos s
             JOIN loterias l ON l.id = s.loteria_id
             WHERE s.activo = 1 AND l.activo = 1
             ORDER BY l.nombre, s.hora, s.nombre"
        )->fetchAll();

        $diasPorSorteo = [];
        foreach ($conexion->query('SELECT sorteo_id, dia FROM sorteo_dias')->fetchAll() as $fila) {
            $diasPorSorteo[(int)$fila['sorteo_id']][] = $fila['dia'];
        }
        foreach ($sorteos as &$sorteo) {
            $sorteo['dias'] = $diasPorSorteo[(int)$sorteo['id']] ?? [];
            $sorteo['hora'] = substr((string)$sorteo['hora'], 0, 5);
        }
        unset($sorteo);

        $juegos = $conexion->query(
            "SELECT j.id, j.sorteo_id, j.modalidad, j.cantidad_numeros,
                    j.numero_minimo, j.numero_maximo, j.monto_minimo, j.monto_maximo,
                    s.nombre AS sorteo, l.nombre AS loteria
             FROM juegos j
             JOIN sorteos s ON s.id = j.sorteo_id
             JOIN loterias l ON l.id = s.loteria_id
             WHERE j.activo = 1 AND s.activo = 1 AND l.activo = 1
             ORDER BY l.nombre, s.nombre, j.modalidad"
        )->fetchAll();

        $premiosPorJuego = [];
        foreach ($conexion->query(
            'SELECT juego_id, nombre_premio, valor_por_peso FROM premios_juego ORDER BY id'
        )->fetchAll() as $premio) {
            $premiosPorJuego[(int)$premio['juego_id']][] = [
                'nombre' => $premio['nombre_premio'],
                'valor' => (float)$premio['valor_por_peso']
            ];
        }
        foreach ($juegos as &$juego) {
            $juego['premios'] = $premiosPorJuego[(int)$juego['id']] ?? [];
        }
        unset($juego);

        responder(200, [
            'ok' => true,
            'loterias' => $loterias,
            'sorteos' => $sorteos,
            'juegos' => $juegos
        ]);
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        responder(405, ['ok' => false, 'mensaje' => 'Método no permitido.']);
    }

    $datos = json_decode(file_get_contents('php://input'), true);
    if (!is_array($datos)) {
        responder(400, ['ok' => false, 'mensaje' => 'La solicitud no contiene datos válidos.']);
    }

    $accion = $datos['accion'] ?? '';

    if ($accion === 'loteria') {
        $nombre = trim((string)($datos['nombre'] ?? ''));
        if ($nombre === '' || mb_strlen($nombre) > 100) {
            responder(422, ['ok' => false, 'mensaje' => 'Escriba un nombre de lotería válido (máximo 100 caracteres).']);
        }

        $stmt = $conexion->prepare('INSERT INTO loterias (nombre) VALUES (?)');
        $stmt->execute([$nombre]);
        responder(201, ['ok' => true, 'mensaje' => 'Lotería guardada correctamente.']);
    }

    if ($accion === 'sorteo') {
        $loteriaId = filter_var($datos['loteria_id'] ?? null, FILTER_VALIDATE_INT);
        $nombre = trim((string)($datos['nombre'] ?? ''));
        $hora = (string)($datos['hora'] ?? '');
        $diasPermitidos = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
        $diasEntrada = $datos['dias'] ?? [];
        $dias = [];
        foreach (is_array($diasEntrada) ? $diasEntrada : [] as $dia) {
            $normalizado = mb_strtolower(trim((string)$dia));
            $normalizado = strtr($normalizado, ['é' => 'e', 'á' => 'a']);
            if (in_array($normalizado, $diasPermitidos, true)) {
                $dias[$normalizado] = $normalizado;
            }
        }
        if (!$loteriaId || $nombre === '' || !preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $hora) || !$dias) {
            responder(422, ['ok' => false, 'mensaje' => 'Revise la lotería, el nombre, la hora y los días seleccionados.']);
        }

        $conexion->beginTransaction();
        $stmt = $conexion->prepare('INSERT INTO sorteos (loteria_id, nombre, hora) VALUES (?, ?, ?)');
        $stmt->execute([$loteriaId, $nombre, $hora . ':00']);
        $sorteoId = (int)$conexion->lastInsertId();
        $stmtDia = $conexion->prepare('INSERT INTO sorteo_dias (sorteo_id, dia) VALUES (?, ?)');
        foreach ($dias as $dia) {
            $stmtDia->execute([$sorteoId, $dia]);
        }
        $conexion->commit();
        responder(201, ['ok' => true, 'mensaje' => 'Sorteo guardado correctamente.']);
    }

    if ($accion === 'juego') {
        $sorteoId = filter_var($datos['sorteo_id'] ?? null, FILTER_VALIDATE_INT);
        $modalidad = trim((string)($datos['modalidad'] ?? ''));
        $cantidad = filter_var($datos['cantidad'] ?? null, FILTER_VALIDATE_INT);
        $minimo = trim((string)($datos['minimo'] ?? ''));
        $maximo = trim((string)($datos['maximo'] ?? ''));
        $montoMinimo = filter_var($datos['monto_minimo'] ?? null, FILTER_VALIDATE_FLOAT);
        $montoMaximo = filter_var($datos['monto_maximo'] ?? null, FILTER_VALIDATE_FLOAT);
        $premios = $datos['premios'] ?? [];

        if (!$sorteoId || $modalidad === '' || !$cantidad || $cantidad < 1 || $minimo === '' || $maximo === ''
            || $montoMinimo === false || $montoMaximo === false || $montoMinimo <= 0 || $montoMaximo < $montoMinimo
            || !is_array($premios)) {
            responder(422, ['ok' => false, 'mensaje' => 'Revise la modalidad, el rango y los montos configurados.']);
        }

        $conexion->beginTransaction();
        $stmt = $conexion->prepare(
            'INSERT INTO juegos (sorteo_id, modalidad, cantidad_numeros, numero_minimo, numero_maximo, monto_minimo, monto_maximo)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$sorteoId, $modalidad, $cantidad, $minimo, $maximo, $montoMinimo, $montoMaximo]);
        $juegoId = (int)$conexion->lastInsertId();
        $stmtPremio = $conexion->prepare(
            'INSERT INTO premios_juego (juego_id, nombre_premio, valor_por_peso) VALUES (?, ?, ?)'
        );
        $nombresPremios = ['Primera', 'Segunda', 'Tercera', 'Palé', 'Tripleta'];
        foreach ($premios as $premio) {
            $nombrePremio = trim((string)($premio['nombre'] ?? ''));
            $valor = filter_var($premio['valor'] ?? null, FILTER_VALIDATE_FLOAT);
            if (!in_array($nombrePremio, $nombresPremios, true) || $valor === false || $valor < 0) {
                throw new InvalidArgumentException('Uno de los valores de premios no es válido.');
            }
            $stmtPremio->execute([$juegoId, $nombrePremio, $valor]);
        }
        $conexion->commit();
        responder(201, ['ok' => true, 'mensaje' => 'Modalidad y premios guardados correctamente.']);
    }

    responder(400, ['ok' => false, 'mensaje' => 'Tipo de registro desconocido.']);
} catch (PDOException $e) {
    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }
    error_log('Error en Configuración: ' . $e->getMessage());
    if ($e->getCode() === '23000') {
        responder(409, ['ok' => false, 'mensaje' => 'Ese registro ya existe o hace referencia a una opción inválida.']);
    }
    responder(500, ['ok' => false, 'mensaje' => 'No se pudo guardar o cargar la configuración. Verifique la estructura de la base de datos.']);
} catch (Throwable $e) {
    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }
    error_log('Error en Configuración: ' . $e->getMessage());
    responder(422, ['ok' => false, 'mensaje' => $e->getMessage()]);
}
