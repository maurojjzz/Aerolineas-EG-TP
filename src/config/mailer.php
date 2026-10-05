<?php

require_once __DIR__ . '/../lib/PHPMailer/Exception.php';
require_once __DIR__ . '/../lib/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../lib/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function crearMailer(): PHPMailer {
    $env = parse_ini_file(__DIR__ . '/../../.env');

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = $env['MAIL_HOST'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $env['MAIL_USER'];
    $mail->Password   = $env['MAIL_PASS'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = $env['MAIL_PORT'];
    $mail->CharSet    = 'UTF-8';
    $mail->setFrom($env['MAIL_FROM'], $env['MAIL_FROM_NAME'] ?? 'Vuela Sin Límites');

    return $mail;
}