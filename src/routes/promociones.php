<?php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../controllers/PromocionController.php';

$ctrl   = new PromocionController($link);
$accion = $_GET['accion'] ?? '';

// Endpoints de solo lectura para AJAX (no requieren POST ni CSRF)
if (in_array($accion, ['destinos-por-origen', 'obtener'], true)) {
    requireRol('ceo');
    header('Content-Type: application/json; charset=utf-8');
    
    if ($accion === 'destinos-por-origen') {
        $origen = $_GET['origen'] ?? '';
        echo json_encode($ctrl->obtenerDestinosPorOrigen($origen), JSON_UNESCAPED_UNICODE);
    } elseif ($accion === 'obtener') {
        $id = (int)($_GET['id'] ?? 0);
        $promo = $ctrl->obtenerParaEditar($id);
        echo json_encode($promo ?? [], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

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

    case 'editar':
        requireRol('ceo');
        $ctrl->editarPromocion();
        break;

    case 'eliminar':
        requireRol('ceo');
        $ctrl->eliminarPromocion();
        break;

    case 'cancelar':
        requireRol('ceo');
        $ctrl->cancelarPromocion();
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