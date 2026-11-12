
<?php

require_once __DIR__ . '/../conexion.php';

class LoteriaModel
{
    private $conexion;

    public function __construct()
    {
        global $conexion;
        $this->conexion = $conexion;
    }

    // Registrar una lotería
    public function registrar($nombre)
    {
        $nombre = trim($nombre);

        if ($nombre === '') {
            return false;
        }

        $sql = "INSERT INTO loterias (nombre) VALUES (?)";

        $stmt = $this->conexion->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("s", $nombre);

        $resultado = $stmt->execute();

        $stmt->close();

        return $resultado;
    }

    // Obtener todas las loterías
    public function obtenerTodas()
    {
        $sql = "SELECT *
                FROM loterias
                ORDER BY nombre ASC";

        $resultado = $this->conexion->query($sql);

        return $resultado
            ? $resultado->fetch_all(MYSQLI_ASSOC)
            : [];
    }

    // Obtener solamente las loterías activas
    public function obtenerActivas()
    {
        $sql = "SELECT *
                FROM loterias
                WHERE activo = 1
                ORDER BY nombre ASC";

        $resultado = $this->conexion->query($sql);

        return $resultado
            ? $resultado->fetch_all(MYSQLI_ASSOC)
            : [];
    }

    // Buscar lotería por ID
    public function obtenerPorId($id)
    {
        $sql = "SELECT *
                FROM loterias
                WHERE id = ?
                LIMIT 1";

        $stmt = $this->conexion->prepare($sql);

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $loteria = $resultado->fetch_assoc();

        $stmt->close();

        return $loteria ?: null;
    }

    // Actualizar nombre de una lotería
    public function actualizar($id, $nombre)
    {
        $nombre = trim($nombre);

        if ($nombre === '') {
            return false;
        }

        $sql = "UPDATE loterias
                SET nombre = ?
                WHERE id = ?";

        $stmt = $this->conexion->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("si", $nombre, $id);

        $resultado = $stmt->execute();

        $stmt->close();

        return $resultado;
    }

    // Activar o desactivar una lotería
    public function cambiarEstado($id, $activo)
    {
        $activo = (int) $activo;

        if (!in_array($activo, [0, 1], true)) {
            return false;
        }

        $sql = "UPDATE loterias
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
