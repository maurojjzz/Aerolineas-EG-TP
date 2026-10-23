<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../controllers/UsuarioController.php';

header('Content-Type: application/json');

// Validar que sea admin
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$ctrl = new UsuarioController($link);
$notificaciones = $ctrl->obtenerNotificacionesPendientes();

echo json_encode($notificaciones);