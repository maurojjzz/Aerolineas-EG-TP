<?php 

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/Aerolinea.php';
require_once __DIR__ . '/CloudinaryController.php';

class AerolineaController {

    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function crearAerolinea(): void {
        $nombre = trim($_POST['nombre'] ?? null);
        $codigoIATA = trim($_POST['codigo'] ?? null);
        $codPais = trim($_POST['pais'] ?? null);
        $email = trim($_POST['email'] ?? null);
        $descripcion = trim($_POST['descripcion'] ?? null);
        $activo = $_POST["estadoAerolinea"] === "activa"? 1: 0;
        $logoUrl = null; 
        $logoPublicId = null;

        if (!$nombre || !$codigoIATA || !$codPais || !$email) {
            flash_set('error', 'Faltan campos obligatorios');
            redirect("index.php?pagina=aerolinea&seccion=alta");
        }

        if(isset($_FILES['logo']) && $_FILES['logo']['error']=== UPLOAD_ERR_OK){
            $cloudinaryController = new CloudinaryController();

            try {
                $resultado = $cloudinaryController->subirFoto($_FILES['logo']['tmp_name']);
                $logoUrl = $resultado['secure_url'];
                $logoPublicId = $resultado['public_id'];
            } catch (Exception $e) {
                flash_set('error', 'Error al subir la imagen: ' . $e->getMessage());
                redirect("index.php?pagina=aerolinea&seccion=alta");
            }
        }

        $aerolinea  = new Aerolinea($nombre, $codigoIATA, $descripcion, $codPais, $email, $logoUrl, $activo, null, $logoPublicId);

        $query = "INSERT INTO aerolinea (nombreAerolinea, codigoIATA, descripcion, codPais, email, logoUrl, activo, logoPublicId) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($this->conexion, $query);

        if(!$stmt){
            flash_set('error', 'Error interno del servidor');
            redirect("index.php?pagina=aerolinea&seccion=alta");
        }

        $nombreBD = $aerolinea->getNombreAerolinea();
        $codigoBD= $aerolinea->getCodigoIATA();
        $descripcionBD = $aerolinea->getDescripcion();
        $paisBD = $aerolinea->getCodPais();
        $emailBD = $aerolinea->getEmail();
        $logoUrlBD = $aerolinea->getLogoUrl();
        $activoBD = $aerolinea->getActivo() ? 1 : 0;
        $logoPubIdBD = $aerolinea->getLogoPublicId();

        mysqli_stmt_bind_param(
            $stmt,
            "sssssisi",
            $nombreBD,
            $codigoBD,
            $descripcionBD,
            $paisBD,
            $emailBD,
            $logoUrlBD,
            $activoBD,
            $logoPubIdBD
        );

        try {
            mysqli_stmt_execute($stmt);
        } catch (mysqli_sql_exception $e) {
            $errno = $e->getCode();
            $error = $e->getMessage();
            mysqli_stmt_close($stmt);

            if ($errno === 1062) {
                flash_set('error', 'Ya existe una aerolínea con ese código IATA.');
            } else {
                flash_set('error', 'Error al crear la aerolínea.');
            }
            redirect('index.php?pagina=aerolinea&seccion=alta');
        }

        mysqli_stmt_close($stmt);

        flash_set('success', 'Aerolínea creada correctamente.');
        redirect('index.php?pagina=aerolinea&seccion=listado');

    }


    public function listarAerolineas(): array {
        $query = "SELECT * FROM aerolinea";
        $result = mysqli_query($this->conexion, $query);

        if (!$result) {
            return [];
        }

        $aerolineas = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $aerolinea = new Aerolinea(
                $row['nombreAerolinea'],
                $row['codigoIATA'],
                $row['descripcion'],
                $row['codPais'],
                $row['email'],
                $row['logoUrl'],
                (bool)$row['activo'],
                (int)$row['idAerolinea'],
                $row['logoPublicId']
            );
            $aerolineas[] = $aerolinea;
        }

        mysqli_free_result($result);

