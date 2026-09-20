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

    public function login(): void {
        $email = trim($_POST['email'] ?? '');
        $contrasena = $_POST['contrasena'] ?? '';

        if (!$email || !$contrasena) {
            flash_set('error', 'Faltan campos obligatorios');
            redirect("index.php?pagina=login");
        }

        $query = "SELECT idUsuario, nombre, apellido, contrasena, rol, activo, emailVerificado, idAerolinea FROM usuario WHERE email = ?";
        $stmt = mysqli_prepare($this->conexion, $query);

        if (!$stmt) {
            flash_set('error', 'Error interno del servidor');
            redirect("index.php?pagina=login");
        }

        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $usuario = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);

        if (!$usuario || !password_verify($contrasena, $usuario['contrasena'])) {
            flash_set('error', 'Email o contraseña incorrectos');
            redirect("index.php?pagina=login");
        }

        if (!$usuario['emailVerificado']) {
            flash_set('error', 'Debe verificar su correo electrónico antes de iniciar sesión. Por favor, revisa tu correo.');
            redirect("index.php?pagina=login");
        }

        // CEO pendiente de aprobación
        if ($usuario['rol'] === 'ceo' && (int)$usuario['activo'] === 0) {
            flash_set('error', 'Tu solicitud está pendiente de aprobación.');
            redirect('index.php?pagina=login');
        }

        if (!$usuario['activo']) {
            flash_set('error', 'Tu cuenta esta inactiva. Por favor, contacta al administrador.');
            redirect("index.php?pagina=login");
        }


        // Iniciar sesión
        $_SESSION['usuario'] = [
            'idUsuario'    => (int)$usuario['idUsuario'],
            'nombre'       => $usuario['nombre'],
            'apellido'     => $usuario['apellido'],
            'email'        => $email,
            'rol'          => $usuario['rol'],
            'idAerolinea'  => $usuario['idAerolinea'] !== null ? (int)$usuario['idAerolinea'] : null,
        ];

        switch ($usuario['rol']) {
            case 'ceo':
                redirect('index.php?pagina=aerolinea&seccion=alta');
                //redirect('index.php?pagina=dashboard-ceo');   // este es el og, cambiarlo ahora esta puesto otro para testear
                break;

            case 'admin':
                redirect('index.php?pagina=aerolinea&seccion=alta');
                // redirect('index.php?pagina=dashboard-admin'); // crearlo dsp // este es el og, cambiarlo ahora esta puesto otro para testear
                break;
            case 'cliente':
                redirect('index.php?pagina=aerolinea&seccion=alta');
                //redirect('index.php?pagina=dashboard'); // crearlo dsp // este es el og, cambiarlo ahora esta puesto otro para testear
                break;
            default: 
                $_SESSION = [];
                session_destroy();
                flash_set('error', 'Tu cuenta tiene un rol inválido. Contactá al administrador.');
                redirect('index.php?pagina=login');
                break;
        }

    }

    public function logout(): void {
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();
        flash_set('success', 'Has cerrado sesión correctamente.');
        redirect("index.php?pagina=login");
    }

}