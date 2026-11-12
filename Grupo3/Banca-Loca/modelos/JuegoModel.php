
<?php

require_once __DIR__ . '/../conexion.php';

class JuegoModel
{
    private $conexion;

    public function __construct()
    {
        global $conexion;
        $this->conexion = $conexion;
    }

    // Registrar modalidad de juego
    public function registrar(
        $sorteo_id,
        $modalidad,
        $cantidad_numeros,
        $numero_minimo,
        $numero_maximo,
        $monto_minimo,
        $monto_maximo
    ) {
        $modalidad = trim($modalidad);

        if (
            $modalidad === '' ||
            $cantidad_numeros <= 0 ||
            $monto_minimo < 0 ||
            $monto_maximo < $monto_minimo
        ) {
            return false;
        }

        $sql = "INSERT INTO juegos (
                    sorteo_id,
                    modalidad,
                    cantidad_numeros,
                    numero_minimo,
                    numero_maximo,
                    monto_minimo,
                    monto_maximo
                ) VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conexion->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "isissdd",
            $sorteo_id,
            $modalidad,
            $cantidad_numeros,
            $numero_minimo,
            $numero_maximo,
            $monto_minimo,
            $monto_maximo
        );

        $resultado = $stmt->execute();

        $stmt->close();

        return $resultado;
    }

    // Obtener todos los juegos
    public function obtenerTodos()
    {
        $sql = "SELECT
                    j.*,
                    s.nombre AS nombre_sorteo,
                    l.nombre AS nombre_loteria
                FROM juegos j
                INNER JOIN sorteos s ON s.id = j.sorteo_id
                INNER JOIN loterias l ON l.id = s.loteria_id
                ORDER BY j.id DESC";

        $resultado = $this->conexion->query($sql);

        return $resultado
            ? $resultado->fetch_all(MYSQLI_ASSOC)
            : [];
    }

    // Obtener juegos activos
    public function obtenerActivos()
    {
        $sql = "SELECT
                    j.*,
                    s.nombre AS nombre_sorteo,
                    l.nombre AS nombre_loteria
                FROM juegos j
                INNER JOIN sorteos s ON s.id = j.sorteo_id
                INNER JOIN loterias l ON l.id = s.loteria_id
                WHERE j.activo = 1
                  AND s.activo = 1
                  AND l.activo = 1
                ORDER BY j.modalidad ASC";

        $resultado = $this->conexion->query($sql);

        return $resultado
            ? $resultado->fetch_all(MYSQLI_ASSOC)
            : [];
    }

    // Buscar juego por ID
    public function obtenerPorId($id)
    {
        $sql = "SELECT *
                FROM juegos
                WHERE id = ?
                LIMIT 1";

        $stmt = $this->conexion->prepare($sql);

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $juego = $resultado->fetch_assoc();

        $stmt->close();

        return $juego ?: null;
    }

    // Obtener juegos por sorteo
    public function obtenerPorSorteo($sorteo_id)
    {
        $sql = "SELECT *
                FROM juegos
                WHERE sorteo_id = ?
                ORDER BY modalidad ASC";

        $stmt = $this->conexion->prepare($sql);

        if (!$stmt) {
            return [];
        }

        $stmt->bind_param("i", $sorteo_id);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $juegos = $resultado->fetch_all(MYSQLI_ASSOC);

        $stmt->close();

        return $juegos;
    }

    // Actualizar modalidad
    public function actualizar(
        $id,
        $sorteo_id,
        $modalidad,
        $cantidad_numeros,
        $numero_minimo,
        $numero_maximo,
        $monto_minimo,
        $monto_maximo
    ) {
        $modalidad = trim($modalidad);

        if (
            $modalidad === '' ||
            $cantidad_numeros <= 0 ||
            $monto_minimo < 0 ||
            $monto_maximo < $monto_minimo
        ) {
            return false;
        }

        $sql = "UPDATE juegos SET
                    sorteo_id = ?,
                    modalidad = ?,
                    cantidad_numeros = ?,
                    numero_minimo = ?,
                    numero_maximo = ?,
                    monto_minimo = ?,
                    monto_maximo = ?
                WHERE id = ?";

        $stmt = $this->conexion->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "isissddi",
            $sorteo_id,
            $modalidad,
            $cantidad_numeros,
            $numero_minimo,
            $numero_maximo,
            $monto_minimo,
            $monto_maximo,
            $id
        );

        $resultado = $stmt->execute();

        $stmt->close();

        return $resultado;
    }

    // Activar o desactivar juego
    public function cambiarEstado($id, $activo)
    {
        $activo = (int) $activo;

        if (!in_array($activo, [0, 1], true)) {
            return false;
        }

        $sql = "UPDATE juegos
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
