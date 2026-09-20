<?php
date_default_timezone_set('America/Argentina/Buenos_Aires');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/src/config/app.php';


$pagina = $_GET['pagina'] ?? 'inicio';

switch ($pagina) {

    case 'inicio':
        require __DIR__ . '/src/views/home.php';
        break;

    case 'login':
        soloInvitados();
        require __DIR__ . '/src/views/auth/login.php';
        break;

    case 'registro':
        soloInvitados();
        require __DIR__ . '/src/views/auth/signUp.php';
        break;

    case 'registro-ceo':
        soloInvitados();
        require __DIR__ . '/src/views/auth/signUpCEO.php';
        break;

    case 'aerolinea':
        requireRol('admin', 'ceo'); // checkear dsp el ceo porque este es el alta de aerolinea el no tendria q verlo
        require __DIR__ . '/src/views/admin/aerolineaLayout.php';
        break;

    // ejemplos solo admin 
    // case 'dashboard-admin':
    //     requiereRol('admin');
    //     require __DIR__ . '/src/views/admin/dashboardAdmin.php';
    //     break;

    // ejemplo solo ceo
    // case 'dashboard-ceo':
    //     requiereRol('ceo');
    //     require __DIR__ . '/src/views/admin/dashboardCeo.php';
    //     break;

    default:
        http_response_code(404);
        echo 'Página no encontrada';
        break;
}

?>