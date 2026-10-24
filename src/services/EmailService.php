<?php

require_once __DIR__ . '/../config/mailer.php';

class EmailService {

    // ── Verificación de cuenta ────────────────────────────────────────────────
    public static function enviarVerificacion(string $email, string $nombre, string $token): bool {
        $nombreSeguro = self::e($nombre);
        $enlace = urlAbsoluta('index.php?pagina=verificar&token=' . $token);

        $cuerpo = "
            <h2>Hola, {$nombreSeguro}!</h2>
            <p>Gracias por registrarte en <strong>Volaré</strong>.</p>
            <p>Para activar tu cuenta hacé click en el botón de abajo:</p>
            <p style='text-align:center;margin:30px 0;'>
                <a href='{$enlace}'
                   style='background:#2066ba;color:white;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:bold;'>
                    Verificar mi cuenta
                </a>
            </p>
            <p style='color:#888;font-size:0.85rem;'>Si no creaste esta cuenta, ignorá este correo.</p>
            <p style='color:#888;font-size:0.85rem;'>El enlace expira en 24 horas.
        ";

        return self::enviar($email, 'Verificá tu cuenta - Volaré', $cuerpo);
    }

    // ── Notificación CEO: pendiente de aprobación ─────────────────────────────
    public static function enviarNotificacionCEOPendiente(string $email, string $nombre): bool {
        $nombreSeguro = self::e($nombre);
        $cuerpo = "
            <h2>Hola, {$nombreSeguro}!</h2>
            <p>Recibimos tu solicitud de registro como CEO en <strong>Volaré</strong>.</p>
            <p>Tu cuenta está siendo revisada por nuestro equipo. Te notificaremos cuando sea aprobada.</p>
        ";

        return self::enviar($email, 'Solicitud de registro recibida - Volaré', $cuerpo);
    }

