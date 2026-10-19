<?php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../controllers/PromocionController.php';

$ctrl   = new PromocionController($link);
$accion = $_GET['accion'] ?? '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "No permitido";
    exit;
}

// CSRF
$tokenEnviado = $_POST['csrf_token'] ?? '';
$tokenSesion  = $_SESSION['csrf_token'] ?? '';
if (!$tokenEnviado || !$tokenSesion || !hash_equals($tokenSesion, $tokenEnviado)) {
    flash_set('error', 'Token de seguridad inválido.');
    redirect('index.php?pagina=promociones');
}

switch ($accion) {
    case 'crear':
        requireRol('ceo');
        $ctrl->crearPromocion();
        break;

    case 'eliminar':
        requireRol('ceo');
        $ctrl->eliminarPromocion();
        break;

    case 'aprobar':
        requireRol('admin');
        $ctrl->resolverEstado('Aprobada');
        break;

    case 'denegar':
        requireRol('admin');
        $ctrl->resolverEstado('Denegada');
        break;

    default:
        http_response_code(404);
        echo "Acción no encontrada";
}