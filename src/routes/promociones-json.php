<?php

header('Content-Type: application/json');
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../controllers/PromocionController.php';
try {
    $promocionCtrl = new PromocionController($link);
    
    $promociones = $promocionCtrl->obtenerUltimasPublicadas(3);
    
    echo json_encode([
        'success' => true,
        'promociones' => $promociones
    ]);
} catch (Throwable $e) {
    error_log('Error en promociones-json.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'No pudimos cargar las promociones. Intentá nuevamente más tarde.'
    ], JSON_UNESCAPED_UNICODE);
}