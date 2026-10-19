<?php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../controllers/NovedadController.php';

requireRol('admin');

$ctrl   = new NovedadController($link);
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
    redirect('index.php?pagina=novedades&seccion=listado');
}

switch ($accion) {
    case 'crear':
        $ctrl->crearNovedad();
        break;

    case 'editar':
        $id = (int)($_POST['idNovedad'] ?? 0);
        if (!$id) {
            flash_set('error', 'ID de novedad inválido.');
            redirect('index.php?pagina=novedades&seccion=listado');
        }
        $ctrl->editarNovedad($id);
        break;

    case 'eliminar':
        $id = (int)($_POST['idNovedad'] ?? 0);
        if (!$id) {
            flash_set('error', 'ID de novedad inválido.');
            redirect('index.php?pagina=novedades&seccion=listado');
        }
        $ctrl->eliminarNovedad($id);
        break;

    default:
        http_response_code(404);
        echo "Acción no encontrada";
}