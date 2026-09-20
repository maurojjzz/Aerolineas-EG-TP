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





}



?>