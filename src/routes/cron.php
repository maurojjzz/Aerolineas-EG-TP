<?php

/**
 * Endpoint para tareas programadas.
 *
 * Uso:
 *   GET /src/routes/cron.php?accion=limpiar-vencidas&token=TU_TOKEN
 *   (también se acepta el token en el header X-Cron-Token)
 *
 * El token se configura con CRON_TOKEN en el .env o como variable de entorno.
 * Si no está configurado, el endpoint queda deshabilitado.
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../controllers/ReservaController.php';

header('Content-Type: application/json; charset=utf-8');

function cronResponder(int $codigo, array $datos): void {
    http_response_code($codigo);
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

function cronTokenEsperado(): string {
    $token = getenv('CRON_TOKEN');
    if (is_string($token) && trim($token) !== '') {
        return trim($token);
    }

    $envPath = __DIR__ . '/../../.env';
    if (!is_readable($envPath)) {
        return '';
    }

    $config = parse_ini_file($envPath);
    if (!is_array($config)) {
        return '';
    }

    return trim((string)($config['CRON_TOKEN'] ?? ''));
}

$tokenEsperado = cronTokenEsperado();
if ($tokenEsperado === '') {
    cronResponder(503, [
        'ok'      => false,
        'mensaje' => 'CRON_TOKEN no está configurado.',
    ]);
}

$tokenRecibido = (string)($_GET['token'] ?? ($_SERVER['HTTP_X_CRON_TOKEN'] ?? ''));
if ($tokenRecibido === '' || !hash_equals($tokenEsperado, $tokenRecibido)) {
    cronResponder(403, [
        'ok'      => false,
        'mensaje' => 'Token inválido.',
    ]);
}

$accion = $_GET['accion'] ?? '';

switch ($accion) {
    case 'limpiar-vencidas':
        $ctrl = new ReservaController($link);
        $cantidad = $ctrl->limpiarReservasVencidas();

        cronResponder(200, [
            'ok'        => true,
            'procesadas' => $cantidad,
            'mensaje'   => 'Reservas vencidas procesadas.',
        ]);
        break;

    default:
        cronResponder(404, [
            'ok'      => false,
            'mensaje' => 'Acción no encontrada.',
        ]);
}
