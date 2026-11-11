
<?php

require_once __DIR__ . '/../conexion.php';

class SorteoModel
{
    private $conexion;

    public function __construct()
    {
        global $conexion;
        $this->conexion = $conexion;
    }

    // Registrar un sorteo
    public function registrar($loteria_id, $nombre, $hora)
    {
        $nombre = trim($nombre);

        if ($nombre === '' || $hora === '') {
            return false;
        }

        $sql = "INSERT INTO sorteos
                (loteria_id, nombre, hora)
                VALUES (?, ?, ?)";

        $stmt = $this->conexion->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "iss",
            $loteria_id,
            $nombre,
            $hora
        );

        $resultado = $stmt->execute();

        $stmt->close();

        return $resultado;
    }

    // Obtener todos los sorteos con su lotería
    public function obtenerTodos()
    {
        $sql = "SELECT
                    s.id,
                    s.loteria_id,
                    s.nombre,
                    s.hora,
                    s.activo,
                    s.fecha_creacion,
                    l.nombre AS nombre_loteria
                FROM sorteos s
                INNER JOIN loterias l
                    ON l.id = s.loteria_id
                ORDER BY s.hora ASC";

        $resultado = $this->conexion->query($sql);

        return $resultado
            ? $resultado->fetch_all(MYSQLI_ASSOC)
            : [];
    }

    // Obtener sorteos activos
    public function obtenerActivos()
    {
        $sql = "SELECT
                    s.id,
                    s.loteria_id,
                    s.nombre,
                    s.hora,
                    l.nombre AS nombre_loteria
                FROM sorteos s
                INNER JOIN loterias l
                    ON l.id = s.loteria_id
                WHERE s.activo = 1
                  AND l.activo = 1
                ORDER BY s.hora ASC";

        $resultado = $this->conexion->query($sql);

        return $resultado
            ? $resultado->fetch_all(MYSQLI_ASSOC)
            : [];
    }

    // Obtener sorteos de una lotería
    public function obtenerPorLoteria($loteria_id)
    {
        $sql = "SELECT *
                FROM sorteos
                WHERE loteria_id = ?
                ORDER BY hora ASC";

        $stmt = $this->conexion->prepare($sql);

        if (!$stmt) {
            return [];
        }

        $stmt->bind_param("i", $loteria_id);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $sorteos = $resultado->fetch_all(MYSQLI_ASSOC);

        $stmt->close();

        return $sorteos;
    }

    // Obtener un sorteo por ID
    public function obtenerPorId($id)
    {
        $sql = "SELECT *
                FROM sorteos
                WHERE id = ?
                LIMIT 1";

        $stmt = $this->conexion->prepare($sql);

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $sorteo = $resultado->fetch_assoc();

        $stmt->close();

        return $sorteo ?: null;
    }

    // Actualizar un sorteo
    public function actualizar($id, $loteria_id, $nombre, $hora)
    {
        $nombre = trim($nombre);

        if ($nombre === '' || $hora === '') {
            return false;
        }

        $sql = "UPDATE sorteos
                SET loteria_id = ?,
                    nombre = ?,
                    hora = ?
                WHERE id = ?";

        $stmt = $this->conexion->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "issi",
            $loteria_id,
            $nombre,
            $hora,
            $id
        );

        $resultado = $stmt->execute();

        $stmt->close();

        return $resultado;
    }

    // Activar o desactivar un sorteo
    public function cambiarEstado($id, $activo)
    {
        $activo = (int) $activo;

        if (!in_array($activo, [0, 1], true)) {
            return false;
        }

        $sql = "UPDATE sorteos
                SET activo = ?
                WHERE id = ?";

        $stmt = $this->conexion->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("ii", $activo, $id);

        $resultado = $stmt->execute();

        $stmt->close();

        return $resultado;
    }
}
