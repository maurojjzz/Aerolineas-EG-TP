<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../services/EmailService.php';

$enviadoExitoso = false;

// Si se envió el formulario por POST, procesamos acá mismo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tokenPost    = $_POST['csrf_token'] ?? '';
    $tokenSession = $_SESSION['csrf_token'] ?? '';

    if (empty($tokenPost) || empty($tokenSession) || !hash_equals($tokenSession, $tokenPost)) {
        flash_set('error', 'Token de seguridad inválido o sesión expirada. Volvé a intentarlo.');
        redirect('index.php?pagina=contacto');
    }

    $nombre  = trim($_POST['nombre'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $asunto  = trim($_POST['asunto'] ?? '');
    $mensaje = trim($_POST['mensaje'] ?? '');

    $motivosValidos = ['reservas', 'equipaje', 'facturacion', 'reclamos', 'otro'];
    $errores = [];

    if ($nombre === '' || mb_strlen($nombre) > 120) {
        $errores[] = 'Ingresá un nombre válido (máximo 120 caracteres).';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'Ingresá un correo electrónico válido.';
    }

    if (!in_array($asunto, $motivosValidos, true)) {
        $errores[] = 'Seleccioná el motivo de tu consulta.';
    }

    if (mb_strlen($mensaje) < 10) {
        $errores[] = 'Contanos un poco más: el mensaje debe tener al menos 10 caracteres.';
    } elseif (mb_strlen($mensaje) > 5000) {
        $errores[] = 'El mensaje no puede superar los 5000 caracteres.';
    }

    if ($errores) {
        flash_set('error', implode(' ', $errores));
        redirect('index.php?pagina=contacto');
    }

    if (EmailService::enviarConsultaContacto($nombre, $email, $asunto, $mensaje)) {
        flash_set('success', '¡Tu mensaje fue enviado con éxito! Nos pondremos en contacto pronto.');
        redirect('index.php?pagina=contacto');
    }

    flash_set('error', 'No pudimos enviar tu mensaje en este momento. Intentá nuevamente o escribinos a soporte@volare.com.');
    redirect('index.php?pagina=contacto');
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
    <link rel="stylesheet" href="<?= url('public/css/accesibilidad.css') ?>">
</head>
<body>

    <!-- Header unificado -->
    <?php require __DIR__ . '/layouts/header.php'; ?>

    <main id="contenido" tabindex="-1" class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                
                <!-- Cabecera de la sección -->
                <div class="titulo-cuadro-resul text-center mt-0 mb-5 user-select-none">
                    <h1 class="fw-bold text-dark display-6">FORMULARIO DE <span>CONTACTO</span></h1>
                    <p class=" m-0 fs-5" style="text-shadow: 0 1px 2px rgba(0,0,0,0.3);">¿Tenés alguna consulta o sugerencia? Escribinos y te responderemos a la brevedad.</p>
                </div>

                <!-- Tarjeta del Formulario -->
                <div class="card shadow-sm border-0 rounded-4 p-4 p-md-5 bg-white mb-4">

                    <?php require __DIR__ . '/components/alertToast.php'; ?>

                    <?php if (isset($_GET['enviado']) && $_GET['enviado'] == '1'): ?>
                        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i>¡Tu mensaje fue enviado con éxito! Nos pondremos en contacto pronto.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                        </div>
                    <?php endif; ?>

                    <form action="<?= url('index.php?pagina=contacto') ?>" method="POST" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                        <p class="small text-secondary mb-3">
                            Los campos con <span class="text-danger" aria-hidden="true">*</span>
                            <span class="visually-hidden">asterisco</span> son obligatorios.
                        </p>

                        <div id="resumenErroresContacto" class="alert alert-danger d-none" role="alert" tabindex="-1"></div>

                        <div class="row">
                            <!-- Nombre y Apellido -->
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label fw-semibold text-dark">
                                    Nombre y Apellido <span class="text-danger" aria-hidden="true">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-secondary" aria-hidden="true"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control shadow-none" id="nombre" name="nombre"
                                           placeholder="Tu nombre" autocomplete="name" required
                                           aria-describedby="nombre-feedback">
                                </div>
                                <div class="invalid-feedback text-danger small mt-1" id="nombre-feedback" role="alert"></div>
                            </div>

                            <!-- Correo Electrónico -->
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label fw-semibold text-dark">
                                    Correo Electrónico <span class="text-danger" aria-hidden="true">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-secondary" aria-hidden="true"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control shadow-none" id="email" name="email"
                                           placeholder="correo@ejemplo.com" autocomplete="email" required
                                           aria-describedby="email-feedback">
                                </div>
                                <div class="invalid-feedback text-danger small mt-1" id="email-feedback" role="alert"></div>
                            </div>
                        </div>

                        <!-- Asunto -->
                        <div class="mb-3">
                            <label for="asunto" class="form-label fw-semibold text-dark">
                                Asunto <span class="text-danger" aria-hidden="true">*</span>
                            </label>
                            <select class="form-select shadow-none" id="asunto" name="asunto" required
                                    aria-describedby="asunto-feedback">
                                <option value="" selected disabled>Seleccioná el motivo de tu consulta</option>
                                <option value="reservas">Consultas sobre Vuelos y Reservas</option>
                                <option value="equipaje">Equipaje y Requisitos de Viaje</option>
                                <option value="facturacion">Facturación y Comprobantes</option>
                                <option value="reclamos">Reclamos o Sugerencias</option>
                                <option value="otro">Otro motivo</option>
                            </select>
                            <div class="invalid-feedback text-danger small mt-1" id="asunto-feedback" role="alert"></div>
                        </div>

                        <!-- Mensaje -->
                        <div class="mb-4">
                            <label for="mensaje" class="form-label fw-semibold text-dark">
                                Mensaje <span class="text-danger" aria-hidden="true">*</span>
                            </label>
                            <textarea class="form-control shadow-none" id="mensaje" name="mensaje" rows="5"
                                      placeholder="Escribí los detalles de tu consulta aquí..." required
                                      aria-describedby="mensaje-feedback"></textarea>
                            <div class="invalid-feedback text-danger small mt-1" id="mensaje-feedback" role="alert"></div>
                        </div>

                        <!-- Botón de Envío -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary py-3 fw-semibold">
                                <i class="bi bi-send me-2" aria-hidden="true"></i>Enviar Mensaje
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
    </main>

    <!-- Footer unificado -->
    <?php require __DIR__ . '/layouts/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('form');
        const inputs = form.querySelectorAll('input, select, textarea');
        const resumen = document.getElementById('resumenErroresContacto');

        const feedbackDe = (field) => document.getElementById(field.id + '-feedback');

        const limpiarError = (field) => {
            field.classList.remove('is-invalid');
            field.removeAttribute('aria-invalid');
            const feedback = feedbackDe(field);
            if (feedback) feedback.textContent = '';
        };

        inputs.forEach(input => {
            // Validar al salir del campo (onblur)
            input.addEventListener('blur', function () {
                validateField(this);
            });

            // Limpiar el error apenas el usuario escribe o selecciona
            input.addEventListener('input', function () {
                limpiarError(this);
            });

            input.addEventListener('change', function () {
                limpiarError(this);
            });
        });

        form.addEventListener('submit', function (event) {
            let isValid = true;
            const invalidos = [];

            inputs.forEach(input => {
                if (!validateField(input)) {
                    isValid = false;
                    invalidos.push(input);
                }
            });

            if (!isValid) {
                event.preventDefault(); // Detiene el envío si hay errores

                if (resumen) {
                    const cantidad = invalidos.length;
                    resumen.textContent = cantidad === 1
                        ? 'Revisá el campo marcado: falta completarlo o su formato no es válido.'
                        : `Revisá los ${cantidad} campos marcados: faltan completarlos o su formato no es válido.`;
                    resumen.classList.remove('d-none');
                    resumen.focus();
                }

                if (invalidos.length) {
                    invalidos[0].focus();
                }
            } else if (resumen) {
                resumen.classList.add('d-none');
                resumen.textContent = '';
            }
        });

        function validateField(field) {
            let valid = true;
            const feedbackElement = feedbackDe(field);

            // Validación de campo vacío (requerido)
            if (field.hasAttribute('required') && !field.value.trim()) {
                valid = false;
                if (feedbackElement) {
                    if (field.tagName === 'SELECT') {
                        feedbackElement.textContent = 'Por favor, seleccioná un motivo de consulta.';
                    } else if (field.type === 'email') {
                        feedbackElement.textContent = 'Por favor, ingresá tu correo electrónico.';
                    } else {
                        feedbackElement.textContent = 'Por favor, completá este campo.';
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
                field.setAttribute('aria-invalid', 'true');
                return false;
            } else {
                field.classList.remove('is-invalid');
                field.removeAttribute('aria-invalid');
                if (feedbackElement) feedbackElement.textContent = '';
                return true;
            }
        }
    });
    </script>
</body>
</html>