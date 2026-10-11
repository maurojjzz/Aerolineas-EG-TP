<?php
session_start();
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../controllers/NovedadController.php';

// Verificar permisos de administrador
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    header('Location: ' . url('index.php?pagina=login'));
    exit;
}

$ctrl = new NovedadController($link);
$accion = $_GET['accion'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar Token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        flash_set('error', 'Error de seguridad: Token CSRF inválido.');
        header('Location: ' . url('index.php?pagina=novedades&seccion=listado'));
        exit;
    }

    $datos = [
        'titulo'    => trim($_POST['titulo'] ?? ''),
        'subtitulo' => trim($_POST['subtitulo'] ?? ''),
        'contenido' => trim($_POST['contenido'] ?? ''),
        'categoria' => $_POST['categoria'] ?? 'general',
        'prioridad' => $_POST['prioridad'] ?? 'media',
        'estado'    => $_POST['estado'] ?? 'publicada',
        'imagen'    => $_POST['imagen'] ?? null
    ];

    if ($accion === 'crear') {
        $exito = $ctrl->guardar($datos);
        if ($exito) {
            flash_set('success', 'Novedad publicada correctamente.');
        } else {
            flash_set('error', 'No se pudo crear la novedad.');
        }
    } elseif ($accion === 'editar') {
        $id = (int)($_POST['idNovedad'] ?? 0);
        $exito = $ctrl->guardar($datos, $id);
        if ($exito) {
            flash_set('success', 'Novedad actualizada correctamente.');
        } else {
            flash_set('error', 'No se pudo actualizar la novedad.');
        }
    } elseif ($accion === 'eliminar') {
        $id = (int)($_POST['idNovedad'] ?? 0);
        $exito = $ctrl->eliminar($id);
        if ($exito) {
            flash_set('success', 'Novedad eliminada correctamente.');
        } else {
            flash_set('error', 'No se pudo eliminar la novedad.');
        }
    }

    header('Location: ' . url('index.php?pagina=novedades&seccion=listado'));
    exit;
}