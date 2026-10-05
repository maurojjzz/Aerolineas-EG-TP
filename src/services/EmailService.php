<?php

require_once __DIR__ . '/../config/mailer.php';

use PHPMailer\PHPMailer\Exception;

class EmailService {

    // ── Verificación de cuenta ────────────────────────────────────────────────
    public static function enviarVerificacion(string $email, string $nombre, string $token): bool {
        $enlace = urlAbsoluta('index.php?pagina=verificar&token=' . $token);

        $cuerpo = "
            <h2>Hola, {$nombre}!</h2>
            <p>Gracias por registrarte en <strong>Vuela Sin Límites</strong>.</p>
            <p>Para activar tu cuenta hacé click en el botón de abajo:</p>
            <p style='text-align:center;margin:30px 0;'>
                <a href='{$enlace}'
                   style='background:#2066ba;color:white;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:bold;'>
                    Verificar mi cuenta
                </a>
            </p>
            <p style='color:#888;font-size:0.85rem;'>Si no creaste esta cuenta, ignorá este correo.</p>
            <p style='color:#888;font-size:0.85rem;'>El enlace expira en 24 horas.</p>
        ";

        return self::enviar($email, 'Verificá tu cuenta - Vuela Sin Límites', $cuerpo);
    }

    // ── Notificación CEO: pendiente de aprobación ─────────────────────────────
    public static function enviarNotificacionCEOPendiente(string $email, string $nombre): bool {
        $cuerpo = "
            <h2>Hola, {$nombre}!</h2>
            <p>Recibimos tu solicitud de registro como CEO en <strong>Vuela Sin Límites</strong>.</p>
            <p>Tu cuenta está siendo revisada por nuestro equipo. Te notificaremos cuando sea aprobada.</p>
        ";

        return self::enviar($email, 'Solicitud de registro recibida - Vuela Sin Límites', $cuerpo);
    }

    // ── Notificación CEO: cuenta aprobada ─────────────────────────────────────
    public static function enviarAprobacionCEO(string $email, string $nombre): bool {
        $enlace = urlAbsoluta('index.php?pagina=login');

        $cuerpo = "
            <h2>¡Buenas noticias, {$nombre}!</h2>
            <p>Tu cuenta como CEO en <strong>Vuela Sin Límites</strong> fue aprobada.</p>
            <p style='text-align:center;margin:30px 0;'>
                <a href='{$enlace}'
                    style='background:#2066ba;color:white;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:bold;'>
                    Iniciar sesión
                </a>
            </p>
        ";

        return self::enviar($email, '¡Tu cuenta fue aprobada! - Vuela Sin Límites', $cuerpo);
    }

    // ── Recuperación de contraseña ────────────────────────────────────────────
    public static function enviarRecuperacionContrasena(string $email, string $nombre, string $token): bool {
        $enlace = urlAbsoluta('index.php?pagina=reset-password&token=' . $token);

        $cuerpo = "
            <h2>Hola, {$nombre}!</h2>
            <p>Recibimos una solicitud para restablecer la contraseña de tu cuenta en <strong>Vuela Sin Límites</strong>.</p>
            <p style='text-align:center;margin:30px 0;'>
                <a href='{$enlace}'
                    style='background:#2066ba;color:white;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:bold;'>
                    Restablecer contraseña
                </a>
            </p>
            <p style='color:#888;font-size:0.85rem;'>Si no solicitaste esto, ignorá este correo. El enlace expira en 1 hora.</p>
        ";

        return self::enviar($email, 'Restablecer contraseña - Vuela Sin Límites', $cuerpo);
    }

    // ── Método base de envío ──────────────────────────────────────────────────
    private static function enviar(string $destinatario, string $asunto, string $cuerpo): bool {
        try {
            $mail = crearMailer();
            $mail->addAddress($destinatario);
            $mail->Subject = $asunto;
            $mail->isHTML(true);
            $mail->Body    = $cuerpo;
            $mail->AltBody = strip_tags($cuerpo);
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('Error enviando email a ' . $destinatario . ': ' . $e->getMessage());
            return false;
        }
    }
}