<?php

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../controllers/UsuarioController.php';

$ctrl = new UsuarioController($link);

$accion = $_GET['accion'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    switch ($accion) {
        case 'registrar':
            $ctrl->crearUsuario();
            break;

        case 'registrar-ceo':
            $ctrl->crearCEO();
            break;

        case 'login':
            $ctrl->login();
            break;

        case 'logout':
            $ctrl->logout();
            break;

        case 'olvide-contrasena':
            $ctrl->olvidéContrasena();
            break;

        case 'reset-password':
            $ctrl->resetPassword();
            break;

        case 'editarPerfilPropio':
            $ctrl->actualizarPerfilPropio();
            break;

        case 'editar':
            requireRol('admin');
            $tokenPost = $_POST['csrf_token'] ?? '';
            $tokenSession = $_SESSION['csrf_token'] ?? '';
            
            if (empty($tokenPost) || empty($tokenSession) || !hash_equals($tokenSession, $tokenPost)) {
                flash_set('error', 'Token de seguridad inválido o sesión expirada.');
                redirect('index.php?pagina=usuarios&seccion=listado');
            }

            $id = (int)($_GET['id'] ?? 0);
            if (!$id) {
                flash_set('error', 'ID de usuario inválido.');
                redirect('index.php?pagina=usuarios&seccion=listado');
            }
            $ctrl->editarUsuario($id);
            break;

        case 'toggleEstado':
            requireRol('admin');
            
            $tokenPost = $_POST['csrf_token'] ?? '';
            $tokenSession = $_SESSION['csrf_token'] ?? '';
            if (empty($tokenPost) || empty($tokenSession) || !hash_equals($tokenSession, $tokenPost)) {
                flash_set('error', 'Token de seguridad inválido o sesión expirada.');
                redirect('index.php?pagina=usuarios&seccion=listado');
            }

            $id = (int)($_POST['id'] ?? 0);
            if (!$id) {
                flash_set('error', 'ID de usuario inválido.');
                redirect('index.php?pagina=usuarios&seccion=listado');
            }
            $ctrl->toggleEstadoUsuario($id);
            break;

        case 'aprobar-ceo':
        case 'denegar-ceo':
            requireRol('admin');

            $tokenPost = $_POST['csrf_token'] ?? '';
            $tokenSession = $_SESSION['csrf_token'] ?? '';
            if (empty($tokenPost) || empty($tokenSession) || !hash_equals($tokenSession, $tokenPost)) {
                flash_set('error', 'Token de seguridad inválido o sesión expirada.');
                redirect('index.php?pagina=ceos&seccion=listado');
            }

            $id = (int)($_POST['id'] ?? 0);
            if (!$id) {
                flash_set('error', 'ID de CEO inválido.');
                redirect('index.php?pagina=ceos&seccion=listado');
            }

            $ctrl->resolverEstadoCEO($accion === 'aprobar-ceo' ? 'aprobado' : 'denegado');
            break;

        default:
            http_response_code(404);
            echo "Acción no encontrada";
            break;
    }

} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    switch ($accion) {
        case 'logout':
            $ctrl->logout();
            break;

        case 'listar':
            requireRol('admin');

            $porPaginaPermitidos = [5, 10, 25, 50];
            $pagina = max(1, (int)($_GET['pagina'] ?? 1));
            $porPagina = in_array((int)($_GET['porPagina'] ?? 10), $porPaginaPermitidos) 
                            ? (int)$_GET['porPagina'] 
                            : 10;

            $estado = $_GET['estado'] ?? '';
            $busqueda = trim($_GET['busqueda'] ?? '');
            $rol = $_GET['rol'] ?? '';

            $resultado = $ctrl->listarUsuariosPaginado($pagina, $porPagina, $estado, $busqueda, $rol);

            header('Content-Type: application/json');
            echo json_encode($resultado);
            exit;

        default:
            http_response_code(405);
            echo "Acción GET no encontrada";
            break;
    }

} else {
    http_response_code(405);
    echo "Método no permitido";
}