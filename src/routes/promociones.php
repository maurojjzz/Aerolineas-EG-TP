<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../controllers/PromocionController.php';

$accion = $_GET['accion'] ?? '';
$ctrl = new PromocionController($link);

// El rol se exige POR ACCIÓN: el CEO crea, el admin resuelve.
switch ($accion) {
    case 'guardar':
        requireRol('ceo');
        $ctrl->guardar();
        break;

    case 'aprobar':
        requireRol('admin');
        $ctrl->resolver('Aprobada');
        break;

    case 'denegar':
        requireRol('admin');
        $ctrl->resolver('Denegada');
        break;

    default:
        redirect('index.php?pagina=inicio');
}