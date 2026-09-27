<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../controllers/AerolineaController.php';

requireRol('admin', 'ceo');

$ctrl = new AerolineaController($link);

$accion = $_GET['accion'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    switch ($accion) {
        case 'crear':
            if (!puedeCrearAerolinea()) {
                flash_set('error', 'No tienes permisos para crear aerolíneas.');
                redirect('index.php?pagina=inicio');
            }
            $ctrl->crearAerolinea();
            break;
        default:
            http_response_code(404);
            echo "Acción no encontrada";
            break;
    }

} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    switch ($accion) {
            case 'listar':
                $porPaginaPermitidos = [5, 10, 25, 50];

                $pagina = max(1, (int)($_GET['pagina']    ?? 1));
                $porPagina = in_array((int)($_GET['porPagina'] ?? 10), $porPaginaPermitidos)
                                ? (int)$_GET['porPagina']
                                : 10;
                $estado = $_GET['estado']   ?? '';
                $pais = $_GET['pais']     ?? '';
                $conCeo = $_GET['conCeo']   ?? '';
                $busqueda = trim($_GET['busqueda'] ?? '');

                $resultado = $ctrl->listarAerolineasPaginado(
                    $pagina, $porPagina, $estado, $pais, $conCeo, $busqueda
                );

                header('Content-Type: application/json');
                echo json_encode($resultado);
                exit;

            default:
                http_response_code(404);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Acción no encontrada']);
                exit;
        }
} else {
    http_response_code(405);
    echo "Método no permitido";
}


?>