
<?php

require_once __DIR__ . '/../conexion.php';

class UsuarioModel
{
    private $conexion;

    public function __construct()
    {
        global $conexion;
        $this->conexion = $conexion;
    }

    // Registrar un usuario
    public function registrar($usuario, $clave, $rol = 'agente')
    {
        $usuario = trim($usuario);

        $rolesPermitidos = ['admin', 'agente'];

        if (
            $usuario === '' ||
            $clave === '' ||
            !in_array($rol, $rolesPermitidos, true)
        ) {
            return false;
        }

        // Guardar la contraseña protegida
        $claveHash = password_hash($clave, PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuarios
                (usuario, clave, rol)
                VALUES (?, ?, ?)";

        $stmt = $this->conexion->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param(
            "sss",
            $usuario,
            $claveHash,
            $rol
        );

        $resultado = $stmt->execute();

        $stmt->close();

        return $resultado;
    }

    // Buscar usuario por nombre de usuario
    public function obtenerPorUsuario($usuario)
    {
        $sql = "SELECT *
                FROM usuarios
                WHERE usuario = ?
                LIMIT 1";

        $stmt = $this->conexion->prepare($sql);

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("s", $usuario);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $datos = $resultado->fetch_assoc();

        $stmt->close();

        return $datos ?: null;
    }

    // Buscar usuario por ID
    public function obtenerPorId($id)
    {
        $sql = "SELECT
                    id,
                    usuario,
                    rol,
                    activo,
                    fecha_creacion
                FROM usuarios
                WHERE id = ?
                LIMIT 1";

        $stmt = $this->conexion->prepare($sql);

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();

        $resultado = $stmt->get_result();
        $datos = $resultado->fetch_assoc();

        $stmt->close();

        return $datos ?: null;
    }

    // Obtener todos los usuarios
    public function obtenerTodos()
    {
        $sql = "SELECT
                    id,
                    usuario,
                    rol,
                    activo,
                    fecha_creacion
                FROM usuarios
                ORDER BY id DESC";

        $resultado = $this->conexion->query($sql);

        return $resultado
            ? $resultado->fetch_all(MYSQLI_ASSOC)
            : [];
    }

    // Verificar credenciales
    public function verificarAcceso($usuario, $clave)
    {
        $datos = $this->obtenerPorUsuario($usuario);

        if (!$datos || (int)$datos['activo'] !== 1) {
            return false;
        }

        if (!password_verify($clave, $datos['clave'])) {
            return false;
        }

        // No devolver la contraseña
        unset($datos['clave']);

        return $datos;
    }

    // Cambiar contraseña
    public function cambiarClave($id, $nuevaClave)
    {
        if (strlen($nuevaClave) < 8) {
            return false;
        }

        $claveHash = password_hash(
            $nuevaClave,
            PASSWORD_DEFAULT
        );

        $sql = "UPDATE usuarios
                SET clave = ?
                WHERE id = ?";

        $stmt = $this->conexion->prepare($sql);

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("si", $claveHash, $id);

        $resultado = $stmt->execute();

        $stmt->close();

        return $resultado;
    }

    // Activar o desactivar usuario
    public function cambiarEstado($id, $activo)
    {
        $activo = (int)$activo;

        if (!in_array($activo, [0, 1], true)) {
            return false;
        }

        $sql = "UPDATE usuarios
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
