
<?php

require_once __DIR__ . '/../conexion.php';

class PremioJuegoModel
{
    private $conexion;

    public function __construct()
    {
        global $conexion;
        $this->conexion = $conexion;
    }

    // Registrar un premio
    public function registrar($juego_id, $nombre_premio, $valor_por_peso)
    {
        $premiosPermitidos = [
            'Primera',
            'Segunda',
            'Tercera',
            'Pale',
            'Tripleta'
        ];

        if (
            !in_array($nombre_premio, $premiosPermitidos, true) ||
            $valor_por_peso <= 0
        ) {
            return false;
        }

        $sql = "INSERT INTO premios_juego
                (juego_id, nombre_premio, valor_por_peso)
                VALUES (?, ?, ?)";

        $stmt = $this->conexion->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "isd",
            $juego_id,
            $nombre_premio,
            $valor_por_peso
        );

        $resultado = $stmt->execute();

        $stmt->close();

        return $resultado;
    }

    // Obtener todos los premios
    public function obtenerTodos()
    {
        $sql = "SELECT
                    p.*,
                    j.modalidad
                FROM premios_juego p
                INNER JOIN juegos j
                    ON j.id = p.juego_id
                ORDER BY p.id DESC";

        $resultado = $this->conexion->query($sql);

        return $resultado
            ? $resultado->fetch_all(MYSQLI_ASSOC)
            : [];
    }

    // Obtener premios de un juego
    public function obtenerPorJuego($juego_id)
    {
        $sql = "SELECT *
                FROM premios_juego
                WHERE juego_id = ?
                ORDER BY id ASC";

        $stmt = $this->conexion->prepare($sql);

        if (!$stmt) {
            return [];
        }

        $stmt->bind_param("i", $juego_id);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $premios = $resultado->fetch_all(MYSQLI_ASSOC);

        $stmt->close();

        return $premios;
    }

    // Obtener un premio por ID
    public function obtenerPorId($id)
    {
        $sql = "SELECT *
                FROM premios_juego
                WHERE id = ?
                LIMIT 1";

        $stmt = $this->conexion->prepare($sql);

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $premio = $resultado->fetch_assoc();

        $stmt->close();

        return $premio ?: null;
    }

    // Actualizar valor de un premio
    public function actualizar($id, $nombre_premio, $valor_por_peso)
    {
        $premiosPermitidos = [
            'Primera',
            'Segunda',
            'Tercera',
            'Pale',
            'Tripleta'
        ];

        if (
            !in_array($nombre_premio, $premiosPermitidos, true) ||
            $valor_por_peso <= 0
        ) {
            return false;
        }

        $sql = "UPDATE premios_juego
                SET nombre_premio = ?,
                    valor_por_peso = ?
                WHERE id = ?";

        $stmt = $this->conexion->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "sdi",
            $nombre_premio,
            $valor_por_peso,
            $id
        );

        $resultado = $stmt->execute();

        $stmt->close();

        return $resultado;
    }

    // Eliminar un premio
    public function eliminar($id)
    {
        $sql = "DELETE FROM premios_juego
                WHERE id = ?";

        $stmt = $this->conexion->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $id);

        $resultado = $stmt->execute();

        $stmt->close();

        return $resultado;
    }
}
