<?php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../controllers/ReservaController.php';

requireRol('cliente');

$ctrl   = new ReservaController($link);
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
    redirect('index.php?pagina=mis-reservas');
}

switch ($accion) {
    case 'crear':
        $ctrl->crearReserva();
        break;

    case 'confirmar':
        $ctrl->confirmarPago();
        break;

    case 'cancelar':
        $ctrl->cancelarReserva();
        break;

    case 'ir-a-pago':
        $ctrl->irAPago();
        break;

    default:
        http_response_code(404);
        echo "Acción no encontrada";
}