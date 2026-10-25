<?php

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../services/EmailService.php';

class UsuarioController {

    private $conexion;

    private function obtenerEscalar(string $query, string $columna): int {
        $stmt = mysqli_prepare($this->conexion, $query);
        if (!$stmt) {
            return 0;
        }
        mysqli_stmt_execute($stmt);
        $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        return (int)($fila[$columna] ?? 0);
    }

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function crearUsuario(): void {
        $this->registrar('cliente');
    }

    public function crearCEO(): void {
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
        if ($rol === 'ceo') {
            $idAerolinea = (int)($_POST['aerolinea'] ?? null);
        }

        if (!textoValido($nombre, 100) || !textoValido($apellido, 100) || !textoValido($tipoDocumento, 20) ||
            !textoValido($nroDocumento, 30) || !textoValido($email, 254) || !textoValido($telefono, 25) ||
            !textoValido($fechaNacimiento, 10) || !textoValido($contrasena, 255)) {
            flash_set('error', 'Faltan campos obligatorios');
            redirect("index.php?pagina=$paginaDestino");
        }

        if (!in_array($rol, ['cliente', 'ceo'], true)) {
            flash_set('error', 'El rol seleccionado no es válido.');
            redirect("index.php?pagina=$paginaDestino");
        }

        if ($rol === 'ceo' && $idAerolinea <= 0) {
            flash_set('error', 'Debés seleccionar una aerolínea.');
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

        if (!telefonoValidoFormato($telefono)) {
            flash_set('error', 'El teléfono no tiene un formato válido.');
            redirect("index.php?pagina=$paginaDestino");
        }

        if (!fechaValidaFormato($fechaNacimiento)) {
            flash_set('error', 'La fecha de nacimiento no es válida.');
            redirect("index.php?pagina=$paginaDestino");
        }
        $fechaNac = DateTime::createFromFormat('!Y-m-d', $fechaNacimiento);
        $hoy = new DateTime();
        if ($hoy->diff($fechaNac)->y < 18) {
            flash_set('error', 'Debe ser mayor de 18 años');
            redirect("index.php?pagina=$paginaDestino");
        }

        $hash  = password_hash($contrasena, PASSWORD_DEFAULT);
        $token = bin2hex(random_bytes(32));

        $activo = 0;
        // Los CEOs se habilitan mediante la aprobación administrativa; no
        // necesitan validar el correo electrónico para iniciar sesión.
        $emailVerificado = $rol === 'ceo' ? 1 : 0;
        $tokenVerificacion = $rol === 'ceo' ? null : $token;

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
            $tokenVerificacion,
            $idAerolinea
        );

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
            EmailService::enviarNotificacionCEOPendiente($email, $nombre);
            flash_set('success', '¡Cuenta creada! Un administrador debe aprobar tu solicitud antes de que puedas iniciar sesión.');
        } else {
            EmailService::enviarVerificacion($email, $nombre, $token);
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

        $query = "SELECT idUsuario, nombre, apellido, contrasena, rol, activo, emailVerificado,
                         fecha_hora_autorizacion, idAerolinea
                  FROM usuario WHERE email = ?";
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

        $esCEO       = $usuario['rol'] === 'ceo';
        $fechaEstado = $usuario['fecha_hora_autorizacion'] ?? null;

        $esCEODenegado  = $esCEO && $fechaEstado === FECHA_DENEGACION_CEO;
        $esCEOPendiente = $esCEO && $fechaEstado === null && (int)$usuario['activo'] === 0;

        if ($esCEODenegado) {
            flash_set('error', 'Tu solicitud para registrarte como CEO fue denegada. Si creés que es un error, contactá al administrador.');
            redirect('index.php?pagina=login');
        }

        if (!$esCEO && !$usuario['emailVerificado']) {
            flash_set('error', 'Debe verificar su correo electrónico antes de iniciar sesión.');
            redirect("index.php?pagina=login");
        }

        if ($esCEOPendiente) {
            flash_set('error', 'Tu solicitud está pendiente de aprobación.');
            redirect('index.php?pagina=login');
        }

        if (!$usuario['activo']) {
            flash_set('error', 'Tu cuenta está inactiva. Por favor, contactá al administrador.');
            redirect("index.php?pagina=login");
        }

        $_SESSION['usuario'] = [
            'idUsuario'   => (int)$usuario['idUsuario'],
            'nombre'      => $usuario['nombre'],
            'apellido'    => $usuario['apellido'],
            'email'       => $email,
            'rol'         => $usuario['rol'],
            'idAerolinea' => $usuario['idAerolinea'] !== null ? (int)$usuario['idAerolinea'] : null,
        ];

        switch ($usuario['rol']) {
            case 'ceo':
                redirect('index.php?pagina=ceo');
                break;
            case 'admin':
                redirect('index.php?pagina=aerolinea&seccion=listado');
                break;
            case 'cliente':
                redirect('index.php?pagina=cliente');
                break;
            default: 
                $_SESSION = [];
                flash_set('error', 'Tu cuenta tiene un rol inválido.');
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
        flash_set('success', 'Has cerrado sesión correctamente.');
        redirect("index.php?pagina=login");
    }

    public function olvidéContrasena(): void {
        $email = trim($_POST['email'] ?? '');
        if (!$email) {
            flash_set('error', 'Ingresá tu email.');
            redirect('index.php?pagina=olvide-contrasena');
        }

        // Solo cuentas activas o CEOs ya aprobados (aunque estén inactivos).
        // Pendientes y denegados quedan expresamente fuera.
        $stmt = mysqli_prepare($this->conexion,
            "SELECT idUsuario, nombre
             FROM usuario
             WHERE email = ?
               AND (
                   activo = 1
                   OR (rol = 'ceo' AND fecha_hora_autorizacion IS NOT NULL AND fecha_hora_autorizacion <> ?)
               )");
        $fechaDenegacion = FECHA_DENEGACION_CEO;
        mysqli_stmt_bind_param($stmt, 'ss', $email, $fechaDenegacion);
        mysqli_stmt_execute($stmt);
        $usuario = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        flash_set('success', 'Recibirás un enlace para restablecer tu contraseña en el correo indicado.');

        if ($usuario) {
            $token = bin2hex(random_bytes(32));
            $stmt = mysqli_prepare($this->conexion, "UPDATE usuario SET tokenVerificacion = ? WHERE idUsuario = ?");
            mysqli_stmt_bind_param($stmt, 'si', $token, $usuario['idUsuario']);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            EmailService::enviarRecuperacionContrasena($email, $usuario['nombre'], $token);
        }

        redirect('index.php?pagina=login');
    }

    public function resetPassword(): void {
        $token      = trim($_POST['token'] ?? '');
        $contrasena = $_POST['contrasena'] ?? '';
        $confirmar  = $_POST['confirmar_contrasena'] ?? '';

        if (!$token || !$contrasena || $contrasena !== $confirmar) {
            flash_set('error', 'Datos inválidos.');
            redirect('index.php?pagina=reset-password&token=' . urlencode($token));
        }

        if (strlen($contrasena) < 8) {
            flash_set('error', 'La contraseña debe tener al menos 8 caracteres.');
            redirect('index.php?pagina=reset-password&token=' . urlencode($token));
        }

        $stmt = mysqli_prepare($this->conexion,
            "SELECT idUsuario, activo
             FROM usuario
             WHERE tokenVerificacion = ?
               AND (
                   activo = 1
                   OR (rol = 'ceo' AND fecha_hora_autorizacion IS NOT NULL AND fecha_hora_autorizacion <> ?)
               )");
        $fechaDenegacion = FECHA_DENEGACION_CEO;
        mysqli_stmt_bind_param($stmt, 'ss', $token, $fechaDenegacion);
        mysqli_stmt_execute($stmt);
        $usuario = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        if (!$usuario) {
            flash_set('error', 'El enlace es inválido o ya fue usado.');
            redirect('index.php?pagina=login');
        }

        $hash = password_hash($contrasena, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($this->conexion, "UPDATE usuario SET contrasena = ?, tokenVerificacion = NULL WHERE idUsuario = ?");
        mysqli_stmt_bind_param($stmt, 'si', $hash, $usuario['idUsuario']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if ((int)$usuario['activo'] === 1) {
            flash_set('success', 'Contraseña actualizada correctamente. Ya podés iniciar sesión.');
        } else {
            flash_set('success', 'Contraseña actualizada correctamente. Tu cuenta está inactiva; contactá al administrador para reactivarla.');
        }

        redirect('index.php?pagina=login');
    }

    public function obtenerUsuarioPorId(int $id): ?array {
        $stmt = mysqli_prepare($this->conexion, "SELECT * FROM usuario WHERE idUsuario = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $usuario = mysqli_fetch_assoc($res) ?: null;
        mysqli_stmt_close($stmt);
        return $usuario;
    }

    public function editarUsuario(int $id): void {
        $nombre        = trim($_POST['nombre'] ?? '');
        $apellido      = trim($_POST['apellido'] ?? '');
        $tipoDocumento = trim($_POST['tipoDocumento'] ?? '');
        $nroDocumento  = trim($_POST['nroDocumento'] ?? '');
        $email         = trim($_POST['email'] ?? '');
        $telefono      = trim($_POST['telefono'] ?? '');
        $rol           = strtolower(trim($_POST['rol'] ?? ''));
        $idAerolinea   = !empty($_POST['idAerolinea']) ? (int)$_POST['idAerolinea'] : null;
        $activo        = isset($_POST['activo']) ? (int)$_POST['activo'] : 0;

        $actual = $this->obtenerUsuarioPorId($id);
        if (!$actual) {
            flash_set('error', 'Usuario no encontrado.');
            redirect('index.php?pagina=usuario&seccion=listado');
        }

        // 1. Evitar que un Admin desactive su propia cuenta
        if ($activo === 0 && isset($_SESSION['usuario']['idUsuario']) && (int)$_SESSION['usuario']['idUsuario'] === $id) {
            flash_set('error', 'No podés desactivar tu propia cuenta de administrador.');
            redirect('index.php?pagina=usuario&seccion=listado');
        }

        // 2. Regla Rol - Aerolínea
        if ($rol === 'ceo') {
            if (!$idAerolinea) {
                flash_set('error', 'Debe seleccionar una aerolínea asociada para el rol CEO.');
                redirect('index.php?pagina=usuario&seccion=editar&id=' . $id);
            }
        } else {
            $idAerolinea = null;
        }

        // 3. Validaciones de requeridos y email
        if (!textoValido($nombre, 100) || !textoValido($apellido, 100) || !textoValido($tipoDocumento, 20) ||
            !textoValido($nroDocumento, 30) || !textoValido($email, 254) || !textoValido($telefono, 25) ||
            !textoValido($rol, 20)) {
            flash_set('error', 'Por favor completá todos los campos obligatorios.');
            redirect('index.php?pagina=usuario&seccion=editar&id=' . $id);
        }

        if (!in_array($tipoDocumento, ['DNI', 'pasaporte', 'lc', 'le'], true)) {
            flash_set('error', 'El tipo de documento seleccionado no es válido.');
            redirect('index.php?pagina=usuario&seccion=editar&id=' . $id);
        }

        if (!in_array($rol, ['cliente', 'ceo'], true)) {
            flash_set('error', 'El rol seleccionado no es válido.');
            redirect('index.php?pagina=usuario&seccion=editar&id=' . $id);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash_set('error', 'El correo electrónico no es válido.');
            redirect('index.php?pagina=usuario&seccion=editar&id=' . $id);
        }

        if (!telefonoValidoFormato($telefono)) {
            flash_set('error', 'El teléfono no tiene un formato válido.');
            redirect('index.php?pagina=usuario&seccion=editar&id=' . $id);
        }

        // 4. Resolver el estado de aprobación del CEO
        $rolActual    = strtolower(trim($actual['rol'] ?? ''));
        $fechaActual  = $actual['fecha_hora_autorizacion'] ?? null;
        $activoActual = (int)($actual['activo'] ?? 0);

        $esCEOPendiente = $rolActual === 'ceo'
            && $fechaActual === null
            && $activoActual === 0;
        $esCEODenegado = $rolActual === 'ceo'
            && $fechaActual === FECHA_DENEGACION_CEO;

        if ($rol !== 'ceo') {
            // Si deja de ser CEO, se limpia cualquier estado de aprobación.
            $fechaAutorizacion = null;
        } elseif ($rolActual !== 'ceo') {
            // Nuevo CEO desde edición: si se marca activo, queda aprobado; si no, pendiente.
            $fechaAutorizacion = $activo === 1 ? date('Y-m-d H:i:s') : null;
        } elseif ($esCEOPendiente || $esCEODenegado) {
            // Pendientes y denegados se resuelven únicamente desde la pantalla de CEOs.
            $activo            = $activoActual;
            $fechaAutorizacion = $fechaActual;
        } elseif ($fechaActual === null && $activoActual === 1) {
            // CEO activo legacy sin fecha: al editarlo se fija su fecha de aprobación.
            $fechaAutorizacion = $actual['fechaCreacion'] ?? date('Y-m-d H:i:s');
        } else {
            // CEO ya aprobado: se conserva su fecha y se permite activar/desactivar.
            $fechaAutorizacion = $fechaActual;
        }

        // 5. Actualización en BD con manejo de excepciones por clave duplicada
        try {
            $sql = "UPDATE usuario SET
                        nombre = ?,
                        apellido = ?,
                        tipoDocumento = ?,
                        nroDocumento = ?,
                        email = ?,
                        telefono = ?,
                        rol = ?,
                        idAerolinea = ?,
                        activo = ?,
                        fecha_hora_autorizacion = ?
                    WHERE idUsuario = ?";

            $stmt = mysqli_prepare($this->conexion, $sql);
            mysqli_stmt_bind_param(
                $stmt,
                "sssssssiisi",
                $nombre,
                $apellido,
                $tipoDocumento,
                $nroDocumento,
                $email,
                $telefono,
                $rol,
                $idAerolinea,
                $activo,
                $fechaAutorizacion,
                $id
            );

            $exito = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            if ($exito) {
                flash_set('success', 'Usuario actualizado correctamente.');
                redirect('index.php?pagina=usuario&seccion=listado');
            }
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1062) {
                flash_set('error', 'El número de documento o el email ya pertenecen a otro usuario.');
            } else {
                flash_set('error', 'Ocurrió un error al intentar actualizar el usuario.');
            }
            redirect('index.php?pagina=usuario&seccion=editar&id=' . $id);
        }
    }

    public function listarUsuariosPaginado(
        int $pagina = 1,
        int $porPagina = 10,
        string $estado = '',
        string $busqueda = '',
        string $rol = ''
    ): array {
        $offset = ($pagina - 1) * $porPagina;
        $where = ["u.rol <> 'admin'"];
        $params = [];
        $types = "";

        // Los CEOs pendientes y denegados se gestionan en la pantalla de CEOs.
        $where[] = "NOT (
                        u.rol = 'ceo'
                        AND (
                            (u.activo = 0 AND u.fecha_hora_autorizacion IS NULL)
                            OR (u.fecha_hora_autorizacion IS NOT NULL AND u.fecha_hora_autorizacion = ?)
                        )
                    )";
        $params[] = FECHA_DENEGACION_CEO;
        $types .= "s";

        if (in_array($rol, ['cliente', 'ceo'], true)) {
            $where[] = "u.rol = ?";
            $params[] = $rol;
            $types .= "s";
        }

        if ($estado === 'sin-verificar') {
            $where[] = "u.emailVerificado = 0";
        } elseif ($estado === '1' || $estado === '0') {
            $where[] = "u.activo = ?";
            $params[] = (int)$estado;
            $types .= "i";
        }

        if (!empty($busqueda)) {
            $where[] = "(u.nombre LIKE ? OR u.apellido LIKE ? OR u.email LIKE ? OR u.nroDocumento LIKE ?)";
            $searchTerm = "%{$busqueda}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "ssss";
        }

        $whereClause = implode(" AND ", $where);

        $queryCount = "SELECT COUNT(*) as total FROM usuario u WHERE {$whereClause}";
        $stmtCount = mysqli_prepare($this->conexion, $queryCount);
        if (!empty($params)) {
            mysqli_stmt_bind_param($stmtCount, $types, ...$params);
        }
        mysqli_stmt_execute($stmtCount);
        $resCount = mysqli_stmt_get_result($stmtCount);
        $total = mysqli_fetch_assoc($resCount)['total'] ?? 0;
        mysqli_stmt_close($stmtCount);

        $queryData = "SELECT u.idUsuario, u.nombre, u.apellido, u.tipoDocumento, u.nroDocumento, 
                             u.email, u.telefono, u.rol, u.activo, u.emailVerificado, u.fechaCreacion,
                             u.fecha_hora_autorizacion, a.nombreAerolinea
                      FROM usuario u
                      LEFT JOIN aerolinea a ON u.idAerolinea = a.idAerolinea
                      WHERE {$whereClause}
                      ORDER BY u.idUsuario DESC
                      LIMIT ? OFFSET ?";

        $paramsData = $params;
        $paramsData[] = $porPagina;
        $paramsData[] = $offset;
        $typesData = $types . "ii";

        $stmtData = mysqli_prepare($this->conexion, $queryData);
        mysqli_stmt_bind_param($stmtData, $typesData, ...$paramsData);
        mysqli_stmt_execute($stmtData);
        $resData = mysqli_stmt_get_result($stmtData);

        $usuarios = [];
        while ($row = mysqli_fetch_assoc($resData)) {
            $usuarios[] = $row;
        }
        mysqli_stmt_close($stmtData);

        return [
            'data'         => $usuarios,
            'total'        => (int)$total,
            'pagina'       => $pagina,
            'porPagina'    => $porPagina,
            'totalPaginas' => ceil($total / $porPagina)
        ];
    }

    public function obtenerEstadisticasUsuarios(): array {
        $fechaDenegacion = FECHA_DENEGACION_CEO;

        // Universo visible del listado: sin administradores y sin CEOs
        // pendientes o denegados (esos se gestionan en la pantalla de CEOs).
        $visibles = "u.rol <> 'admin'
                     AND NOT (
                         u.rol = 'ceo'
                         AND (
                             (u.activo = 0 AND u.fecha_hora_autorizacion IS NULL)
                             OR (u.fecha_hora_autorizacion IS NOT NULL AND u.fecha_hora_autorizacion = '{$fechaDenegacion}')
                         )
                     )";

        $total = $this->obtenerEscalar("SELECT COUNT(*) as total FROM usuario u WHERE {$visibles}", 'total');
        $nuevosEsteMes = $this->obtenerEscalar("SELECT COUNT(*) as nuevos FROM usuario u WHERE {$visibles} AND MONTH(u.fechaCreacion) = MONTH(CURRENT_DATE()) AND YEAR(u.fechaCreacion) = YEAR(CURRENT_DATE())", 'nuevos');
        $mesAnterior = $this->obtenerEscalar("SELECT COUNT(*) as anterior FROM usuario u WHERE {$visibles} AND MONTH(u.fechaCreacion) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH) AND YEAR(u.fechaCreacion) = YEAR(CURRENT_DATE() - INTERVAL 1 MONTH)", 'anterior');

        $diff = $nuevosEsteMes - $mesAnterior;

        // El KPI de roles refleja los usuarios visibles en el listado:
        // clientes y CEOs aprobados. Los admins quedan fuera del panel.
        $stmtRoles = mysqli_prepare($this->conexion,
            "SELECT u.rol, COUNT(*) as cantidad
             FROM usuario u
             WHERE u.rol IN ('cliente', 'ceo')
               AND (
                   u.rol <> 'ceo'
                   OR (u.fecha_hora_autorizacion IS NOT NULL AND u.fecha_hora_autorizacion <> '{$fechaDenegacion}')
                   OR (u.fecha_hora_autorizacion IS NULL AND u.activo = 1)
               )
             GROUP BY u.rol");
        mysqli_stmt_execute($stmtRoles);
        $resRoles = mysqli_stmt_get_result($stmtRoles);
        $roles = ['cliente' => 0, 'ceo' => 0];
        while ($row = mysqli_fetch_assoc($resRoles)) {
            $roles[$row['rol']] = (int)$row['cantidad'];
        }
        mysqli_stmt_close($stmtRoles);

        return [
            'total'         => (int)$total,
            'nuevosEsteMes' => (int)$nuevosEsteMes,
            'diff'          => (int)$diff,
            'clientes'      => $roles['cliente'] ?? 0,
            'ceos'          => $roles['ceo'] ?? 0
        ];
    }

    /**
     * Listado paginado de CEOs por estado.
     *
     * Estados admitidos:
     * - todos / ''  : sin filtro de estado.
     * - revision    : pendientes y denegados (comportamiento histórico).
     * - aprobado    : fecha real distinta de la centinela, o CEO activo legacy sin fecha.
     * - pendiente   : activo = 0 y fecha NULL.
     * - denegado    : fecha centinela.
     */
    public function listarCEOsPaginado(
        int $pagina = 1,
        int $porPagina = 10,
        string $estado = '',
        string $busqueda = ''
    ): array {
        $porPagina = max(1, $porPagina);
        $where = ["u.rol = 'ceo'"];
        $params = [];
        $types = "";

        if ($estado === 'pendiente') {
            $where[] = "u.activo = 0 AND u.fecha_hora_autorizacion IS NULL";
        } elseif ($estado === 'denegado') {
            $where[] = "u.fecha_hora_autorizacion = ?";
            $params[] = FECHA_DENEGACION_CEO;
            $types .= "s";
        } elseif ($estado === 'aprobado') {
            $where[] = "(
                (u.fecha_hora_autorizacion IS NOT NULL AND u.fecha_hora_autorizacion <> ?)
                OR (u.fecha_hora_autorizacion IS NULL AND u.activo = 1)
            )";
            $params[] = FECHA_DENEGACION_CEO;
            $types .= "s";
        } elseif ($estado === 'revision') {
            $where[] = "(
                (u.activo = 0 AND u.fecha_hora_autorizacion IS NULL)
                OR u.fecha_hora_autorizacion = ?
            )";
            $params[] = FECHA_DENEGACION_CEO;
            $types .= "s";
        }

        if ($busqueda !== '') {
            $where[] = "(u.nombre LIKE ?
                         OR u.apellido LIKE ?
                         OR u.email LIKE ?
                         OR u.nroDocumento LIKE ?
                         OR a.nombreAerolinea LIKE ?)";
            $like = "%{$busqueda}%";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $types .= "sssss";
        }

        $whereClause = implode(" AND ", $where);
        $from = "FROM usuario u
                 LEFT JOIN aerolinea a ON u.idAerolinea = a.idAerolinea
                 WHERE {$whereClause}";

        $stmtCount = mysqli_prepare($this->conexion, "SELECT COUNT(*) AS total {$from}");
        if (!$stmtCount) {
            return [
                'data'         => [],
                'total'        => 0,
                'pagina'       => 1,
                'porPagina'    => $porPagina,
                'totalPaginas' => 1
            ];
        }
        if ($params) {
            mysqli_stmt_bind_param($stmtCount, $types, ...$params);
        }
        mysqli_stmt_execute($stmtCount);
        $total = (int)(mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCount))['total'] ?? 0);
        mysqli_stmt_close($stmtCount);

        $totalPaginas = max(1, (int)ceil($total / $porPagina));
        $pagina       = min(max(1, $pagina), $totalPaginas);
        $offset       = ($pagina - 1) * $porPagina;

        $queryData = "SELECT u.idUsuario, u.nombre, u.apellido, u.tipoDocumento, u.nroDocumento,
                             u.email, u.telefono, u.fechaNacimiento, u.rol, u.activo,
                             u.emailVerificado, u.fecha_hora_autorizacion, u.fechaCreacion,
                             a.nombreAerolinea
                      {$from}
                      ORDER BY u.fechaCreacion DESC, u.idUsuario DESC
                      LIMIT ? OFFSET ?";

        $paramsData = $params;
        $paramsData[] = $porPagina;
        $paramsData[] = $offset;
        $typesData = $types . "ii";

        $stmtData = mysqli_prepare($this->conexion, $queryData);
        if (!$stmtData) {
            return [
                'data'         => [],
                'total'        => $total,
                'pagina'       => $pagina,
                'porPagina'    => $porPagina,
                'totalPaginas' => $totalPaginas
            ];
        }
        mysqli_stmt_bind_param($stmtData, $typesData, ...$paramsData);
        mysqli_stmt_execute($stmtData);
        $resData = mysqli_stmt_get_result($stmtData);

        $ceos = [];
        while ($row = mysqli_fetch_assoc($resData)) {
            $ceos[] = $row;
        }
        mysqli_stmt_close($stmtData);

        return [
            'data'         => $ceos,
            'total'        => $total,
            'pagina'       => $pagina,
            'porPagina'    => $porPagina,
            'totalPaginas' => $totalPaginas
        ];
    }

