<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('America/Argentina/Buenos_Aires');

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
        requireRol('admin');
        require __DIR__ . '/src/views/admin/aerolineaLayout.php';
        break;

    case 'cliente':
        requireRol('cliente');
        require __DIR__ . '/src/views/cliente/home.php';
        break;

    case 'ceo':
        requireRol('ceo');
        require __DIR__ . '/src/views/ceo/home.php';
        break;

    case 'vuelos':
        requireRol('ceo');
        require __DIR__ . '/src/views/ceo/vueloListado.php';
        break;
        
    case 'vuelo':
        requireRol('ceo');
        require __DIR__ . '/src/views/ceo/vueloForm.php';
        break;

    case 'verificar':
        require __DIR__ . '/src/views/auth/verificarEmail.php';
        break;

    case 'reset-password':
        require __DIR__ . '/src/views/auth/resetPassword.php';
        break;

    case 'olvide-contrasena':
        soloInvitados();
        require __DIR__ . '/src/views/auth/olvideContrasena.php';
        break;

    default:
        http_response_code(404);
        echo 'Página no encontrada';
        break;
}

?>