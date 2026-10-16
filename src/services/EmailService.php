<?php

require_once __DIR__ . '/../config/mailer.php';

use PHPMailer\PHPMailer\Exception;

class EmailService {

    // ── Verificación de cuenta ────────────────────────────────────────────────
    public static function enviarVerificacion(string $email, string $nombre, string $token): bool {
        $enlace = urlAbsoluta('index.php?pagina=verificar&token=' . $token);

        $cuerpo = "
            <h2>Hola, {$nombre}!</h2>
            <p>Gracias por registrarte en <strong>Volaré</strong>.</p>
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

        return self::enviar($email, 'Verificá tu cuenta - Volaré', $cuerpo);
    }

    // ── Notificación CEO: pendiente de aprobación ─────────────────────────────
    public static function enviarNotificacionCEOPendiente(string $email, string $nombre): bool {
        $cuerpo = "
            <h2>Hola, {$nombre}!</h2>
            <p>Recibimos tu solicitud de registro como CEO en <strong>Volaré</strong>.</p>
            <p>Tu cuenta está siendo revisada por nuestro equipo. Te notificaremos cuando sea aprobada.</p>
        ";

        return self::enviar($email, 'Solicitud de registro recibida - Volaré', $cuerpo);
    }

    // ── Notificación CEO: cuenta aprobada ─────────────────────────────────────
    public static function enviarAprobacionCEO(string $email, string $nombre): bool {
        $enlace = urlAbsoluta('index.php?pagina=login');

        $cuerpo = "
            <h2>¡Buenas noticias, {$nombre}!</h2>
            <p>Tu cuenta como CEO en <strong>Volaré</strong> fue aprobada.</p>
            <p style='text-align:center;margin:30px 0;'>
                <a href='{$enlace}'
                    style='background:#2066ba;color:white;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:bold;'>
                    Iniciar sesión
                </a>
            </p>
        ";

        return self::enviar($email, '¡Tu cuenta fue aprobada! - Volaré', $cuerpo);
    }

    // ── Recuperación de contraseña ────────────────────────────────────────────
    public static function enviarRecuperacionContrasena(string $email, string $nombre, string $token): bool {
        $enlace = urlAbsoluta('index.php?pagina=reset-password&token=' . $token);

        $cuerpo = "
            <h2>Hola, {$nombre}!</h2>
            <p>Recibimos una solicitud para restablecer la contraseña de tu cuenta en <strong>Volaré</strong>.</p>
            <p style='text-align:center;margin:30px 0;'>
                <a href='{$enlace}'
                    style='background:#2066ba;color:white;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:bold;'>
                    Restablecer contraseña
                </a>
            </p>
            <p style='color:#888;font-size:0.85rem;'>Si no solicitaste esto, ignorá este correo. El enlace expira en 1 hora.</p>
        ";

        return self::enviar($email, 'Restablecer contraseña - Volaré', $cuerpo);
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

    // ── Consulta de Contacto ──────────────────────────────────────────────────
    public static function enviarConsultaContacto(string $nombre, string $email, string $asunto, string $mensaje): bool {
        // Mapear el asunto a un texto más legible para el mail
        $motivos = [
            'reservas' => 'Consultas sobre Vuelos y Reservas',
            'equipaje' => 'Equipaje y Requisitos de Viaje',
            'facturacion' => 'Facturación y Comprobantes',
            'reclamos' => 'Reclamos o Sugerencias',
            'otro' => 'Otro motivo'
        ];
        $motivoLegible = $motivos[$asunto] ?? 'Consulta general';

        $cuerpo = "
            <h2>Nueva consulta desde el Formulario de Contacto</h2>
            <p><strong>Nombre:</strong> {$nombre}</p>
            <p><strong>Correo Electrónico:</strong> {$email}</p>
            <p><strong>Motivo / Asunto:</strong> {$motivoLegible}</p>
            <hr style='border:0;border-top:1px solid #ddd;margin:20px 0;'>
            <p><strong>Mensaje:</strong></p>
            <p style='background:#f9f9f9;padding:15px;border-radius:6px;color:#333;'>" . nl2br(htmlspecialchars($mensaje)) . "</p>
        ";

        // Enviamos el correo al buzón de soporte o al administrador configurado en el sistema
        $destinatarioSoporte = 'rodrigojmayer@gmail.com'; // O podés usar alguna constante de config si la tenés

        return self::enviar($destinatarioSoporte, 'Nueva consulta de contacto: ' . $motivoLegible, $cuerpo);
    }
}