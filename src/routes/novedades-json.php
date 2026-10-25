<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../controllers/NovedadController.php';

try {
    $novedadCtrl = new NovedadController($link);
    // Obtenemos las últimas 5 publicadas
    $novedades = $novedadCtrl->obtenerUltimasPublicadas(3);
    
    echo json_encode([
        'success' => true,
        'novedades' => $novedades
    ]);
} catch (Throwable $e) {
    error_log('Error en novedades-json.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'No pudimos cargar las novedades. Intentá nuevamente más tarde.'
    ], JSON_UNESCAPED_UNICODE);
}