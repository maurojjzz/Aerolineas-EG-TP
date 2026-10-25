<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../controllers/UsuarioController.php';

// Verificar que haya un usuario autenticado
if (!isset($_SESSION['usuario'])) {
    redirect('index.php?pagina=login');
}

$idUsuario = (int)$_SESSION['usuario']['idUsuario'];
$ctrl      = new UsuarioController($link);
$perfil   = $ctrl->obtenerUsuarioPorId($idUsuario);

if (!$perfil) {
    flash_set('error', 'No se encontró la información del usuario.');
    redirect('index.php?pagina=inicio');
}

$rolPerfil = strtolower(trim($perfil['rol'] ?? 'cliente'));

// Definir a qué panel vuelve el perfil según su rol
$urlVolver = match ($rolPerfil) {
    'admin' => url('index.php?pagina=aerolinea&seccion=listado'),
    'ceo'   => url('index.php?pagina=ceo'),
    default => url('index.php?pagina=cliente')
};

$tiposDoc = ['DNI', 'Pasaporte', 'LC', 'LE'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil — Volaré</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= url('public/css/layout/header.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= url('public/css/components/menuMobile.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= url('public/css/layout/homeCliente.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= url('public/css/components/searchMobile.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/components/searchDesktop.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/bootstrap-icons.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/layout/reservas.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/accesibilidad.css') ?>">
</head>
<body>

    <!-- Header unificado -->
    <?php require __DIR__ . '/layouts/header.php'; ?>

    <main id="contenido" tabindex="-1" class="container pb-4">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-7">
                
                
                <!-- Cabecera de la sección -->
                <div class="titulo-cuadro-resul text-center mt-0 mb-5 user-select-none">
                    <h1 class="fw-bold text-dark display-6">MI <span>PERFIL</span></h1>
                </div>
                
                <!-- Formulario principal con novalidate -->
                <div class="card shadow-sm border-0 rounded-4 p-4 bg-white">
                    <?php require __DIR__ . '/components/alertToast.php'; ?>
                    <form action="<?= url('src/routes/usuarios.php?accion=editarPerfilPropio') ?>" method="POST" class="d-flex flex-column gap-3" id="formPerfil" novalidate>
<p class="small text-secondary mb-2">Los campos con <span class="text-danger" aria-hidden="true">*</span><span class="visually-hidden">asterisco</span> son obligatorios.</p>
                        
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <input type="hidden" name="idUsuario" value="<?= $idUsuario ?>">

                        <!-- Fila 1: Nombre y Apellido -->
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="nombre" style="font-size:0.9rem;">Nombre *</label>
                                <input type="text" name="nombre" id="nombre" class="form-control" required minlength="2" maxlength="50"
                                       value="<?= htmlspecialchars($perfil['nombre'] ?? '') ?>">
                                <div class="invalid-feedback text-danger small" id="error-nombre" role="alert"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="apellido" style="font-size:0.9rem;">Apellido *</label>
                                <input type="text" name="apellido" id="apellido" class="form-control" required minlength="2" maxlength="50"
                                       value="<?= htmlspecialchars($perfil['apellido'] ?? '') ?>">
                                <div class="invalid-feedback text-danger small" id="error-apellido" role="alert"></div>
                            </div>
                        </div>

                        <!-- Fila 2: Documento -->
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="tipoDocumento" style="font-size:0.9rem;">Tipo de Documento *</label>
                                <select name="tipoDocumento" id="tipoDocumento" class="form-select" required>
                                    <option value="">Seleccioná un tipo de documento</option>
                                    <?php foreach ($tiposDoc as $td): ?>
                                        <option value="<?= $td ?>" <?= (strcasecmp(trim($perfil['tipoDocumento'] ?? ''),$td) === 0) ? 'selected' : '' ?>>
                                            <?= $td ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="nroDocumento" style="font-size:0.9rem;">Número de Documento *</label>
                                <input 
                                    type="text" 
                                    name="nroDocumento" 
                                    id="nroDocumento" 
                                    autocomplete="off" 
                                    class="form-control ctm-inp" 
                                    required 
                                    minlength="8" 
                                    maxlength="15"
                                    value="<?= htmlspecialchars($perfil['nroDocumento'] ) ?>"
                                >
                                <div class="invalid-feedback text-danger small" id="error-nroDocumento" role="alert"></div>
                            </div>
                        </div>

                        <!-- Fila 3: Email y Teléfono -->
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="email" style="font-size:0.9rem;">Correo Electrónico *</label>
                                <input type="email" name="email" id="email" class="form-control" required
                                       value="<?= htmlspecialchars($perfil['email'] ?? '') ?>">
                                <div class="invalid-feedback text-danger small" id="error-email" role="alert"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="telefono" style="font-size:0.9rem;">Teléfono de Contacto</label>
                                <input type="tel" name="telefono" id="telefono" class="form-control" pattern="[0-9+() -]{7,}" maxlength="25" placeholder="Ej. +54 341 1234567"
                                       value="<?= htmlspecialchars($perfil['telefono'] ?? '') ?>">
                                <div class="invalid-feedback text-danger small" id="error-telefono" role="alert"></div>
                            </div>
                        </div>

                        <!-- Información contextual según rol -->
                        <?php if ($rolPerfil === 'ceo' && !empty($perfil['nombreAerolinea'])): ?>
                            <div>
                                <label class="form-label fw-semibold text-muted" style="font-size:0.9rem;">Aerolínea a Cargo</label>
                                <input type="text" class="form-control bg-light" readonly 
                                       value="<?= htmlspecialchars($perfil['nombreAerolinea'] ?? '') ?>">
                            </div>
                        <?php endif; ?>

                        <!-- Fila de Cambio de Contraseña (Opcional) -->
                        <hr >
                        <h2 class="h5 fw-bold fs-6 text-primary">Cambiar Contraseña (Opcional)</h2>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="passwordActual" style="font-size:0.9rem;">Contraseña Actual</label>
                                <div class="input-group">
                                    <input type="password" name="passwordActual" id="passwordActual" class="form-control" placeholder="••••••••">
                                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="passwordActual" aria-label="Mostrar contraseña actual" aria-pressed="false">
                                        <i class="bi bi-eye" aria-hidden="true"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback text-danger small d-block" id="error-passwordActual" style="min-height: 20px;" role="alert"></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="passwordNueva" style="font-size:0.9rem;">Nueva Contraseña</label>
                                <div class="input-group">
                                    <input type="password" name="passwordNueva" id="passwordNueva" class="form-control" placeholder="••••••••" minlength="8">
                                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="passwordNueva" aria-label="Mostrar nueva contraseña" aria-pressed="false">
                                        <i class="bi bi-eye" aria-hidden="true"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback text-danger small d-block" id="error-passwordNueva" style="min-height: 20px;" role="alert"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold" for="passwordConfirmar" style="font-size:0.9rem;">Confirmar Nueva Contraseña</label>
                                <div class="input-group">
                                    <input type="password" name="passwordConfirmar" id="passwordConfirmar" class="form-control" placeholder="••••••••" minlength="8">
                                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="passwordConfirmar" aria-label="Mostrar confirmación de contraseña" aria-pressed="false">
                                        <i class="bi bi-eye" aria-hidden="true"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback text-danger small d-block" id="error-passwordConfirmar" style="min-height: 20px;" role="alert"></div>
                            </div>
                        </div>

                        <!-- Acciones -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?= $urlVolver ?>" class="btn btn-outline-danger">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                        </div>

                    </form>
                </div>

            </div>
        </div>
    </main>

    <?php require __DIR__ . '/layouts/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const form = document.getElementById("formPerfil");
    if (!form) return;

    const campos = form.querySelectorAll("input[required], select[required]");
    const passActual = document.getElementById("passwordActual");
    const passNueva = document.getElementById("passwordNueva");
    const passConfirmar = document.getElementById("passwordConfirmar");

    function validarCampo(input) {
        const errorDiv = document.getElementById("error-" + input.name);
        if (!errorDiv) return true;

        if (input.hasAttribute("required") && !input.value.trim()) {
            input.classList.add("is-invalid");
            errorDiv.textContent = "Este campo es obligatorio.";
            return false;
        }

        if (input.type === "email" && input.value.trim()) {
            const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!regexEmail.test(input.value.trim())) {
                input.classList.add("is-invalid");
                errorDiv.textContent = "Por favor, ingresá un correo electrónico válido.";
                return false;
            }
        }

        if (input.minLength > 0 && input.value.trim() && input.value.trim().length < input.minLength) {
            input.classList.add("is-invalid");
            errorDiv.textContent = `Debe tener al menos ${input.minLength} caracteres.`;
            return false;
        }

        input.classList.remove("is-invalid");
        errorDiv.textContent = "";
        return true;
    }

    function validarContrasenas() {
        let esValido = true;
        
        const errNueva = document.getElementById("error-passwordNueva");
        const errConf = document.getElementById("error-passwordConfirmar");

        // Si escribió algo en alguna de las contraseñas nuevas, se vuelven obligatorias para validar
        if (passNueva.value || passConfirmar.value) {
            if (passNueva.value.length < 8) {
                passNueva.classList.add("is-invalid");
                if (errNueva) errNueva.textContent = "Debe tener al menos 8 caracteres.";
                esValido = false;
            } else {
                passNueva.classList.remove("is-invalid");
                if (errNueva) errNueva.textContent = "";
            }

            if (passNueva.value !== passConfirmar.value) {
                passConfirmar.classList.add("is-invalid");
                if (errConf) errConf.textContent = "Las nuevas contraseñas no coinciden.";
                esValido = false;
            } else {
                passConfirmar.classList.remove("is-invalid");
                if (errConf) errConf.textContent = "";
            }
        } else {
            passNueva.classList.remove("is-invalid");
            passConfirmar.classList.remove("is-invalid");
            if (errNueva) errNueva.textContent = "";
            if (errConf) errConf.textContent = "";
        }

        return esValido;
    }

    campos.forEach(function(input) {
        input.addEventListener("blur", function() {
            validarCampo(input);
        });
        input.addEventListener("input", function() {
            if (input.classList.contains("is-invalid")) {
                validarCampo(input);
            }
        });
    });

    if (passNueva) {
        passNueva.addEventListener("input", validarContrasenas);
        passNueva.addEventListener("blur", validarContrasenas);
    }
    if (passConfirmar) {
        passConfirmar.addEventListener("input", validarContrasenas);
        passConfirmar.addEventListener("blur", validarContrasenas);
    }

    // Funcionalidad para los botones de ver/ocultar contraseña
    document.querySelectorAll(".toggle-password").forEach(button => {
        button.addEventListener("click", function() {
            const targetId = this.getAttribute("data-target");
            const inputField = document.getElementById(targetId);
            const icon = this.querySelector("i");
            const etiquetaBase = this.getAttribute("aria-label").replace(/^Mostrar /, "").replace(/^Ocultar /, "");

            if (inputField.type === "password") {
                inputField.type = "text";
                icon.classList.remove("bi-eye");
                icon.classList.add("bi-eye-slash");
                this.setAttribute("aria-pressed", "true");
                this.setAttribute("aria-label", "Ocultar " + etiquetaBase);
            } else {
                inputField.type = "password";
                icon.classList.remove("bi-eye-slash");
                icon.classList.add("bi-eye");
                this.setAttribute("aria-pressed", "false");
                this.setAttribute("aria-label", "Mostrar " + etiquetaBase);
            }
        });
    });

    form.addEventListener("submit", function(e) {
        let esValido = true;
        campos.forEach(function(input) {
            if (!validarCampo(input)) {
                esValido = false;
            }
        });

        if (!validarContrasenas()) {
            esValido = false;
        }

        if (!esValido) {
            e.preventDefault();
        }
    });
});
</script>
</body>
</html>