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
        $nombre = $_POST['nombre'] ?? null;
        $codigoIATA = $_POST['codigo'] ?? null;
        $codPais = $_POST['pais'] ?? null;
        $email = $_POST['email'] ?? null;
        $descripcion = $_POST['descripcion'] ?? null;
        $activo = $_POST["estadoAerolinea"] === "activa"? 1: 0;
        $logoUrl = null; 
        $logoPublicId = null;

        if (!$nombre || !$codigoIATA || !$codPais || !$email) {
            http_response_code(400);
            echo "datos incompletos";
            return;
        }

        if(isset($_FILES['logo']) && $_FILES['logo']['error']=== UPLOAD_ERR_OK){
            $cloudinaryController = new CloudinaryController();

            try {
                $resultado = $cloudinaryController->subirFoto($_FILES['logo']['tmp_name']);
                $logoUrl = $resultado['secure_url'];
                $logoPublicId = $resultado['public_id'];
            } catch (Exception $e) {
                http_response_code(500);
                echo "Error al subir la imagen: " . $e->getMessage();
                return;
            }

        }


        $aerolinea  = new Aerolinea($nombre, $codigoIATA, $descripcion, $codPais, $email, $logoUrl, $activo, null, $logoPublicId);



        $query = "INSERT INTO aerolinea (nombreAerolinea, codigoIATA, descripcion, codPais, email, logoUrl, activo, logoPublicId) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($this->conexion, $query);

        if(!$stmt){
            http_response_code(500);
            echo "Error al preparar la consulta";
            return;
        }

        $ok = mysqli_stmt_execute($stmt, [
            $aerolinea->getNombreAerolinea(),
            $aerolinea->getCodigoIATA(),
            $aerolinea->getDescripcion(),
            $aerolinea->getCodPais(),
            $aerolinea->getEmail(),
            $aerolinea->getLogoUrl(),
            $aerolinea->getActivo(),
            $aerolinea->getLogoPublicId()
        ]);

        mysqli_stmt_close($stmt);

        if(!$ok){
            http_response_code(500);
            echo "Error al crear la aerolinea";
            return;
        }

        // despues cambiar por aerolinea home ahora no hay nada ahi 
        header("Location: " . url('src/views/admin/aerolineaLayout.php'));

        exit;

    }

}



?>