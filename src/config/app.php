<?php

date_default_timezone_set('America/Argentina/Buenos_Aires');

function fechaValidaFormato(string $fecha): bool {
    $date = DateTime::createFromFormat('!Y-m-d', $fecha);
    return $date !== false && $date->format('Y-m-d') === $fecha;
}

function horaValidaFormato(string $hora): bool {
    $time = DateTime::createFromFormat('!H:i', $hora);
    return $time !== false && $time->format('H:i') === $hora;
}

function telefonoValidoFormato(string $telefono): bool {
    $digitos = preg_replace('/\D/', '', $telefono);
    return preg_match('/^[0-9+() -]+$/', $telefono) === 1
        && $digitos !== null
        && strlen($digitos) >= 7
        && strlen($digitos) <= 15;
}

function textoValido(string $texto, int $maximo, bool $multilinea = false): bool {
    $longitud = function_exists('mb_strlen') ? mb_strlen($texto, 'UTF-8') : strlen($texto);
    if ($texto === '' || $longitud > $maximo) {
        return false;
    }

    $control = $multilinea ? '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/' : '/[\x00-\x1F\x7F]/';
    return preg_match($control, $texto) !== 1;
}
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
ini_set('log_errors', '1');

/*
 * Estado de aprobación de CEOs sin cambios de esquema:
 * - Pendiente: activo = 0 y fecha_hora_autorizacion IS NULL
 * - Aprobado:  fecha_hora_autorizacion con fecha real, o CEO activo legacy
 * - Denegado:  fecha_hora_autorizacion = FECHA_DENEGACION_CEO
 */
if (!defined('FECHA_DENEGACION_CEO')) {
    define('FECHA_DENEGACION_CEO', '1970-01-01 00:00:00');
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

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

function urlAbsoluta(string $path = ''): string {
    // Detecta protocolo
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';

    // Detecta host (con puerto si aplica)
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    // Usa el APP_BASE que ya calculaste (con DOCUMENT_ROOT + __DIR__)
    // para saber si estás en raíz o en /aerolinea
    $path = ltrim($path, '/');

    return $scheme . '://' . $host . APP_BASE . '/' . $path;
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

function solicitudEsJson(): bool {
    $accept = strtolower((string)($_SERVER['HTTP_ACCEPT'] ?? ''));
    $ruta = strtolower((string)($_SERVER['REQUEST_URI'] ?? ''));
    foreach (headers_list() as $header) {
        if (stripos($header, 'content-type: application/json') === 0) {
            return true;
        }
    }
    return str_contains($accept, 'application/json') || str_contains($ruta, '-json');
}

function registrarErrorAplicacion(string $tipo, string $detalle): void {
    error_log(sprintf('[%s] %s', $tipo, $detalle));
}

function responderErrorAplicacion(int $status = 500, string $tipo = 'Error inesperado'): void {
    registrarErrorAplicacion($tipo, 'Se mostró una respuesta genérica al usuario.');
    http_response_code($status);

    if (solicitudEsJson()) {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode([
            'success' => false,
            'error' => 'Ocurrió un error inesperado. Intentá nuevamente más tarde.'
        ], JSON_UNESCAPED_UNICODE);
        return;
    }

    if (!headers_sent()) {
        header('Content-Type: text/html; charset=utf-8');
    }
    require __DIR__ . '/../views/error500.php';
}

set_exception_handler(function (Throwable $exception): void {
    registrarErrorAplicacion(
        'Excepción no controlada',
        $exception->getMessage() . ' en ' . $exception->getFile() . ':' . $exception->getLine()
    );

    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    responderErrorAplicacion();
});

register_shutdown_function(function (): void {
    $error = error_get_last();
    if (!$error || !in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        return;
    }

    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    registrarErrorAplicacion(
        'Error fatal',
        $error['message'] . ' en ' . $error['file'] . ':' . $error['line']
    );
    responderErrorAplicacion();
});

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/policies.php';
