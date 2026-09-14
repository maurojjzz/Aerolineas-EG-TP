<?php

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/Usuario.php';

class UsuarioController {

    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function crearUsuario(): void {

        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $tipoDocumento = trim($_POST['tipo_documento'] ?? '');
        $nroDocumento = trim($_POST['numero_documento'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $fechaNacimiento = $_POST['fecha_nacimiento'] ?? '';
        $contrasena = $_POST['contrasena'] ?? '';
        $confirmarContrasena = $_POST['confirmar_contrasena'] ?? '';

        if (!$nombre || !$apellido || !$tipoDocumento || !$nroDocumento
            || !$email || !$telefono || !$fechaNacimiento || !$contrasena) {
            $this->responderError(400, "Datos incompletos");
            return;
        }

        if (!in_array($tipoDocumento, ['DNI', 'pasaporte', 'lc', 'le'], true)) {
            $this->responderError(400, "Tipo de documento inválido");
            return;
        }

        if ($contrasena !== $confirmarContrasena) {
            $this->responderError(400, "Las contraseñas no coinciden");
            return;
        }

        if (strlen($contrasena) < 8) {
            $this->responderError(400, "La contraseña debe tener al menos 8 caracteres");
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->responderError(400, "Email inválido");
            return;
        }

        // Mayoría de edad
        $fechaNac = new DateTime($fechaNacimiento);
        $hoy      = new DateTime();
        if ($hoy->diff($fechaNac)->y < 18) {
            $this->responderError(400, "Debe ser mayor de 18 años");
            return;
        }

        // --- Unicidad email ---
        $stmt = mysqli_prepare($this->conexion,
            "SELECT idUsuario FROM usuario WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) > 0) {
            mysqli_stmt_close($stmt);
            $this->responderError(409, "El email ya está registrado");
            return;
        }
        mysqli_stmt_close($stmt);

        // --- Unicidad documento ---
        $stmt = mysqli_prepare($this->conexion,
            "SELECT idUsuario FROM usuario
             WHERE tipoDocumento = ? AND nroDocumento = ?");
        mysqli_stmt_bind_param($stmt, "ss", $tipoDocumento, $nroDocumento);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) > 0) {
            mysqli_stmt_close($stmt);
            $this->responderError(409, "El documento ya está registrado");
            return;
        }
        mysqli_stmt_close($stmt);

        // --- Preparar datos ---
        $hash  = password_hash($contrasena, PASSWORD_DEFAULT);
        $token = bin2hex(random_bytes(32));

        $usuario = new Usuario(
            $nombre,
            $apellido,
            $tipoDocumento,
            $nroDocumento,
            $hash,
            $email,
            $telefono,
            $fechaNacimiento,
            'cliente',
            false,   // activo       -> ponelo en true si querés testear sin mail
            false,   // emailVerificado
            $token
        );

        $query = "INSERT INTO usuario
                  (nombre, apellido, tipoDocumento, nroDocumento, contrasena,
                   email, telefono, fechaNacimiento, rol, activo,
                   emailVerificado, tokenVerificacion)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($this->conexion, $query);

        if (!$stmt) {
            $this->responderError(500, "Error al preparar la consulta");
            return;
        }

        $rol             = $usuario->getRol();
        $activo          = $usuario->getActivo() ? 1 : 0;
        $emailVerificado = $usuario->getEmailVerificado() ? 1 : 0;
        $tokenVerif      = $usuario->getTokenVerificacion();

        mysqli_stmt_bind_param(
            $stmt,
            "sssssssssiis",
            $usuario->getNombre(),
            $usuario->getApellido(),
            $usuario->getTipoDocumento(),
            $usuario->getNroDocumento(),
            $usuario->getContrasena(),
            $usuario->getEmail(),
            $usuario->getTelefono(),
            $usuario->getFechaNacimiento(),
            $rol,
            $activo,
            $emailVerificado,
            $tokenVerif
        );

        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if (!$ok) {
            $this->responderError(500, "Error al crear el usuario");
            return;
        }

        // TODO: acá va el envío del mail con $tokenVerif.
        header("Location: " . url('index.php?pagina=login&registro=ok'));
        exit;
    }

    private function responderError(int $codigo, string $mensaje): void {
        http_response_code($codigo);
        echo $mensaje;
    }
}