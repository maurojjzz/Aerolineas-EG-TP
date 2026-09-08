<?php
date_default_timezone_set('America/Argentina/Buenos_Aires');

require_once __DIR__ . '/src/config/app.php';

$pagina = $_GET['pagina'] ?? 'inicio';

switch ($pagina) {

    case 'inicio':
        require __DIR__ . '/src/views/home.php';
        break;

    case 'aerolinea':
        require __DIR__ . '/src/views/admin/aerolineaLayout.php';
        break;

    default:
        http_response_code(404);
        echo 'Página no encontrada';
        break;
}







?>