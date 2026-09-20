<?php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../controllers/UsuarioController.php';

$ctrl = new UsuarioController($link);

$accion = $_GET['accion'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    switch ($accion) {
        case 'registrar':
            $ctrl->crearUsuario();
            break;
        case 'registrar-ceo':
            $ctrl->crearCEO();
            break;
        case 'login':
            $ctrl->login();
            break;
        case 'logout':
            $ctrl->logout();
            break;
        default:
            http_response_code(404);
            echo "Acción no encontrada";
            break;
    }

} else {
    // Permitimos logout por GET (es un caso típico)
    if ($accion === 'logout') {
        $ctrl->logout();
    } else {
        http_response_code(405);
        echo "No permitido";
    }
}