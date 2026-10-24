<?php

require_once __DIR__ . '/../lib/PHPMailer/Exception.php';
require_once __DIR__ . '/../lib/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../lib/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function crearMailer(): PHPMailer {
    $envPath = __DIR__ . '/../../.env';
    $env = is_readable($envPath) ? parse_ini_file($envPath) : false;

    if (!is_array($env)) {
        throw new Exception('No se pudo leer el archivo .env para configurar el correo.');
    }

    // Las variables de entorno reales tienen prioridad sobre el .env.
    // Así se pueden hacer pruebas locales sin tocar la configuración del proyecto.
    $claves = [
        'MAIL_HOST', 'MAIL_USER', 'MAIL_PASS', 'MAIL_PORT',
        'MAIL_FROM', 'MAIL_FROM_NAME', 'MAIL_ENCRYPTION', 'MAIL_TIMEOUT',
    ];

    foreach ($claves as $clave) {
        $valor = getenv($clave);
        if ($valor !== false && $valor !== '') {
            $env[$clave] = $valor;
        }
    }

    foreach (['MAIL_HOST', 'MAIL_USER', 'MAIL_PASS', 'MAIL_PORT', 'MAIL_FROM'] as $clave) {
        if (!isset($env[$clave]) || trim((string)$env[$clave]) === '') {
            throw new Exception("Falta la configuración {$clave} en el archivo .env.");
        }
    }

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = $env['MAIL_HOST'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $env['MAIL_USER'];
    $mail->Password   = $env['MAIL_PASS'];
    $mail->Port       = (int)$env['MAIL_PORT'];
    $mail->Timeout    = isset($env['MAIL_TIMEOUT']) ? max(1, (int)$env['MAIL_TIMEOUT']) : 10;
    $mail->CharSet    = 'UTF-8';

    $encriptacion = strtolower(trim((string)($env['MAIL_ENCRYPTION'] ?? 'starttls')));
    $mail->SMTPSecure = match ($encriptacion) {
        'ssl', 'smtps'       => PHPMailer::ENCRYPTION_SMTPS,
        'none', 'false', '0' => '',
        default              => PHPMailer::ENCRYPTION_STARTTLS,
    };

    $mail->setFrom($env['MAIL_FROM'], $env['MAIL_FROM_NAME'] ?? 'Volaré');

    return $mail;
}
