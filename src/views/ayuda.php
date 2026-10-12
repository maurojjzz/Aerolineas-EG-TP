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
    <title>Centro de Ayuda — Volaré</title>
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
                
                <!-- Cabecera de la sección -->
                <div class="text-center mb-5">
                    <h2 class="fw-bold text-dark display-6">Centro de Ayuda</h2>
                    <p class="text-white m-0 fs-5" style="text-shadow: 0 1px 2px rgba(0,0,0,0.3);">¿Tenés dudas? Encontrá respuestas rápidas a las consultas más frecuentes.</p>
                </div>

                <!-- Tarjeta con el Acordeón de Preguntas Frecuentes -->
                <div class="card shadow-sm border-0 rounded-4 p-4 p-md-5 bg-white mb-4" id="faq">
                    <h4 class="fw-bold text-primary mb-4"><i class="bi bi-question-circle me-2"></i>Preguntas Frecuentes</h4>

                    <div class="accordion accordion-flush" id="accordionFAQ">
                        
                        <!-- Pregunta 1 -->
                        <div class="accordion-item border-bottom">
                            <h2 class="accordion-header" id="headingOne">
                                <button class="accordion-button collapsed fw-semibold text-dark shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                    ¿Cómo puedo buscar y reservar un vuelo?
                                </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionFAQ">
                                <div class="accordion-body text-secondary">
                                    Para buscar un vuelo, ingresá a la sección de <strong>Vuelos</strong> desde el menú principal. Completá la ciudad de origen, destino y fecha estimada en nuestro buscador inteligente. Seleccioná el vuelo que mejor se adapte a tus necesidades y seguí los pasos indicados para confirmar tu reserva.
                                </div>
                            </div>
                        </div>

                        <!-- Pregunta 2 -->
                        <div class="accordion-item border-bottom">
                            <h2 class="accordion-header" id="headingTwo">
                                <button class="accordion-button collapsed fw-semibold text-dark shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    ¿Cómo modifico o actualizo los datos de mi perfil?
                                </button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionFAQ">
                                <div class="accordion-body text-secondary">
                                    Haciendo clic sobre tu nombre de usuario en la esquina superior derecha, seleccioná la opción <strong>Mi Perfil</strong>. Allí podrás editar tu información personal, cambiar tu contraseña de forma segura y guardar los cambios al instante.
                                </div>
                            </div>
                        </div>

                        <!-- Pregunta 3 -->
                        <div class="accordion-item border-bottom">
                            <h2 class="accordion-header" id="headingThree">
                                <button class="accordion-button collapsed fw-semibold text-dark shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                    ¿Qué debo hacer si olvidé mi contraseña?
                                </button>
                            </h2>
                            <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionFAQ">
                                <div class="accordion-body text-secondary">
                                    En la pantalla de inicio de sesión, hacé clic en el enlace <em>"¿Olvidaste tu contraseña?"</em>. Ingresá el correo electrónico registrado y te enviaremos un enlace de recuperación seguro para que puedas establecer una nueva contraseña.
                                </div>
                            </div>
                        </div>

                        <!-- Pregunta 4 -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingFour">
                                <button class="accordion-button collapsed fw-semibold text-dark shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                    ¿Cómo puedo verificar el estado actual de mi vuelo o posibles demoras?
                                </button>
                            </h2>
                            <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#accordionFAQ">
                                <div class="accordion-body text-secondary">
                                    Podés consultar el estado en tiempo real de cualquier vuelo ingresando a la sección de novedades o buscando el número de vuelo en nuestro buscador para conocer si hay reprogramaciones, demoras o cambios en la puerta de embarque.
                                </div>
                            </div>
                        </div>

                        <!-- Pregunta 5 -->
                        <div class="accordion-item border-bottom">
                            <h2 class="accordion-header" id="headingFive">
                                <button class="accordion-button collapsed fw-semibold text-dark shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                    ¿Qué cantidad y peso de equipaje tengo permitido llevar?
                                </button>
                            </h2>
                            <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#accordionFAQ">
                                <div class="accordion-body text-secondary">
                                    El equipaje permitido depende de la tarifa seleccionada al momento de comprar tu pasaje. Por lo general, se incluye una mochila o bolso de mano pequeño que entra bajo el asiento delantero, y podés sumar equipaje de mano o bodega según las condiciones de tu pasaje.
                                </div>
                            </div>
                        </div>

                        <!-- Pregunta 6 -->
                        <div class="accordion-item border-bottom">
                            <h2 class="accordion-header" id="headingSix">
                                <button class="accordion-button collapsed fw-semibold text-dark shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSix" aria-expanded="false" aria-controls="collapseSix">
                                    ¿Con cuánta anticipación debo presentarme en el aeropuerto?
                                </button>
                            </h2>
                            <div id="collapseSix" class="accordion-collapse collapse" aria-labelledby="headingSix" data-bs-parent="#accordionFAQ">
                                <div class="accordion-body text-secondary">
                                    Para vuelos nacionales te recomendamos llegar al menos 2 horas antes de la hora de salida programada. Para vuelos internacionales, sugerimos estar en el aeropuerto con 3 horas de anticipación para realizar el despacho de equipaje y los controles de seguridad sin apuros.
                                </div>
                            </div>
                        </div>

                        <!-- Pregunta 7 -->
                        <div class="accordion-item border-bottom">
                            <h2 class="accordion-header" id="headingSeven">
                                <button class="accordion-button collapsed fw-semibold text-dark shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSeven" aria-expanded="false" aria-controls="collapseSeven">
                                    ¿Cómo y cuándo puedo realizar el check-in online?
                                </button>
                            </h2>
                            <div id="collapseSeven" class="accordion-collapse collapse" aria-labelledby="headingSeven" data-bs-parent="#accordionFAQ">
                                <div class="accordion-body text-secondary">
                                    El check-in digital se habilita entre 48 y 2 horas antes de la salida de tu vuelo. Podés realizarlo ingresando a la sección de tus reservas o vuelos, lo que te permitirá obtener tu tarjeta de embarque digital y ahorrar tiempo en el aeropuerto.
                                </div>
                            </div>
                        </div>

                        <!-- Pregunta 8 -->
                        <div class="accordion-item border-bottom">
                            <h2 class="accordion-header" id="headingEight">
                                <button class="accordion-button collapsed fw-semibold text-dark shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEight" aria-expanded="false" aria-controls="collapseEight">
                                    ¿Puedo cambiar la fecha o cancelar mi pasaje?
                                </button>
                            </h2>
                            <div id="collapseEight" class="accordion-collapse collapse" aria-labelledby="headingEight" data-bs-parent="#accordionFAQ">
                                <div class="accordion-body text-secondary">
                                    Sí, las modificaciones o cancelaciones dependen de las condiciones de la tarifa que adquiriste. Podés revisar los detalles de flexibilidad y posibles penalidades ingresando al detalle de tu reserva desde tu perfil de usuario.
                                </div>
                            </div>
                        </div>

                        <!-- Pregunta 9 -->
                        <div class="accordion-item border-bottom">
                            <h2 class="accordion-header" id="headingNine">
                                <button class="accordion-button collapsed fw-semibold text-dark shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNine" aria-expanded="false" aria-controls="collapseNine">
                                    ¿Qué documentación necesito presentar para abordar el vuelo?
                                </button>
                            </h2>
                            <div id="collapseNine" class="accordion-collapse collapse" aria-labelledby="headingNine" data-bs-parent="#accordionFAQ">
                                <div class="accordion-body text-secondary">
                                    Para vuelos nacionales debés presentar tu DNI físico vigente. Para vuelos internacionales, es obligatorio contar con tu pasaporte al día, además de las visas o certificados sanitarios que requiera el país de destino.
                                </div>
                            </div>
                        </div>

                        <!-- Pregunta 10 -->
                        <div class="accordion-item border-bottom">
                            <h2 class="accordion-header" id="headingTen">
                                <button class="accordion-button collapsed fw-semibold text-dark shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTen" aria-expanded="false" aria-controls="collapseTen">
                                    ¿Puedo seleccionar mi asiento con anticipación?
                                </button>
                            </h2>
                            <div id="collapseTen" class="accordion-collapse collapse" aria-labelledby="headingTen" data-bs-parent="#accordionFAQ">
                                <div class="accordion-body text-secondary">
                                    Sí, tenés la opción de elegir tu asiento preferido durante el proceso de compra del vuelo o ingresando posteriormente a los detalles de tu reserva, sujeto a la disponibilidad del avión.
                                </div>
                            </div>
                        </div>

                        <!-- Pregunta 11 -->
                        <div class="accordion-item border-bottom">
                            <h2 class="accordion-header" id="headingEleven">
                                <button class="accordion-button collapsed fw-semibold text-dark shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEleven" aria-expanded="false" aria-controls="collapseEleven">
                                    ¿Cómo puedo solicitar asistencia especial o silla de ruedas?
                                </button>
                            </h2>
                            <div id="collapseEleven" class="accordion-collapse collapse" aria-labelledby="headingEleven" data-bs-parent="#accordionFAQ">
                                <div class="accordion-body text-secondary">
                                    Si necesitás asistencia por movilidad reducida, atención médica especial o viajar con un perro guía, podés solicitarlo comunicándote a través de nuestro <a href="<?= url('index.php?pagina=contacto') ?>" class="text-primary text-decoration-underline">Formulario de Contacto</a> con al menos 48 horas hábiles de anticipación a tu vuelo.
                                </div>
                            </div>
                        </div>

                        <!-- Pregunta 12 -->
                        <div class="accordion-item border-bottom">
                            <h2 class="accordion-header" id="headingTwelve">
                                <button class="accordion-button collapsed fw-semibold text-dark shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwelve" aria-expanded="false" aria-controls="collapseTwelve">
                                    ¿Está permitido viajar con mascotas?
                                </button>
                            </h2>
                            <div id="collapseTwelve" class="accordion-collapse collapse" aria-labelledby="headingTwelve" data-bs-parent="#accordionFAQ">
                                <div class="accordion-body text-secondary">
                                    Sí, aceptamos mascotas (perros y gatos pequeños) en cabina bajo ciertas normativas de peso, contenedor y documentación veterinaria al día. Te sugerimos consultar las condiciones específicas de transporte animal antes de realizar tu reserva.
                                </div>
                            </div>
                        </div>

                        <!-- Pregunta 13 -->
                        <div class="accordion-item border-bottom">
                            <h2 class="accordion-header" id="headingThirteen">
                                <button class="accordion-button collapsed fw-semibold text-dark shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThirteen" aria-expanded="false" aria-controls="collapseThirteen">
                                    ¿Dónde puedo descargar la factura o comprobante de mi compra?
                                </button>
                            </h2>
                            <div id="collapseThirteen" class="accordion-collapse collapse" aria-labelledby="headingThirteen" data-bs-parent="#accordionFAQ">
                                <div class="accordion-body text-secondary">
                                    Una vez confirmado el pago de tu vuelo, el comprobante y los detalles de la factura se envían automáticamente a tu correo electrónico registrado. También podés solicitarlos o visualizarlos desde el historial de transacciones en tu perfil.
                                </div>
                            </div>
                        </div>

                        <!-- Pregunta 14 -->
                        <div class="accordion-item border-bottom">
                            <h2 class="accordion-header" id="headingFourteen">
                                <button class="accordion-button collapsed fw-semibold text-dark shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFourteen" aria-expanded="false" aria-controls="collapseFourteen">
                                    ¿Cómo se aplican los códigos de descuento o promociones?
                                </button>
                            </h2>
                            <div id="collapseFourteen" class="accordion-collapse collapse" aria-labelledby="headingFourteen" data-bs-parent="#accordionFAQ">
                                <div class="accordion-body text-secondary">
                                    Si disponés de un código promocional, podrás ingresarlo en el campo habilitado dentro del resumen de tu compra antes de confirmar el pago, aplicando el descuento de manera automática sobre la tarifa base.
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Tarjeta de llamada a la acción (Contacto) -->
                <div class="card shadow-sm border-0 rounded-4 p-4 text-center bg-white">
                    <h5 class="fw-bold text-dark mb-2">¿No encontraste lo que buscabas?</h5>
                    <p class="text-secondary mb-3">Estamos para ayudarte a resolver cualquier duda o inconveniente.</p>
                    <div>
                        <a href="<?= url('index.php?pagina=contacto') ?>" class="btn btn-primary px-4 py-2">
                            <i class="bi bi-headset me-2"></i>Ir al Formulario de Contacto
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <?php require __DIR__ . '/layouts/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>