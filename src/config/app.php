<?php

date_default_timezone_set('America/Argentina/Buenos_Aires');

// Compara la carpeta del proyecto contra la raíz del server (htdocs)
// y saca el prefijo. Da igual qué archivo PHP se ejecute.
$docRoot  = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? ''));
$proyRoot = str_replace('\\', '/', realpath(__DIR__ . '/../..'));

$prefijo = '';
if ($docRoot && $proyRoot && str_starts_with($proyRoot, $docRoot)) {
    $prefijo = substr($proyRoot, strlen($docRoot));
    $prefijo = rtrim($prefijo, '/');
}

define('APP_BASE', $prefijo);

function url(string $path = ''): string {
    $path = ltrim($path, '/');
    if ($path === '') {
        return APP_BASE === '' ? '/' : APP_BASE . '/';
    }
    return APP_BASE . '/' . $path;
}

function redirect(string $path): void {
    header('Location: ' . url($path));
    exit;
}


/**
 * Guarda un valor en sesión para leerlo UNA SOLA VEZ en la próxima request.
 * Se usa para mensajes de error y para repoblar formularios.
 */
function flash_set(string $key, $value): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['flash'][$key] = $value;
}

/**
 * Lee (y borra) un valor guardado con flash_set().
 * Si no existe, devuelve $default.
 */
function flash_get(string $key, $default = null) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $value = $_SESSION['flash'][$key] ?? $default;
    unset($_SESSION['flash'][$key]);
    return $value;
}
