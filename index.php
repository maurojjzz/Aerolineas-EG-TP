<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
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

    case 'promociones':
        if (rolActual() === 'admin') {
            requireRol('admin');
            require __DIR__ . '/src/views/admin/promocionesLayout.php';
        } elseif (rolActual() === 'ceo') {
            requireRol('ceo');
            require __DIR__ . '/src/views/ceo/promocionesListado.php';
        } else {
            requireAuth();
            redirect('index.php?pagina=inicio');
        }
        break;

    case 'novedad':
    case 'novedades':
        requireRol('admin');
        require __DIR__ . '/src/views/admin/novedadLayout.php';
        break;

    case 'usuario':
    case 'usuarios':
        requireRol('admin');
        require __DIR__ . '/src/views/admin/usuarioLayout.php';
        break;

    case 'ceos':
        requireRol('admin');
        require __DIR__ . '/src/views/admin/ceoLayout.php';
        break;

    case 'reportes':
        requireRol('admin');
        require __DIR__ . '/src/views/admin/reportesLayout.php';
        break;

    case 'cliente':
        requireRol('cliente');
        require __DIR__ . '/src/views/cliente/home.php';
        break;

    case 'cliente-novedades':
        // if (!isset($_SESSION['usuario']) || (!in_array($_SESSION['usuario']['rol'], ['cliente', 'ceo']))) {
        //     header('Location: ' . url('index.php?pagina=login'));
        //     exit;
        // }
        require __DIR__ . '/src/views/cliente/novedadListado.php';
        break;

    case 'cliente-novedad-detalle':
        // if (!isset($_SESSION['usuario']) || (!in_array($_SESSION['usuario']['rol'], ['cliente', 'ceo']))) {
        //     header('Location: ' . url('index.php?pagina=login'));
        //     exit;
        // }
        require __DIR__ . '/src/views/cliente/novedadDetalle.php';
        break;

    case 'ceo':
        requireRol('ceo');
        $seccion = $_GET['seccion'] ?? 'home';

        if ($seccion === 'promociones') {
            require __DIR__ . '/src/views/ceo/promocionesListado.php';
        } elseif ($seccion === 'promocion-detalle') {
            require __DIR__ . '/src/views/ceo/promocionDetalle.php';
        } elseif ($seccion === 'reportes') {
            require __DIR__ . '/src/views/ceo/reportes.php';
        } else {
            require __DIR__ . '/src/views/ceo/home.php';
        }
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

    case 'perfil':
        require __DIR__ . '/src/views/usuarioPerfil.php';
        break;

    case 'buscar-vuelos':
        require __DIR__ . '/src/views/cliente/resultados.php';
        break;

    case 'reservar':
        requireRol('cliente');
        require __DIR__ . '/src/views/cliente/reservar.php';
        break;

    case 'mis-reservas':
        requireRol('cliente');
        require __DIR__ . '/src/views/cliente/misReservas.php';
        break;

    case 'historial-compras':
        requireRol('cliente');
        require __DIR__ . '/src/views/cliente/historialCompras.php';
        break;
    
    case 'pago':
        requireRol('cliente');
        require __DIR__ . '/src/views/cliente/pago.php';
        break;
    
    case 'aeropuertos-json':
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: public, max-age=86400');
        $aeropuertos = require __DIR__ . '/src/data/aeropuertos.php';
        echo json_encode($aeropuertos, JSON_UNESCAPED_UNICODE);
        exit;

    case 'ayuda':
        require __DIR__ . '/src/views/ayuda.php';
        break;

    case 'terminos':
        require __DIR__ . '/src/views/terminos.php';
        break;

    case 'privacidad':
        require __DIR__ . '/src/views/privacidad.php';
        break;

    case 'contacto':
        require __DIR__ . '/src/views/contacto.php';
        break;
        
    case 'enviar-contacto':
        // Controlador para procesar el formulario de contacto
        // $ctrl->procesarFormularioContacto(); 
        break;

    default:
        http_response_code(404);
        echo 'Página no encontrada';
        break;
}