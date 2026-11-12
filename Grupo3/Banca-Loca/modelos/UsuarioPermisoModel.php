
<?php

require_once __DIR__ . '/../conexion.php';

class UsuarioPermisoModel
{
    private $conexion;

    public function __construct()
    {
        global $conexion;
        $this->conexion = $conexion;
    }

    // Validar los datos del permiso
    private function validarDatos($usuarioId, $modulo)
    {
        $usuarioId = (int)$usuarioId;
        $modulo = trim($modulo);

        return (
            $usuarioId > 0 &&
            $modulo !== '' &&
            strlen($modulo) <= 30
        );
    }

    // Asignar permiso a un usuario
    public function asignar($usuarioId, $modulo)
    {
        $usuarioId = (int)$usuarioId;
        $modulo = trim($modulo);

        if (!$this->validarDatos($usuarioId, $modulo)) {
            return false;
        }

        // Comprobar si el permiso ya existe
        $sql = "SELECT id
                FROM usuario_permisos
                WHERE usuario_id = ?
                AND modulo = ?
                LIMIT 1";

        $stmt = $this->conexion->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("is", $usuarioId, $modulo);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $existe = $resultado->num_rows > 0;

        $stmt->close();

        // Evitar asignaciones duplicadas
        if ($existe) {
            return true;
        }

        $sql = "INSERT INTO usuario_permisos
                (usuario_id, modulo)
                VALUES (?, ?)";

        $stmt = $this->conexion->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("is", $usuarioId, $modulo);

        $resultado = $stmt->execute();

        $stmt->close();

        return $resultado;
    }

    // Retirar un permiso de un usuario
    public function quitar($usuarioId, $modulo)
    {
        $usuarioId = (int)$usuarioId;
        $modulo = trim($modulo);

        if (!$this->validarDatos($usuarioId, $modulo)) {
            return false;
        }

        $sql = "DELETE FROM usuario_permisos
                WHERE usuario_id = ?
                AND modulo = ?";

        $stmt = $this->conexion->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("is", $usuarioId, $modulo);

        $resultado = $stmt->execute();

        $stmt->close();

        return $resultado;
    }

    // Retirar todos los permisos de un usuario
    public function quitarTodos($usuarioId)
    {
        $usuarioId = (int)$usuarioId;

        if ($usuarioId <= 0) {
            return false;
        }

        $sql = "DELETE FROM usuario_permisos
                WHERE usuario_id = ?";

        $stmt = $this->conexion->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $usuarioId);

        $resultado = $stmt->execute();

        $stmt->close();

        return $resultado;
    }

    // Consultar todos los permisos de un usuario
    public function obtenerPorUsuario($usuarioId)
    {
        $usuarioId = (int)$usuarioId;

        if ($usuarioId <= 0) {
            return [];
        }

        $sql = "SELECT
                    id,
                    usuario_id,
                    modulo,
                    fecha_asignacion
                FROM usuario_permisos
                WHERE usuario_id = ?
                ORDER BY modulo ASC";

        $stmt = $this->conexion->prepare($sql);

        if (!$stmt) {
            return [];
        }

        $stmt->bind_param("i", $usuarioId);
        $stmt->execute();

        $resultado = $stmt->get_result();

        $datos = $resultado->fetch_all(MYSQLI_ASSOC);

        $stmt->close();

        return $datos;
    }

    // Obtener solamente los nombres de los módulos
    public function obtenerModulosPorUsuario($usuarioId)
    {
        $permisos = $this->obtenerPorUsuario($usuarioId);

        return array_column($permisos, 'modulo');
    }

    // Comprobar si un usuario tiene determinado permiso
    public function tienePermiso($usuarioId, $modulo)
    {
        $usuarioId = (int)$usuarioId;
        $modulo = trim($modulo);

        if (!$this->validarDatos($usuarioId, $modulo)) {
            return false;
        }

        $sql = "SELECT id
                FROM usuario_permisos
                WHERE usuario_id = ?
                AND modulo = ?
                LIMIT 1";

        $stmt = $this->conexion->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("is", $usuarioId, $modulo);
        $stmt->execute();

        $resultado = $stmt->get_result();

        $tieneAcceso = $resultado->num_rows > 0;

        $stmt->close();

        return $tieneAcceso;
    }
}