    public function obtenerEstadisticasCEOs(): array {
        $fechaDenegacion = FECHA_DENEGACION_CEO;

        $query = "SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN activo = 0 AND fecha_hora_autorizacion IS NULL THEN 1 ELSE 0 END) AS pendientes,
                    SUM(CASE WHEN fecha_hora_autorizacion = ? THEN 1 ELSE 0 END) AS denegados
                  FROM usuario
                  WHERE rol = 'ceo'";

        $stmt = mysqli_prepare($this->conexion, $query);
        if (!$stmt) {
            return ['total' => 0, 'pendientes' => 0, 'denegados' => 0, 'aprobados' => 0];
        }

        mysqli_stmt_bind_param($stmt, 's', $fechaDenegacion);
        mysqli_stmt_execute($stmt);
        $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt)) ?: [];
        mysqli_stmt_close($stmt);

        $total      = (int)($row['total'] ?? 0);
        $pendientes = (int)($row['pendientes'] ?? 0);
        $denegados  = (int)($row['denegados'] ?? 0);

        return [
            'total'      => $total,
            'pendientes' => $pendientes,
            'denegados'  => $denegados,
            'aprobados'  => max(0, $total - $pendientes - $denegados)
        ];
    }

    /**
     * Aprueba o deniega un CEO pendiente.
     * Solo actúa sobre CEOs con activo=0 y fecha_hora_autorizacion NULL.
     */
    public function resolverEstadoCEO(string $estado): void {
        if (!in_array($estado, ['aprobado', 'denegado'], true)) {
            flash_set('error', 'Acción no válida.');
            redirect('index.php?pagina=ceos&seccion=listado');
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            flash_set('error', 'CEO no válido.');
            redirect('index.php?pagina=ceos&seccion=listado');
        }

        $stmt = mysqli_prepare($this->conexion,
            "SELECT nombre, apellido, email
             FROM usuario
             WHERE idUsuario = ? AND rol = 'ceo'");

        if (!$stmt) {
            flash_set('error', 'No se pudo consultar el CEO.');
            redirect('index.php?pagina=ceos&seccion=listado');
        }

        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $ceo = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        if (!$ceo) {
            flash_set('error', 'CEO no encontrado.');
            redirect('index.php?pagina=ceos&seccion=listado');
        }

        try {
            if ($estado === 'aprobado') {
                $stmt = mysqli_prepare($this->conexion,
                    "UPDATE usuario
                     SET activo = 1, fecha_hora_autorizacion = NOW()
                     WHERE idUsuario = ?
                       AND rol = 'ceo'
                       AND activo = 0
                       AND fecha_hora_autorizacion IS NULL");

                if (!$stmt) {
                    flash_set('error', 'No se pudo actualizar el estado del CEO.');
                    redirect('index.php?pagina=ceos&seccion=listado');
                }

                mysqli_stmt_bind_param($stmt, 'i', $id);
            } else {
                $fechaDenegacion = FECHA_DENEGACION_CEO;
                $stmt = mysqli_prepare($this->conexion,
                    "UPDATE usuario
                     SET activo = 0, fecha_hora_autorizacion = ?
                     WHERE idUsuario = ?
                       AND rol = 'ceo'
                       AND activo = 0
                       AND fecha_hora_autorizacion IS NULL");

                if (!$stmt) {
                    flash_set('error', 'No se pudo actualizar el estado del CEO.');
                    redirect('index.php?pagina=ceos&seccion=listado');
                }

                mysqli_stmt_bind_param($stmt, 'si', $fechaDenegacion, $id);
            }

            mysqli_stmt_execute($stmt);
            $afectadas = mysqli_stmt_affected_rows($stmt);
            mysqli_stmt_close($stmt);

            if ($afectadas <= 0) {
                flash_set('error', 'No se pudo resolver: el CEO no existe o su solicitud ya fue resuelta.');
                redirect('index.php?pagina=ceos&seccion=listado');
            }

            $emailOk = $estado === 'aprobado'
                ? EmailService::enviarAprobacionCEO($ceo['email'], $ceo['nombre'])
                : EmailService::enviarDenegacionCEO($ceo['email'], $ceo['nombre']);

            $mensaje = $estado === 'aprobado'
                ? 'CEO aprobado correctamente.'
                : 'CEO denegado correctamente.';

            $mensaje .= $emailOk
                ? ' Se envió la notificación por email.'
                : ' No se pudo enviar la notificación por email.';

            flash_set('success', $mensaje);
        } catch (mysqli_sql_exception $e) {
            flash_set('error', 'Ocurrió un error al actualizar el estado del CEO.');
        }

        redirect('index.php?pagina=ceos&seccion=listado');
    }

    public function toggleEstadoUsuario(int $id): void {
        if ($id <= 0) {
            flash_set('error', 'ID de usuario no válido.');
            redirect('index.php?pagina=usuario&seccion=listado');
        }

        if (isset($_SESSION['usuario']['idUsuario']) && (int)$_SESSION['usuario']['idUsuario'] === $id) {
            flash_set('error', 'No podés desactivar tu propia cuenta de administrador.');
            redirect('index.php?pagina=usuario&seccion=listado');
        }

        $stmt = mysqli_prepare($this->conexion,
            "SELECT nombre, apellido, rol, activo, fecha_hora_autorizacion
             FROM usuario WHERE idUsuario = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $usuario = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);

        if (!$usuario) {
            flash_set('error', 'Usuario no encontrado.');
            redirect('index.php?pagina=usuario&seccion=listado');
        }

        $esCEO          = strtolower($usuario['rol'] ?? '') === 'ceo';
        $fechaEstado    = $usuario['fecha_hora_autorizacion'] ?? null;
        $esCEOPendiente = $esCEO && $fechaEstado === null && (int)$usuario['activo'] === 0;
        $esCEODenegado  = $esCEO && $fechaEstado === FECHA_DENEGACION_CEO;

        if ($esCEOPendiente || $esCEODenegado) {
            flash_set('error', 'Los CEOs pendientes o denegados se resuelven desde la pantalla de CEOs.');
            redirect('index.php?pagina=usuario&seccion=listado');
        }

        $nuevoEstado = $usuario['activo'] ? 0 : 1;

        // CEO activo legacy sin fecha: al desactivarlo se fija su fecha de aprobación
        // para que no vuelva a aparecer como pendiente.
        if ($esCEO && $nuevoEstado === 0 && $fechaEstado === null) {
            $stmtUpdate = mysqli_prepare($this->conexion,
                "UPDATE usuario SET activo = ?, fecha_hora_autorizacion = NOW() WHERE idUsuario = ?");
        } else {
            $stmtUpdate = mysqli_prepare($this->conexion,
                "UPDATE usuario SET activo = ? WHERE idUsuario = ?");
        }

        mysqli_stmt_bind_param($stmtUpdate, "ii", $nuevoEstado, $id);
        mysqli_stmt_execute($stmtUpdate);
        mysqli_stmt_close($stmtUpdate);

        $accionTexto = $nuevoEstado ? 'activado' : 'desactivado';
        flash_set('success', "Usuario {$usuario['nombre']} {$usuario['apellido']} {$accionTexto} correctamente.");
        redirect('index.php?pagina=usuario&seccion=listado');
    }

    public function actualizarPerfilPropio(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $tokenPost = $_POST['csrf_token'] ?? '';
        $tokenSession = $_SESSION['csrf_token'] ?? '';

        if (empty($tokenPost) || empty($tokenSession) || !hash_equals($tokenSession, $tokenPost)) {
            flash_set('error', 'Token de seguridad inválido o sesión expirada.');
            redirect('index.php?pagina=perfil');
        }

        $idUsuario = (int)($_POST['idUsuario'] ?? 0);
        if (!isset($_SESSION['usuario']) || (int)$_SESSION['usuario']['idUsuario'] !== $idUsuario) {
            flash_set('error', 'Acción no autorizada.');
            redirect('index.php?pagina=login');
        }

        $nombre        = trim($_POST['nombre'] ?? '');
        $apellido      = trim($_POST['apellido'] ?? '');
        $tipoDocumento = trim($_POST['tipoDocumento'] ?? '');
        $nroDocumento  = trim($_POST['nroDocumento'] ?? '');
        $email         = trim($_POST['email'] ?? '');
        $telefono      = trim($_POST['telefono'] ?? '');
        
        $passActual = $_POST['passwordActual'] ?? null;
        $passNueva  = $_POST['passwordNueva'] ?? null;

        if (!textoValido($nombre, 100) || !textoValido($apellido, 100) || !textoValido($tipoDocumento, 20) ||
            !textoValido($nroDocumento, 30) || !textoValido($email, 254) || !textoValido($telefono, 25)) {
            flash_set('error', 'Por favor completá todos los campos obligatorios.');
            redirect('index.php?pagina=perfil');
        }
        if (!in_array($tipoDocumento, ['DNI', 'pasaporte', 'lc', 'le'], true)) {
            flash_set('error', 'El tipo de documento seleccionado no es válido.');
            redirect('index.php?pagina=perfil');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash_set('error', 'El correo electrónico no es válido.');
            redirect('index.php?pagina=perfil');
        }
        if (!telefonoValidoFormato($telefono)) {
            flash_set('error', 'El teléfono no tiene un formato válido.');
            redirect('index.php?pagina=perfil');
        }

        // Si quiere cambiar la contraseña, validamos la actual
        if (!empty($passNueva)) {
            $usuarioActual = $this->obtenerUsuarioPorId($idUsuario);
            if (!password_verify($passActual, $usuarioActual['contrasena'])) {
                flash_set('error', 'La contraseña actual es incorrecta.');
                redirect('index.php?pagina=perfil');
            }
            $hash = password_hash($passNueva, PASSWORD_DEFAULT);
            $sql = "UPDATE usuario SET nombre = ?, apellido = ?, tipoDocumento = ?, nroDocumento = ?, email = ?, telefono = ?, contrasena = ? WHERE idUsuario = ?";
            $stmt = mysqli_prepare($this->conexion, $sql);
            mysqli_stmt_bind_param($stmt, "sssssssi", $nombre, $apellido, $tipoDocumento, $nroDocumento, $email, $telefono, $hash, $idUsuario);
        } else {
            $sql = "UPDATE usuario SET nombre = ?, apellido = ?, tipoDocumento = ?, nroDocumento = ?, email = ?, telefono = ? WHERE idUsuario = ?";
            $stmt = mysqli_prepare($this->conexion, $sql);
            mysqli_stmt_bind_param($stmt, "ssssssi", $nombre, $apellido, $tipoDocumento, $nroDocumento, $email, $telefono, $idUsuario);
        }

        try {
            $exito = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            if ($exito) {
                // Actualizamos los datos en la sesión actual
                $_SESSION['usuario']['nombre'] = $nombre;
                $_SESSION['usuario']['apellido'] = $apellido;
                $_SESSION['usuario']['email'] = $email;
                flash_set('success', 'Perfil actualizado correctamente.');
            }
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1062) {
                flash_set('error', 'El número de documento o el email ya pertenecen a otro usuario.');
            } else {
                flash_set('error', 'Ocurrió un error al intentar actualizar el perfil.');
            }
        }

        redirect('index.php?pagina=perfil');
    }

    public function obtenerNotificacionesPendientes(): array {
        // 1. CEOs pendientes
        $sqlCEOs = "SELECT idUsuario AS id, nombre, apellido, email, fechaCreacion, 'ceo' AS tipo 
                    FROM usuario 
                    WHERE rol = 'ceo' AND activo = 0 AND fecha_hora_autorizacion IS NULL AND notificacion_vista = 0
                    ORDER BY fechaCreacion DESC LIMIT 5";
        $stmtCEOs = mysqli_prepare($this->conexion, $sqlCEOs);
        if (!$stmtCEOs) return ['ceos' => [], 'promociones' => [], 'total' => 0];
        mysqli_stmt_execute($stmtCEOs);
        $resCEOs = mysqli_stmt_get_result($stmtCEOs);
        $ceos = [];
        if ($resCEOs) {
            while ($row = mysqli_fetch_assoc($resCEOs)) {
                $ceos[] = $row;
            }
            mysqli_stmt_close($stmtCEOs);
        }

        // 2. Promociones pendientes
        $sqlPromos = "SELECT p.idPromocion AS id, p.codigo, p.nombre, a.nombreAerolinea, p.fechaCreacion, 'promocion' AS tipo 
                      FROM promocion p 
                      JOIN aerolinea a ON p.idAerolinea = a.idAerolinea 
                      WHERE p.estado = 'Pendiente' AND p.notificacion_vista = 0
                      ORDER BY p.fechaCreacion DESC LIMIT 5";
        $stmtPromos = mysqli_prepare($this->conexion, $sqlPromos);
        if (!$stmtPromos) return ['ceos' => $ceos, 'promociones' => [], 'total' => count($ceos)];
        mysqli_stmt_execute($stmtPromos);
        $resPromos = mysqli_stmt_get_result($stmtPromos);
        $promos = [];
        if ($resPromos) {
            while ($row = mysqli_fetch_assoc($resPromos)) {
                $promos[] = $row;
            }
            mysqli_stmt_close($stmtPromos);
        }

        return [
            'ceos' => $ceos,
            'promociones' => $promos,
            'total' => count($ceos) + count($promos)
        ];
    }
}
