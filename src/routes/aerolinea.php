<?php

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../controllers/AerolineaController.php';

$ctrl = new AerolineaController($link);

$accion = $_GET['accion'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    switch ($accion) {
        case 'crear':
            $ctrl->crearAerolinea();
            break;
        default:
            http_response_code(404);
            echo "Acción no encontrada";
            break;
    }

} else {
    http_response_code(405);
    echo "No permitido";
}


?>