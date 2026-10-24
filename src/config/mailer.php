<?php

require_once __DIR__ . '/../lib/PHPMailer/Exception.php';
require_once __DIR__ . '/../lib/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../lib/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Lee la configuración de correo desde el .env y deja que las variables de
 * entorno reales tengan prioridad (útil para pruebas locales sin tocar el .env).
 */
function configuracionCorreo(): array {
    $envPath = __DIR__ . '/../../.env';
    $env = is_readable($envPath) ? parse_ini_file($envPath) : false;

    if (!is_array($env)) {
        throw new Exception('No se pudo leer el archivo .env para configurar el correo.');
    }

    $claves = [
        'MAIL_HOST', 'MAIL_USER', 'MAIL_PASS', 'MAIL_PORT',
        'MAIL_FROM', 'MAIL_FROM_NAME', 'MAIL_ENCRYPTION', 'MAIL_TIMEOUT',
        'MAIL_REPLY_TO', 'MAIL_CONTACT_TO', 'MAIL_HOSTNAME',
        'MAIL_DKIM_DOMAIN', 'MAIL_DKIM_SELECTOR', 'MAIL_DKIM_PRIVATE_KEY',
        'MAIL_DKIM_PASSPHRASE',
    ];

    foreach ($claves as $clave) {
        $valor = getenv($clave);
        if ($valor !== false && $valor !== '') {
            $env[$clave] = $valor;
        }
    }

    return $env;
}

function crearMailer(): PHPMailer {
    $env = configuracionCorreo();

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

    $remitente = trim((string)$env['MAIL_FROM']);
    $nombreRemitente = trim((string)($env['MAIL_FROM_NAME'] ?? '')) ?: 'Volaré';

    $mail->setFrom($remitente, $nombreRemitente);

    // El envelope sender explícito mejora la entregabilidad y evita que el
    // servidor SMTP use una dirección inventada para los rebotes.
    $mail->Sender = $remitente;

    // Reply-To: configurable; si no se define, responde al remitente.
    $responderA = trim((string)($env['MAIL_REPLY_TO'] ?? ''));
    if ($responderA === '') {
        $responderA = $remitente;
    }
    if (filter_var($responderA, FILTER_VALIDATE_EMAIL)) {
        $mail->addReplyTo($responderA, $nombreRemitente);
    }

    // Nombre usado en el saludo HELO/EHLO. Si no se configura, PHPMailer usa
    // el hostname del servidor, que suele ser el valor correcto.
    $hostname = trim((string)($env['MAIL_HOSTNAME'] ?? ''));
    if ($hostname !== '') {
        $mail->Hostname = $hostname;
    }

    // DKIM es opcional: si no hay claves configuradas, el envío sigue igual.
    $dkimDominio   = trim((string)($env['MAIL_DKIM_DOMAIN'] ?? ''));
    $dkimSelector  = trim((string)($env['MAIL_DKIM_SELECTOR'] ?? ''));
    $dkimClave     = trim((string)($env['MAIL_DKIM_PRIVATE_KEY'] ?? ''));

    if ($dkimDominio !== '' && $dkimSelector !== '' && $dkimClave !== '') {
        if (is_readable($dkimClave)) {
            $mail->DKIM_domain   = $dkimDominio;
            $mail->DKIM_selector = $dkimSelector;
            $mail->DKIM_private  = $dkimClave;
            $mail->DKIM_identity = $remitente;

            $passphrase = (string)($env['MAIL_DKIM_PASSPHRASE'] ?? '');
            if ($passphrase !== '') {
                $mail->DKIM_passphrase = $passphrase;
            }
        } else {
            error_log('DKIM configurado pero la clave privada no es legible: ' . $dkimClave);
        }
    }

    return $mail;
}

/**
 * Buzón que recibe los mensajes del formulario de contacto.
 * Usa MAIL_CONTACT_TO y, si no está definido, cae en MAIL_FROM.
 */
function destinoContactoCorreo(): string {
    try {
        $env = configuracionCorreo();
    } catch (Throwable $e) {
        return '';
    }

    $destino = trim((string)($env['MAIL_CONTACT_TO'] ?? ''));
    if ($destino === '') {
        $destino = trim((string)($env['MAIL_FROM'] ?? ''));
    }

    // Puede contener varias direcciones separadas por coma.
    $validas = [];
    foreach (explode(',', $destino) as $direccion) {
        $direccion = trim($direccion);
        if ($direccion !== '' && filter_var($direccion, FILTER_VALIDATE_EMAIL)) {
            $validas[] = $direccion;
        }
    }

    return implode(',', $validas);
}
