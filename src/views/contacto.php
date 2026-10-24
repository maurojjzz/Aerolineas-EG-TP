<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../services/EmailService.php';

$enviadoExitoso = false;

// Si se envió el formulario por POST, procesamos acá mismo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $asunto = trim($_POST['asunto'] ?? '');
    $mensaje = trim($_POST['mensaje'] ?? '');

    // Acá harías la lógica (enviar mail, guardar en BD, etc.)
    
    // Redirigimos a la misma página con el parámetro enviado=1 para mostrar el cartel verde y evitar reenvíos al refrescar
    header('Location: ' . url('index.php?pagina=contacto&enviado=1'));
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto — Volaré</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= url('public/css/layout/header.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= url('public/css/layout/homeCliente.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= url('public/css/components/menuMobile.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= url('public/css/components/searchDesktop.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/bootstrap-icons.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/layout/reservas.css') ?>">
</head>
<body>

    <!-- Header unificado -->
    <?php require __DIR__ . '/layouts/header.php'; ?>

    <div class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                
                <!-- Cabecera de la sección -->
                <div class="titulo-cuadro-resul text-center mt-0 mb-5 user-select-none">
                    <h2 class="fw-bold text-dark display-6">FORMULARIO DE <span>CONTACTO</span></h2>
                    <p class=" m-0 fs-5" style="text-shadow: 0 1px 2px rgba(0,0,0,0.3);">¿Tenés alguna consulta o sugerencia? Escribinos y te responderemos a la brevedad.</p>
                </div>

                <!-- Tarjeta del Formulario -->
                <div class="card shadow-sm border-0 rounded-4 p-4 p-md-5 bg-white mb-4">
                    
                    <?php if (isset($_GET['enviado']) && $_GET['enviado'] == '1'): ?>
                        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i>¡Tu mensaje fue enviado con éxito! Nos pondremos en contacto pronto.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="<?= url('index.php?pagina=contacto') ?>" method="POST" novalidate>

                        <div class="row">
                            <!-- Nombre y Apellido -->
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label fw-semibold text-dark">Nombre y Apellido</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-secondary"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control shadow-none" id="nombre" name="nombre" placeholder="Tu nombre" required>
                                </div>
                                <div class="invalid-feedback text-danger small mt-1" style="display: none;">Por favor, ingresa tu nombre y apellido.</div>
                            </div>

                            <!-- Correo Electrónico -->
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label fw-semibold text-dark">Correo Electrónico</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-secondary"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control shadow-none" id="email" name="email" placeholder="correo@ejemplo.com" required>
                                </div>
                                <div class="invalid-feedback text-danger small mt-1" style="display: none;" id="email-feedback">Por favor, ingresa un correo electrónico válido.</div>
                            </div>
                        </div>

                        <!-- Asunto -->
                        <div class="mb-3">
                            <label for="asunto" class="form-label fw-semibold text-dark">Asunto</label>
                            <select class="form-select shadow-none" id="asunto" name="asunto" required>
                                <option value="" selected disabled>Seleccioná el motivo de tu consulta</option>
                                <option value="reservas">Consultas sobre Vuelos y Reservas</option>
                                <option value="equipaje">Equipaje y Requisitos de Viaje</option>
                                <option value="facturacion">Facturación y Comprobantes</option>
                                <option value="reclamos">Reclamos o Sugerencias</option>
                                <option value="otro">Otro motivo</option>
                            </select>
                            <div class="invalid-feedback text-danger small mt-1" style="display: none;">Por favor, seleccioná un motivo de consulta.</div>
                        </div>

                        <!-- Mensaje -->
                        <div class="mb-4">
                            <label for="mensaje" class="form-label fw-semibold text-dark">Mensaje</label>
                            <textarea class="form-control shadow-none" id="mensaje" name="mensaje" rows="5" placeholder="Escribí los detalles de tu consulta aquí..." required></textarea>
                            <div class="invalid-feedback text-danger small mt-1" style="display: none;">Por favor, escribí los detalles de tu mensaje.</div>
                        </div>

                        <!-- Botón de Envío -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary py-3 fw-semibold">
                                <i class="bi bi-send me-2"></i>Enviar Mensaje
                            </button>
                        </div>

                    </form>
                </div>

                <!-- Tarjeta de información secundaria -->
                <div class="card shadow-sm border-0 rounded-4 p-4 text-center bg-white">
                    <p class="text-secondary mb-1">También podés comunicarte directamente con nuestro equipo a través de:</p>
                    <p class="fw-bold text-dark mb-0"><i class="bi bi-envelope-at text-primary me-2"></i>soporte@volare.com</p>
                </div>

            </div>
        </div>
    </div>

    <!-- Footer unificado -->
    <?php require __DIR__ . '/layouts/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('form');
        const inputs = form.querySelectorAll('input, select, textarea');

        inputs.forEach(input => {
            // Validar al salir del campo (onblur)
            input.addEventListener('blur', function () {
                validateField(this);
            });

            // Ocultar error y limpiar borde apenas el usuario escribe o selecciona
            input.addEventListener('input', function () {
                this.classList.remove('is-invalid');
                const parentContainer = this.closest('.input-group') ? this.closest('.col-md-6, .mb-3, .mb-4') : this.parentElement;
                const feedbackElement = parentContainer.querySelector('.invalid-feedback');
                if (feedbackElement) feedbackElement.style.display = 'none';
            });

            input.addEventListener('change', function () {
                this.classList.remove('is-invalid');
                const parentContainer = this.closest('.input-group') ? this.closest('.col-md-6, .mb-3, .mb-4') : this.parentElement;
                const feedbackElement = parentContainer.querySelector('.invalid-feedback');
                if (feedbackElement) feedbackElement.style.display = 'none';
            });
        });

        form.addEventListener('submit', function (event) {
            let isValid = true;
            inputs.forEach(input => {
                if (!validateField(input)) {
                    isValid = false;
                }
            });

            if (!isValid) {
                event.preventDefault(); // Detiene el envío si hay errores
            }
        });

        function validateField(field) {
            let valid = true;
            const parentContainer = field.closest('.input-group') ? field.closest('.col-md-6, .mb-3, .mb-4') : field.parentElement;
            const feedbackElement = parentContainer.querySelector('.invalid-feedback');

            // Validación de campo vacío (requerido)
            if (field.hasAttribute('required') && !field.value.trim()) {
                valid = false;
                if (feedbackElement) {
                    if (field.tagName === 'SELECT') {
                        feedbackElement.textContent = 'Por favor, seleccioná un motivo de consulta.';
                    } else if (field.type === 'email') {
                        feedbackElement.textContent = 'Por favor, ingresa tu correo electrónico.';
                    } else {
                        feedbackElement.textContent = 'Por favor, completa este campo.';
                    }
                }
            } 
            // Validación específica para formato de email
            else if (field.type === 'email' && field.value.trim()) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(field.value.trim())) {
                    valid = false;
                    if (feedbackElement) {
                        feedbackElement.textContent = 'El formato del correo electrónico no es válido.';
                    }
                }
            }

            if (!valid) {
                field.classList.add('is-invalid');
                if (feedbackElement) feedbackElement.style.display = 'block';
                return false;
            } else {
                field.classList.remove('is-invalid');
                if (feedbackElement) feedbackElement.style.display = 'none';
                return true;
            }
        }
    });
    </script>
</body>
</html>