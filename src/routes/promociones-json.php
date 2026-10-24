<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../controllers/PromocionController.php';

try {
    $promocionCtrl = new PromocionController($link);
    
    $promociones = $promocionCtrl->obtenerUltimasPublicadas(3);
    
    echo json_encode([
        'success' => true,
        'promociones' => $promociones
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}