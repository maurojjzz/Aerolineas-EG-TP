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
    <title>Sobre Volaré — Volaré</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= url('public/css/layout/header.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= url('public/css/layout/homeCliente.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= url('public/css/components/menuMobile.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= url('public/css/accesibilidad.css') ?>">
</head>
<body>

    <!-- Header unificado -->
    <?php require __DIR__ . '/layouts/header.php'; ?>

    <main id="contenido" tabindex="-1" class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-9">

                <div class="card shadow-sm border-0 rounded-4 p-4 p-md-5 bg-white">

                    <div class="mb-4 pb-3 border-bottom">
                        <h1 class="fw-bold text-dark display-6 mb-2">Sobre Volaré</h1>
                        <p class="text-secondary m-0">Quiénes somos y cómo trabajamos para que tu viaje empiece bien.</p>
                    </div>

                    <div class="text-secondary">
                        <section aria-labelledby="sobreQuienesSomos">
                            <h2 class="h5 fw-bold text-dark mt-4" id="sobreQuienesSomos">Quiénes somos</h2>
                            <p>
                                <strong>Volaré</strong> es una plataforma de reserva de pasajes aéreos que reúne en un solo lugar
                                la búsqueda de vuelos, la gestión de reservas y la información comercial de las aerolíneas que
                                operan con nosotros. Nuestro objetivo es que reservar un pasaje sea un trámite simple, claro y
                                seguro, tanto desde una computadora como desde el teléfono.
                            </p>
                        </section>

                        <section aria-labelledby="sobreMision">
                            <h2 class="h5 fw-bold text-dark mt-4" id="sobreMision">Misión</h2>
                            <p>
                                Facilitar el acceso al transporte aéreo ofreciendo una experiencia de compra transparente,
                                con información completa sobre vuelos, tarifas, promociones y condiciones antes de confirmar
                                cualquier reserva.
                            </p>
                        </section>

                        <section aria-labelledby="sobreVision">
                            <h2 class="h5 fw-bold text-dark mt-4" id="sobreVision">Visión</h2>
                            <p>
                                Ser la plataforma de referencia para viajar por Argentina y la región, reconocida por la
                                confiabilidad de su información, la calidad de su atención y el respeto por sus pasajeros.
                            </p>
                        </section>

                        <section aria-labelledby="sobreValores">
                            <h2 class="h5 fw-bold text-dark mt-4" id="sobreValores">Valores</h2>
                            <ul class="ps-3">
                                <li class="mb-2"><strong>Transparencia:</strong> mostramos el precio final y las condiciones de cada pasaje antes de la compra.</li>
                                <li class="mb-2"><strong>Seguridad:</strong> protegemos los datos personales de quienes navegan y reservan en el sitio.</li>
                                <li class="mb-2"><strong>Accesibilidad:</strong> el sitio se puede usar con teclado, lector de pantalla y zoom, y ese compromiso se revisa de forma permanente.</li>
                                <li class="mb-2"><strong>Cercanía:</strong> atendemos consultas y reclamos por canales claros y con respuestas dentro de plazos razonables.</li>
                            </ul>
                        </section>

                        <section aria-labelledby="sobreAutoridades">
                            <h2 class="h5 fw-bold text-dark mt-4" id="sobreAutoridades">Autoridades y organización</h2>
                            <!-- Reemplazar este bloque con los datos reales de las autoridades y el organigrama institucional. -->
                            <p>
                                La información institucional sobre autoridades y organigrama se encuentra en proceso de
                                publicación. Mientras tanto, cualquier consulta sobre la organización de la empresa puede
                                realizarse a través de nuestro <a href="<?= url('index.php?pagina=contacto') ?>">formulario de contacto</a>.
                            </p>
                        </section>

                        <section aria-labelledby="sobreAccesibilidad">
                            <h2 class="h5 fw-bold text-dark mt-4" id="sobreAccesibilidad">Compromiso con la accesibilidad</h2>
                            <p>
                                Trabajamos para que Volaré cumpla con las pautas WCAG 2.1 nivel AA: estructura de encabezados
                                consistente, navegación completa por teclado, textos alternativos en las imágenes, formularios
                                con etiquetas asociadas y contraste suficiente. Si encontrás una barrera de accesibilidad,
                                escribinos y la revisamos.
                            </p>
                        </section>
                    </div>

                    <div class="mt-5 pt-3 border-top text-center">
                        <a href="<?= url('index.php?pagina=inicio') ?>" class="btn btn-outline-primary px-4 fw-semibold">
                            <i class="bi bi-arrow-left me-2" aria-hidden="true"></i>Volver al Inicio
                        </a>
                    </div>

                </div>

            </div>
        </div>
    </main>

    <!-- Footer unificado -->
    <?php require __DIR__ . '/layouts/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
