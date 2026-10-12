<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Políticas de Privacidad — Volaré</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= url('public/css/layout/header.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= url('public/css/layout/homeCliente.css') ?>?v=<?= time() ?>">
</head>
<body>

    <!-- Header unificado -->
    <?php require __DIR__ . '/layouts/header.php'; ?>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-9">
                
                <!-- Tarjeta contenedora -->
                <div class="card shadow-sm border-0 rounded-4 p-4 p-md-5 bg-white">
                    
                    <div class="mb-4 pb-3 border-bottom">
                        <h2 class="fw-bold text-dark display-6 mb-2">Políticas de Privacidad</h2>
                        <p class="text-secondary m-0">Última actualización: Octubre de 2026</p>
                    </div>

                    <div class="text-secondary">
                        <h5 class="fw-bold text-dark mt-4">1. Recopilación de Información</h5>
                        <p>En <strong>Volaré</strong> recopilamos información personal que nos proporcionás directamente al registrarte, actualizar tu perfil, realizar una reserva de vuelo o comunicarte con nuestro soporte (como tu nombre, correo electrónico, DNI/pasaporte y datos de contacto).</p>

                        <h5 class="fw-bold text-dark mt-4">2. Uso de los Datos</h5>
                        <p>Utilizamos tus datos personales exclusivamente para procesar tus reservas de pasajes, emitir comprobantes, gestionar la seguridad de tu cuenta, enviar notificaciones importantes sobre el estado de tus vuelos y responder a tus consultas de asistencia.</p>

                        <h5 class="fw-bold text-dark mt-4">3. Protección y Seguridad</h5>
                        <p>Implementamos medidas de seguridad técnicas y organizativas adecuadas para proteger tu información personal contra accesos no autorizados, alteraciones, divulgación o destrucción indebida.</p>

                        <h5 class="fw-bold text-dark mt-4">4. Compartir Información</h5>
                        <p>No comercializamos ni alquilamos tus datos personales a terceros. La información solo se comparte cuando es estrictamente necesario para cumplir con regulaciones de transporte aéreo o procesamientos de pago autorizados.</p>

                        <h5 class="fw-bold text-dark mt-4">5. Tus Derechos</h5>
                        <p>Tenés derecho a acceder, rectificar o solicitar la eliminación de tus datos personales ingresando a la configuración de tu perfil o comunicándote directamente a través de nuestro formulario de contacto.</p>
                    </div>

                    <div class="mt-5 pt-3 border-top text-center">
                        <a href="<?= url('index.php?pagina=inicio') ?>" class="btn btn-outline-primary px-4 fw-semibold">
                            <i class="bi bi-arrow-left me-2"></i>Volver al Inicio
                        </a>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <!-- Footer unificado -->
    <?php require __DIR__ . '/layouts/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>