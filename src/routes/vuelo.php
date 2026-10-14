<?php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../controllers/VueloController.php';

requireRol('ceo');

$idAerolinea = aerolineaCEO();
if (!$idAerolinea) {
    flash_set('error', 'No tenés una aerolínea asignada.');
    redirect('index.php?pagina=login');
}

$ctrl   = new VueloController($link);
$accion = $_GET['accion'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $tokenEnviado = $_POST['csrf_token'] ?? '';
    $tokenSesion  = $_SESSION['csrf_token'] ?? '';

    if (!$tokenEnviado || !$tokenSesion || !hash_equals($tokenSesion, $tokenEnviado)) {
        flash_set('error', 'Token de seguridad inválido. Intentá de nuevo.');
        redirect('index.php?pagina=vuelos');
    }

    switch ($accion) {
        case 'crear':
            $ctrl->crearVuelo($idAerolinea);
            break;
        case 'editar':
            $id = (int)($_GET['id'] ?? 0);
            if (!$id) { flash_set('error', 'ID inválido.'); redirect('index.php?pagina=vuelos'); }
            $ctrl->editarVuelo($id, $idAerolinea);
            break;
        case 'eliminar':
            $id = (int)($_POST['id'] ?? 0);
            if (!$id) { flash_set('error', 'ID inválido.'); redirect('index.php?pagina=vuelos'); }
            $ctrl->eliminarVuelo($id, $idAerolinea);
            break;
        default:
            http_response_code(404);
            echo "Acción no encontrada";
    }
} else {
    http_response_code(405);
    echo "No permitido";
}

?>