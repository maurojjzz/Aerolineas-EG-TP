<?php

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/Usuario.php';

class UsuarioController {

    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function crearUsuario():void{
        $this->registrar('cliente');
    }

    public function crearCEO():void{
        $this->registrar('ceo');
    }

    public function registrar(string $rol): void {

        $paginaDestino = ($rol === 'ceo') ? 'registro-ceo' : 'registro';

        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $tipoDocumento = trim($_POST['tipo_documento'] ?? '');
        $nroDocumento = trim($_POST['numero_documento'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $fechaNacimiento = $_POST['fecha_nacimiento'] ?? '';
        $contrasena = $_POST['contrasena'] ?? '';
        $confirmarContrasena = $_POST['confirmar_contrasena'] ?? '';

        $idAerolinea = null;

        if($rol === 'ceo') {
            $idAerolinea = (int)($_POST['aerolinea'] ?? null);
        }

        if (!$nombre || !$apellido || !$tipoDocumento || !$nroDocumento || !$email || !$telefono || !$fechaNacimiento || !$contrasena) {
            flash_set('error', 'Faltan campos obligatorios');
            redirect("index.php?pagina=$paginaDestino");
        }

        if (!in_array($tipoDocumento, ['DNI', 'pasaporte', 'lc', 'le'], true)) {
            flash_set('error', 'Tipo de documento inválido');
            redirect("index.php?pagina=$paginaDestino");
        }

        if ($contrasena !== $confirmarContrasena) {
            flash_set('error', 'Las contraseñas no coinciden');
            redirect("index.php?pagina=$paginaDestino");
        }

        if (strlen($contrasena) < 8) {
            flash_set('error', 'La contraseña debe tener al menos 8 caracteres');
            redirect("index.php?pagina=$paginaDestino");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash_set('error', 'Email inválido');
            redirect("index.php?pagina=$paginaDestino");
        }

        // Mayoría de edad
        $fechaNac = new DateTime($fechaNacimiento);
        $hoy = new DateTime();
        if ($hoy->diff($fechaNac)->y < 18) {
            flash_set('error', 'Debe ser mayor de 18 años');
            redirect("index.php?pagina=$paginaDestino");
            exit;
        }

        $hash  = password_hash($contrasena, PASSWORD_DEFAULT);
        $token = bin2hex(random_bytes(32));

        // Todo usuario nuevo arranca inactivo y sin verificar.
        // - cliente -> se activa al confirmar el mail.
        // - ceo     -> además tiene que ser aprobado por un admin.

        $activo=0;
        $emailVerificado=0;


        $query = "INSERT INTO usuario(nombre, apellido, tipoDocumento, nroDocumento, contrasena, email, telefono, fechaNacimiento, rol, activo, emailVerificado, tokenVerificacion, idAerolinea) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($this->conexion, $query);

        if (!$stmt) {
            flash_set('error', 'Error interno del servidor');
            redirect("index.php?pagina=$paginaDestino");
        }


        mysqli_stmt_bind_param(
            $stmt,
            "sssssssssiisi",
            $nombre,
            $apellido,
            $tipoDocumento,
            $nroDocumento,
            $hash,
            $email,
            $telefono,
            $fechaNacimiento,
            $rol,
            $activo,
            $emailVerificado,
            $token,
            $idAerolinea
        );

        // --- Ejecutar y capturar el error ---
        try {
            mysqli_stmt_execute($stmt);
        } catch (mysqli_sql_exception $e) {
            $errno = $e->getCode();
            $error = $e->getMessage();
            mysqli_stmt_close($stmt);
            if ($errno === 1062) {   
                if (str_contains($error, 'uk_email')) {
                    flash_set('error', 'El email ya está registrado');
                } elseif (str_contains($error, 'uk_documento')) {
                    flash_set('error', 'El documento ya está registrado');
                } else {
                    flash_set('error', 'Error, el usuario ya existe');
                }
            } else {
                flash_set('error', 'Error al crear el usuario');
            }
            redirect("index.php?pagina=$paginaDestino");
        }


        mysqli_stmt_close($stmt);


        if ($rol === 'ceo') {
            flash_set('success', '¡Cuenta creada! Revisa tu correo para verificarla. Luego un administrador la revisará y te avisaremos por email si fue activada.');
        } else {
            flash_set('success', '¡Cuenta creada! Revisá tu correo para verificarla.');
        }
        
        redirect("index.php?pagina=login");
    }

}