        return $aerolineas;
    }

    public function listarAerolineasConCEO(): array {
        $query = "SELECT a.*, u.nombre as ceoNombre, u.apellido as ceoApellido, u.email as ceoEmail
                FROM aerolinea a
                LEFT JOIN usuario u 
                    ON u.idAerolinea = a.idAerolinea AND u.rol = 'ceo'
                ORDER BY a.nombreAerolinea;";

        $result = mysqli_query($this->conexion, $query);
        if (!$result) return [];

        $filas = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $filas[] = $row;
        }
        mysqli_free_result($result);
        return $filas;
    }

    public function listarAerolineasPaginado(
        int $pagina,
        int $porPagina,
        string $estado = '',
        string $pais = '',
        string $conCeo = '',
        string $busqueda = ''
    ): array {

        $offset = ($pagina - 1) * $porPagina;
        $condiciones = [];
        $params = [];
        $tipos = '';

        if ($estado === 'activa') {
            $condiciones[] = "a.activo = 1";
        } elseif ($estado === 'inactiva') {
            $condiciones[] = "a.activo = 0";
        }

        if ($pais !== '') {
            $condiciones[] = "a.codPais = ?";
            $params[] = $pais;
            $tipos .= 's';
        }

        if ($conCeo === 'sin') {
            $condiciones[] = "u.idUsuario IS NULL";
        } elseif ($conCeo === 'con') {
            $condiciones[] = "u.idUsuario IS NOT NULL";
        }

        if ($busqueda !== '') {
            $condiciones[] = "(a.nombreAerolinea LIKE ? OR a.codigoIATA LIKE ?)";
            $like = "%$busqueda%";
            $params[] = $like;
            $params[] = $like;
            $tipos .= 'ss';
        }

        $where = count($condiciones) ? 'WHERE ' . implode(' AND ', $condiciones) : '';

        // Total sin LIMIT para calcular páginas
        $queryTotal = "SELECT COUNT(*) as total
                    FROM aerolinea a
                    LEFT JOIN usuario u ON u.idAerolinea = a.idAerolinea AND u.rol = 'ceo'
                    $where";

        $stmtTotal = mysqli_prepare($this->conexion, $queryTotal);

        if ($params) {
            mysqli_stmt_bind_param($stmtTotal, $tipos, ...$params);
        }

        mysqli_stmt_execute($stmtTotal);

        $total = (int) mysqli_fetch_assoc(mysqli_stmt_get_result($stmtTotal))['total'];
        mysqli_stmt_close($stmtTotal);

        // Query paginada
        $query = "SELECT a.*, u.nombre as ceoNombre, u.apellido as ceoApellido
                FROM aerolinea a
                LEFT JOIN usuario u ON u.idAerolinea = a.idAerolinea AND u.rol = 'ceo'
                $where
                ORDER BY a.nombreAerolinea
                LIMIT ? OFFSET ?";

        $params[] = $porPagina;
        $params[] = $offset;
        $tipos .= 'ii';

        $stmt = mysqli_prepare($this->conexion, $query);
        mysqli_stmt_bind_param($stmt, $tipos, ...$params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $filas = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $filas[] = $row;
        }
        mysqli_stmt_close($stmt);

        return [
            'data' => $filas,
            'total' => $total,
            'pagina' => $pagina,
            'porPagina' => $porPagina,
            'totalPaginas' => (int) ceil($total / $porPagina)
        ];
    }

    public function obtenerAerolineaPorId(int $id): ?array {
        $query = "SELECT a.*, u.nombre as ceoNombre, u.apellido as ceoApellido, u.email as ceoEmail
                FROM aerolinea a
                LEFT JOIN usuario u ON u.idAerolinea = a.idAerolinea AND u.rol = 'ceo'
                WHERE a.idAerolinea = ?";

        $stmt = mysqli_prepare($this->conexion, $query);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $fila = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        return $fila ?: null;
    }
    // aca la logica para las cards de aerolinea en el listado

    public function obtenerEstadisticasListado(): array {
        // Total aerolíneas este mes
        $queryEsteMes = "SELECT COUNT(*) as total FROM aerolinea 
                        WHERE MONTH(fechaCreacion) = MONTH(NOW()) 
                        AND YEAR(fechaCreacion) = YEAR(NOW())";

        // Total aerolíneas mes pasado
        $queryMesPasado = "SELECT COUNT(*) as total FROM aerolinea 
                        WHERE MONTH(fechaCreacion) = MONTH(DATE_SUB(NOW(), INTERVAL 1 MONTH)) 
                        AND YEAR(fechaCreacion) = YEAR(DATE_SUB(NOW(), INTERVAL 1 MONTH))";

        // Total CEOs 
        $queryCeos = "SELECT COUNT(*) as total FROM usuario 
                    WHERE rol = 'ceo' AND idAerolinea IS NOT NULL";

        $esteMes   = (int) mysqli_fetch_assoc(mysqli_query($this->conexion, $queryEsteMes))['total'];
        $mesPasado = (int) mysqli_fetch_assoc(mysqli_query($this->conexion, $queryMesPasado))['total'];
        $ceos      = (int) mysqli_fetch_assoc(mysqli_query($this->conexion, $queryCeos))['total'];

        $diff = $esteMes - $mesPasado;

        return [
            'nuevasEsteMes' => $esteMes,
            'mesPasado'     => $mesPasado,
            'diff'          => $diff,
            'ceos'          => $ceos,
        ];
    }



}



?>