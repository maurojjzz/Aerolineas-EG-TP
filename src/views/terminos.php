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
    <title>Términos y Condiciones — Volaré</title>
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
                        <h2 class="fw-bold text-dark display-6 mb-2">Términos y Condiciones</h2>
                        <p class="text-secondary m-0">Última actualización: Octubre de 2026</p>
                    </div>

                    <div class="text-secondary">
                        <h5 class="fw-bold text-dark mt-4">1. Introducción</h5>
                        <p>Bienvenido a <strong>Volaré</strong>. Al acceder, registrarte y utilizar nuestro sitio web y servicios de reserva de pasajes aéreos, aceptás cumplir y quedar sujeto a los siguientes términos y condiciones de uso.</p>

                        <h5 class="fw-bold text-dark mt-4">2. Uso del Sitio</h5>
                        <p>Te comprometes a utilizar nuestra plataforma exclusivamente para fines lícitos y de manera que no infrinja los derechos de terceros, ni restrinja o inhiba el uso y disfrute del sitio por parte de otros usuarios. Queda prohibido el uso de sistemas automatizados o scraping sin autorización expresa.</p>

                        <h5 class="fw-bold text-dark mt-4">3. Reservas y Tarifas</h5>
                        <p>Todas las reservas de vuelos realizadas a través de nuestra plataforma están sujetas a disponibilidad y a las condiciones específicas de la tarifa seleccionada al momento de la compra (políticas de equipaje, cambios, cancelaciones y reembolsos).</p>

                        <h5 class="fw-bold text-dark mt-4">4. Cuentas de Usuario</h5>
                        <p>Sos responsable de mantener la confidencialidad de los datos de acceso a tu cuenta (nombre de usuario y contraseña) y de restringir el acceso a tu dispositivo para prevenir usos no autorizados.</p>

                        <h5 class="fw-bold text-dark mt-4">5. Modificaciones</h5>
                        <p>Nos reservamos el derecho de modificar estos términos y condiciones en cualquier momento. Las modificaciones entrarán en vigencia de forma inmediata tras su publicación en el sitio web.</p>
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