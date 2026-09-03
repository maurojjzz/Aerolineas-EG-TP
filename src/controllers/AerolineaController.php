<?php 

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/Aerolinea.php';

class AerolineaController {

    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function crearAerolinea() {
        $nombre = $_POST['nombre'];
        $codigoIATA = $_POST['codigo'];
        $codPais = $_POST['pais'];
        $email = $_POST['email'];
        $descripcion = $_POST['descripcion'];
        // $logoUrl = $_POST['logo']; todavia no hay cloudinary
        $activo = $_POST["estadoAerolinea"] === "activa"? 1: 0;
        $logoUrl = null; //BORRAR CUANDO INTEGRE CLOUDINARY
        
        $aerolinea  = new Aerolinea($nombre, $codigoIATA, $descripcion, $codPais, $email, $logoUrl, $activo);

       print_r($aerolinea, true); 

        $query = "INSERT INTO aerolinea (nombreAerolinea, codigoIATA, descripcion, codPais, email, logoUrl, activo) VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($this->conexion, $query);


        mysqli_stmt_execute($stmt, [
            $aerolinea->getNombreAerolinea(),
            $aerolinea->getCodigoIATA(),
            $aerolinea->getDescripcion(),
            $aerolinea->getCodPais(),
            $aerolinea->getEmail(),
            $aerolinea->getLogoUrl(),
            $aerolinea->getActivo()
        ]);


        header("Location: /admin/aerolineaHome");

        exit;

    }

}



?>