    // ── Notificación CEO: cuenta aprobada ─────────────────────────────────────
    public static function enviarAprobacionCEO(string $email, string $nombre): bool {
        $nombreSeguro = self::e($nombre);
        $enlace = urlAbsoluta('index.php?pagina=login');

        $cuerpo = "
            <h2>¡Buenas noticias, {$nombreSeguro}!</h2>
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

    // ── Notificación CEO: cuenta denegada ─────────────────────────────────────
    public static function enviarDenegacionCEO(string $email, string $nombre): bool {
        $nombreSeguro = self::e($nombre);
        $cuerpo = "
            <h2>Hola, {$nombreSeguro}!</h2>
            <p>Después de revisar tu solicitud para registrarte como CEO en <strong>Volaré</strong>, lamentablemente no pudimos aprobarla.</p>
            <p>Si creés que se trata de un error o querés ampliar la información, podés contactar a nuestro equipo desde la sección de contacto.</p>
        ";

        return self::enviar($email, 'Solicitud de registro como CEO denegada - Volaré', $cuerpo);
    }

    // ── Recuperación de contraseña ────────────────────────────────────────────
    public static function enviarRecuperacionContrasena(string $email, string $nombre, string $token): bool {
        $nombreSeguro = self::e($nombre);
        $enlace = urlAbsoluta('index.php?pagina=reset-password&token=' . $token);

        $cuerpo = "
            <h2>Hola, {$nombreSeguro}!</h2>
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

    // ── Reserva creada (pendiente de pago) ────────────────────────────────────
    public static function enviarReservaCreada(string $email, string $nombre, array $reserva): bool {
        $codigo = self::codigoReserva($reserva['idReserva'] ?? 0);
        $enlace = urlAbsoluta('index.php?pagina=pago&idReserva=' . (int)($reserva['idReserva'] ?? 0));

        $contenido = self::parrafo('Hola, <strong>' . self::e($nombre) . '</strong>:')
            . self::parrafo('Recibimos tu reserva en <strong>Volaré</strong>. Para asegurar tu lugar tenés que completar el pago antes de la fecha límite.')
            . self::tablaReserva($reserva, true)
            . self::aviso(
                'Si no registramos el pago antes de <strong>' . self::e(self::fecha($reserva['fechaVencimiento'] ?? null)) . '</strong>, la reserva se cancela automáticamente y los asientos vuelven a estar disponibles.',
                'warning'
            )
            . self::boton('Pagar ahora', $enlace)
            . self::parrafo(
                "<span style='color:#6b7a90;font-size:13px;'>También podés verla desde "
                . "<a href='" . self::e(urlAbsoluta('index.php?pagina=mis-reservas')) . "' style='color:#2066ba;'>Mis reservas</a>.</span>"
            );

        return self::enviar(
            $email,
            'Reserva ' . $codigo . ' creada - Volaré',
            self::plantilla('Reserva creada', $contenido)
        );
    }

    // ── Pago confirmado ───────────────────────────────────────────────────────
    public static function enviarPagoConfirmado(string $email, string $nombre, array $reserva): bool {
        $codigo = self::codigoReserva($reserva['idReserva'] ?? 0);

        $contenido = self::parrafo('Hola, <strong>' . self::e($nombre) . '</strong>:')
            . self::parrafo('¡Buenas noticias! Confirmamos el pago de tu reserva y tu lugar ya está asegurado.')
            . self::tablaReserva($reserva)
            . self::aviso('Guardá este correo como comprobante. Podés consultar el detalle de tu reserva cuando quieras.', 'success')
            . self::boton('Ver mis reservas', urlAbsoluta('index.php?pagina=mis-reservas'));

        return self::enviar(
            $email,
            '¡Pago confirmado! Reserva ' . $codigo . ' - Volaré',
            self::plantilla('Pago confirmado', $contenido)
        );
    }

    // ── Reserva cancelada por el cliente ──────────────────────────────────────
    public static function enviarReservaCancelada(string $email, string $nombre, array $reserva): bool {
        $codigo = self::codigoReserva($reserva['idReserva'] ?? 0);

        $contenido = self::parrafo('Hola, <strong>' . self::e($nombre) . '</strong>:')
            . self::parrafo('Cancelaste tu reserva en <strong>Volaré</strong>. A continuación te dejamos el detalle.')
            . self::tablaReserva($reserva)
            . self::aviso('Los asientos fueron liberados. Si ya habías abonado, contactá a la aerolínea para consultar las condiciones de reintegro.', 'info')
            . self::boton('Buscar nuevos vuelos', urlAbsoluta('index.php?pagina=buscar-vuelos'));

        return self::enviar(
            $email,
            'Reserva ' . $codigo . ' cancelada - Volaré',
            self::plantilla('Reserva cancelada', $contenido)
        );
    }

    // ── Reserva vencida por falta de pago ─────────────────────────────────────
    public static function enviarReservaVencida(string $email, string $nombre, array $reserva): bool {
        $codigo = self::codigoReserva($reserva['idReserva'] ?? 0);

        $contenido = self::parrafo('Hola, <strong>' . self::e($nombre) . '</strong>:')
            . self::parrafo('Pasaron 3 días desde que hiciste la reserva y no registramos el pago.')
            . self::tablaReserva($reserva)
            . self::aviso('La reserva fue cancelada automáticamente y los asientos volvieron a estar disponibles. Si todavía querés viajar, podés hacer una nueva reserva.', 'danger')
            . self::boton('Buscar vuelos', urlAbsoluta('index.php?pagina=buscar-vuelos'));

        return self::enviar(
            $email,
            'Tu reserva ' . $codigo . ' venció - Volaré',
            self::plantilla('Reserva vencida', $contenido)
        );
    }

    // ── Promoción aprobada / denegada (al CEO creador) ────────────────────────
    public static function enviarPromocionResuelta(string $email, string $nombre, array $promocion, string $estado): bool {
        $aprobada = $estado === 'Aprobada';

        $contenido = self::parrafo('Hola, <strong>' . self::e($nombre) . '</strong>:')
            . self::parrafo($aprobada
                ? 'Tu promoción fue <strong>aprobada</strong> y ya está disponible para los clientes.'
                : 'Tu promoción fue <strong>denegada</strong> por el administrador.')
            . self::tablaPromocion($promocion)
            . self::aviso(
                $aprobada
                    ? 'La promoción se aplicará automáticamente a los vuelos que correspondan durante su vigencia.'
                    : 'Si querés revisar los datos o volver a enviarla con cambios, podés hacerlo desde tu panel.',
                $aprobada ? 'success' : 'danger'
            )
            . self::boton('Ver mis promociones', urlAbsoluta('index.php?pagina=promociones'));

        return self::enviar(
            $email,
            $aprobada ? 'Tu promoción fue aprobada - Volaré' : 'Tu promoción fue denegada - Volaré',
            self::plantilla($aprobada ? 'Promoción aprobada' : 'Promoción denegada', $contenido)
        );
    }

    // ── Nueva promoción pendiente (a los administradores) ─────────────────────
    public static function enviarPromocionPendienteAdmin(
        string $email,
        string $nombre,
        array $promocion,
        string $aerolinea,
        string $ceo
    ): bool {
        $contenido = self::parrafo('Hola, <strong>' . self::e($nombre) . '</strong>:')
            . self::parrafo(
                'El CEO <strong>' . self::e($ceo) . '</strong> de <strong>' . self::e($aerolinea)
                . '</strong> envió una nueva promoción que está pendiente de aprobación.'
            )
            . self::tablaPromocion($promocion)
            . self::boton('Revisar promociones', urlAbsoluta('index.php?pagina=promociones&seccion=listado'));

        return self::enviar(
            $email,
            'Nueva promoción pendiente de aprobación - Volaré',
            self::plantilla('Promoción pendiente', $contenido)
        );
    }

    // ── Nueva venta confirmada (al CEO de la aerolínea) ───────────────────────
    public static function enviarVentaCEO(string $email, string $nombre, array $reserva, string $cliente): bool {
        $codigo = self::codigoReserva($reserva['idReserva'] ?? 0);

        $contenido = self::parrafo('Hola, <strong>' . self::e($nombre) . '</strong>:')
            . self::parrafo('Se confirmó el pago de una nueva reserva en tu aerolínea.')
            . self::tablaVenta($reserva, $cliente)
            . self::boton('Ver reportes', urlAbsoluta('index.php?pagina=ceo&seccion=reportes'));

        return self::enviar(
            $email,
            'Nueva venta: reserva ' . $codigo . ' - Volaré',
            self::plantilla('Nueva venta', $contenido)
        );
    }

    // ── Helpers de formato ────────────────────────────────────────────────────
    private static function e(?string $valor): string {
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
    }

    private static function fecha(?string $fecha, bool $conHora = true): string {
        if ($fecha === null || trim($fecha) === '') {
            return '—';
        }

        $timestamp = strtotime($fecha);
        if ($timestamp === false) {
            return '—';
        }

        return date($conHora ? 'd/m/Y H:i' : 'd/m/Y', $timestamp);
    }

    private static function monto($valor): string {
        return '$ ' . number_format((float)$valor, 2, ',', '.');
    }

    private static function codigoReserva($idReserva): string {
        return '#' . str_pad((string)(int)$idReserva, 6, '0', STR_PAD_LEFT);
    }

    private static function pasajeros(int $cantidad): string {
        return $cantidad . ' pasajero' . ($cantidad === 1 ? '' : 's');
    }

    private static function ruta(array $reserva): string {
        $origen  = trim((string)($reserva['origen'] ?? ''));
        $destino = trim((string)($reserva['destino'] ?? ''));

        if ($origen === '' || $destino === '') {
            return '—';
        }

        return self::e($origen) . ' → ' . self::e($destino);
    }

    // ── Helpers de maquetado ──────────────────────────────────────────────────
    private static function plantilla(string $titulo, string $contenido): string {
        $tituloSeguro = self::e($titulo);

        return "
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>{$tituloSeguro}</title>
        </head>
        <body style='margin:0;padding:0;background:#f4f7fb;font-family:Arial,Helvetica,sans-serif;color:#1f2a3d;'>
            <table role='presentation' width='100%' cellpadding='0' cellspacing='0' style='background:#f4f7fb;padding:24px 12px;'>
                <tr>
                    <td align='center'>
                        <table role='presentation' width='100%' cellpadding='0' cellspacing='0' style='max-width:600px;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e3eaf3;'>
                            <tr>
                                <td style='background:#0d3262;padding:20px 28px;'>
                                    <span style='color:#ffffff;font-size:20px;font-weight:bold;letter-spacing:1px;'>Volaré</span>
                                </td>
                            </tr>
                            <tr>
                                <td style='padding:28px;'>
                                    <h1 style='margin:0 0 16px;font-size:22px;line-height:1.3;color:#102a4c;'>{$tituloSeguro}</h1>
                                    {$contenido}
                                </td>
                            </tr>
                            <tr>
                                <td style='padding:18px 28px;background:#f8fafd;border-top:1px solid #e3eaf3;color:#6b7a90;font-size:12px;line-height:1.5;'>
                                    Este correo se envió automáticamente por una acción en Volaré.
                                    Si no reconocés esta operación, contactanos desde la sección de contacto.
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        ";
    }

    private static function parrafo(string $html): string {
        return "<p style='margin:0 0 12px;font-size:15px;line-height:1.6;color:#3d4b60;'>{$html}</p>";
    }

    private static function fila(string $etiqueta, string $valor): string {
        return "
            <tr>
                <td style='padding:7px 0;color:#6b7a90;font-size:14px;'>{$etiqueta}</td>
                <td style='padding:7px 0;color:#102a4c;font-size:14px;font-weight:bold;text-align:right;'>{$valor}</td>
            </tr>
        ";
    }

    private static function tabla(array $filas): string {
        return "
            <table role='presentation' width='100%' cellpadding='0' cellspacing='0' style='border-collapse:collapse;margin:18px 0;border-top:1px solid #e3eaf3;border-bottom:1px solid #e3eaf3;'>
                " . implode('', $filas) . "
            </table>
        ";
    }

    private static function tablaReserva(array $reserva, bool $incluirVencimiento = false): string {
        $filas = [
            self::fila('Reserva', self::e(self::codigoReserva($reserva['idReserva'] ?? 0))),
            self::fila('Ruta', self::ruta($reserva)),
            self::fila('Salida', self::e(self::fecha($reserva['fechaSalida'] ?? null))),
            self::fila('Llegada', self::e(self::fecha($reserva['fechaLlegada'] ?? null))),
            self::fila('Pasajeros', self::e(self::pasajeros((int)($reserva['cantPasajeros'] ?? 1)))),
        ];

        if (!empty($reserva['aerolinea'])) {
            $filas[] = self::fila('Aerolínea', self::e($reserva['aerolinea']));
        }

        $filas[] = self::fila('Total', self::e(self::monto($reserva['precioTotal'] ?? 0)));

        if ($incluirVencimiento && !empty($reserva['fechaVencimiento'])) {
            $filas[] = self::fila('Pagar antes de', self::e(self::fecha($reserva['fechaVencimiento'])));
        }

        return self::tabla($filas);
    }

    private static function tablaPromocion(array $promocion): string {
        $descuento = (float)($promocion['descuentoPorcentaje'] ?? 0);

        $filas = [
            self::fila('Código', self::e((string)($promocion['codigo'] ?? '—'))),
            self::fila('Nombre', self::e((string)($promocion['nombre'] ?? '—'))),
            self::fila('Descuento', self::e(number_format($descuento, 0, ',', '.') . '%')),
            self::fila(
                'Vigencia',
                self::e(
                    self::fecha($promocion['fechaInicio'] ?? null, false)
                    . ' al '
                    . self::fecha($promocion['fechaFin'] ?? null, false)
                )
            ),
        ];

        if (!empty($promocion['aerolinea'])) {
            $filas[] = self::fila('Aerolínea', self::e($promocion['aerolinea']));
        }

        return self::tabla($filas);
    }

    private static function tablaVenta(array $reserva, string $cliente): string {
        $filas = [
            self::fila('Reserva', self::e(self::codigoReserva($reserva['idReserva'] ?? 0))),
            self::fila('Ruta', self::ruta($reserva)),
            self::fila('Salida', self::e(self::fecha($reserva['fechaSalida'] ?? null))),
            self::fila('Pasajeros', self::e(self::pasajeros((int)($reserva['cantPasajeros'] ?? 1)))),
            self::fila('Total', self::e(self::monto($reserva['precioTotal'] ?? 0))),
            self::fila('Cliente', self::e($cliente)),
        ];

        return self::tabla($filas);
    }

    private static function aviso(string $html, string $tipo = 'info'): string {
        $colores = [
            'info'    => ['#eaf3ff', '#0d3262'],
            'warning' => ['#fff7e6', '#8a6100'],
            'success' => ['#e7f6ee', '#137a45'],
            'danger'  => ['#fdecec', '#a12622'],
        ];

        [$fondo, $color] = $colores[$tipo] ?? $colores['info'];

        return "
            <div style='background:{$fondo};border-left:4px solid {$color};border-radius:6px;padding:12px 14px;margin:18px 0;color:{$color};font-size:14px;line-height:1.5;'>
                {$html}
            </div>
        ";
    }

    private static function boton(string $texto, string $url): string {
        return "
            <p style='text-align:center;margin:26px 0;'>
                <a href='" . self::e($url) . "' style='display:inline-block;background:#2066ba;color:#ffffff;padding:12px 28px;border-radius:6px;text-decoration:none;font-weight:bold;font-size:15px;'>
                    " . self::e($texto) . "
                </a>
            </p>
        ";
    }

    // ── Método base de envío ──────────────────────────────────────────────────
    private static function enviar(string $destinatario, string $asunto, string $cuerpo, ?string $responderA = null): bool {
        try {
            $mail = crearMailer();

            $destinatarios = array_values(array_filter(array_map('trim', explode(',', $destinatario))));
            if (!$destinatarios) {
                throw new Exception('No hay destinatarios válidos para el correo.');
            }

            foreach ($destinatarios as $direccion) {
                $mail->addAddress($direccion);
            }

            if ($responderA !== null && filter_var($responderA, FILTER_VALIDATE_EMAIL)) {
                $mail->clearReplyTos();
                $mail->addReplyTo($responderA);
            }

            $mail->Subject = $asunto;
            $mail->isHTML(true);
            $mail->Body    = $cuerpo;
            $mail->AltBody = trim(strip_tags($cuerpo));
            $mail->send();
            return true;
        } catch (Throwable $e) {
            // No registramos direcciones de correo en el log: se reemplazan por [correo].
            $detalle = preg_replace(
                '/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i',
                '[correo]',
                (string)$e->getMessage()
            );
            error_log('No se pudo enviar el correo "' . $asunto . '": ' . $detalle);
            return false;
        }
    }

    // ── Consulta de Contacto ──────────────────────────────────────────────────
    public static function enviarConsultaContacto(string $nombre, string $email, string $asunto, string $mensaje): bool {
        // Mapear el asunto a un texto más legible para el mail
        $motivos = [
            'reservas'    => 'Consultas sobre Vuelos y Reservas',
            'equipaje'    => 'Equipaje y Requisitos de Viaje',
            'facturacion' => 'Facturación y Comprobantes',
            'reclamos'    => 'Reclamos o Sugerencias',
            'otro'        => 'Otro motivo',
        ];
        $motivoLegible = $motivos[$asunto] ?? 'Consulta general';

        $contenido = self::parrafo('Recibimos una nueva consulta desde el formulario de contacto.')
            . self::tabla([
                self::fila('Nombre', self::e($nombre)),
                self::fila('Correo electrónico', self::e($email)),
                self::fila('Motivo', self::e($motivoLegible)),
            ])
            . self::parrafo('<strong>Mensaje:</strong>')
            . self::parrafo(nl2br(self::e($mensaje)));

        $destinatarios = destinoContactoCorreo();
        if ($destinatarios === '') {
            error_log('Formulario de contacto sin destinatario configurado (MAIL_CONTACT_TO / MAIL_FROM).');
            return false;
        }

        // El Reply-To apunta al visitante para poder responderle directamente.
        return self::enviar(
            $destinatarios,
            'Nueva consulta de contacto: ' . $motivoLegible,
            self::plantilla('Nueva consulta de contacto', $contenido),
            $email
        );
    }
}
