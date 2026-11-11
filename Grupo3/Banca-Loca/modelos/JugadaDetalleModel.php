
<?php

require_once __DIR__ . '/../conexion.php';

class JugadaDetalleModel
{
    private $conexion;

    public function __construct()
    {
        global $conexion;
        $this->conexion = $conexion;
    }

    // Registrar una jugada
    public function registrar($id_ticket, $id_loteria, $id_sorteo, $id_tipo, $numeros, $monto)
    {
        $sql = "INSERT INTO jugada_detalle
                (id_ticket, id_loteria, id_sorteo, id_tipo, numeros, monto)
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $this->conexion->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "iiiisd",
            $id_ticket,
            $id_loteria,
            $id_sorteo,
            $id_tipo,
            $numeros,
            $monto
        );

        $resultado = $stmt->execute();

        $stmt->close();

        return $resultado;
    }

    // Obtener todos los detalles
    public function obtenerTodos()
    {
        $sql = "SELECT *
                FROM jugada_detalle
                ORDER BY id_detalle DESC";

        $resultado = $this->conexion->query($sql);

        return $resultado
            ? $resultado->fetch_all(MYSQLI_ASSOC)
            : [];
    }

    // Obtener detalles de un ticket
    public function obtenerPorTicket($id_ticket)
    {
        $sql = "SELECT *
                FROM jugada_detalle
                WHERE id_ticket = ?
                ORDER BY id_detalle ASC";

        $stmt = $this->conexion->prepare($sql);

        if (!$stmt) {
            return [];
        }

        $stmt->bind_param("i", $id_ticket);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $detalles = $resultado->fetch_all(MYSQLI_ASSOC);

        $stmt->close();

        return $detalles;
    }

    // Consultar jugadas por lotería
    public function obtenerPorLoteria($id_loteria)
    {
        $sql = "SELECT *
                FROM jugada_detalle
                WHERE id_loteria = ?
                ORDER BY id_detalle DESC";

        $stmt = $this->conexion->prepare($sql);

        if (!$stmt) {
            return [];
        }

        $stmt->bind_param("i", $id_loteria);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $detalles = $resultado->fetch_all(MYSQLI_ASSOC);

        $stmt->close();

        return $detalles;
    }

    // Consultar jugadas por sorteo
    public function obtenerPorSorteo($id_sorteo)
    {
        $sql = "SELECT *
                FROM jugada_detalle
                WHERE id_sorteo = ?
                ORDER BY id_detalle DESC";

        $stmt = $this->conexion->prepare($sql);

        if (!$stmt) {
            return [];
        }

        $stmt->bind_param("i", $id_sorteo);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $detalles = $resultado->fetch_all(MYSQLI_ASSOC);

        $stmt->close();

        return $detalles;
    }

    // Actualizar estado de una jugada
    public function actualizarEstado($id_detalle, $estado)
    {
        $estadosPermitidos = [
            'pendiente',
            'ganadora',
            'perdedora',
            'pagada',
            'anulada'
        ];

        if (!in_array($estado, $estadosPermitidos, true)) {
            return false;
        }

        $sql = "UPDATE jugada_detalle
                SET estado = ?
                WHERE id_detalle = ?";

        $stmt = $this->conexion->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("si", $estado, $id_detalle);

        $resultado = $stmt->execute();

        $stmt->close();

        return $resultado;
    }

    // Anular una jugada
    public function anular($id_detalle)
    {
        return $this->actualizarEstado($id_detalle, 'anulada');
    }
}
