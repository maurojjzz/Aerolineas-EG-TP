<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../controllers/NovedadController.php';

try {
    $novedadCtrl = new NovedadController($link);
    // Obtenemos las últimas 5 publicadas
    $novedades = $novedadCtrl->obtenerUltimasPublicadas(5);
    
    echo json_encode([
        'success' => true,
        'novedades' => $novedades
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}