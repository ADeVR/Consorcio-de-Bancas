
<?php

require_once __DIR__ . '/../conexion.php';

class TicketModel
{
    private $conexion;

    public function __construct()
    {
        global $conexion;
        $this->conexion = $conexion;
    }

    // Registrar un nuevo ticket
    public function registrar($codigo_ticket, $id_apertura, $id_usuario, $total)
    {
        $sql = "INSERT INTO ticket
                (codigo_ticket, id_apertura, id_usuario, total)
                VALUES (?, ?, ?, ?)";

        $stmt = $this->conexion->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "siid",
            $codigo_ticket,
            $id_apertura,
            $id_usuario,
            $total
        );

        $resultado = $stmt->execute();

        $stmt->close();

        return $resultado;
    }

    // Obtener todos los tickets
    public function obtenerTodos()
    {
        $sql = "SELECT *
                FROM ticket
                ORDER BY fecha_hora DESC";

        $resultado = $this->conexion->query($sql);

        if (!$resultado) {
            return [];
        }

        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    // Buscar ticket por código
    public function buscarPorCodigo($codigo_ticket)
    {
        $sql = "SELECT *
                FROM ticket
                WHERE codigo_ticket = ?
                LIMIT 1";

        $stmt = $this->conexion->prepare($sql);

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("s", $codigo_ticket);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $ticket = $resultado->fetch_assoc();

        $stmt->close();

        return $ticket ?: null;
    }

    // Obtener tickets de un usuario
    public function obtenerPorUsuario($id_usuario)
    {
        $sql = "SELECT *
                FROM ticket
                WHERE id_usuario = ?
                ORDER BY fecha_hora DESC";

        $stmt = $this->conexion->prepare($sql);

        if (!$stmt) {
            return [];
        }

        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $tickets = $resultado->fetch_all(MYSQLI_ASSOC);

        $stmt->close();

        return $tickets;
    }

    // Obtener un ticket por su ID
    public function obtenerPorId($id_ticket)
    {
        $sql = "SELECT *
                FROM ticket
                WHERE id_ticket = ?
                LIMIT 1";

        $stmt = $this->conexion->prepare($sql);

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("i", $id_ticket);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $ticket = $resultado->fetch_assoc();

        $stmt->close();

        return $ticket ?: null;
    }

    // Anular un ticket
    public function anular($id_ticket)
    {
        $sql = "UPDATE ticket
                SET estado = 'anulado'
                WHERE id_ticket = ?
                AND estado = 'activo'";

        $stmt = $this->conexion->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $id_ticket);

        $resultado = $stmt->execute();

        $stmt->close();

        return $resultado;
    }

    // Consultar tickets por rango de fechas
    public function obtenerPorFechas($desde, $hasta)
    {
        $sql = "SELECT *
                FROM ticket
                WHERE fecha_hora BETWEEN ? AND ?
                ORDER BY fecha_hora DESC";

        $stmt = $this->conexion->prepare($sql);

        if (!$stmt) {
            return [];
        }

        $stmt->bind_param("ss", $desde, $hasta);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $tickets = $resultado->fetch_all(MYSQLI_ASSOC);

        $stmt->close();

        return $tickets;
    }
}

