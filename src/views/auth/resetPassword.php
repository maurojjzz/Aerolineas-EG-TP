<?php
require_once __DIR__ . '/../../config/conexion.php';

$token = trim($_GET['token'] ?? '');

// Si no hay token, redirige al login con mensaje de error
if (!$token) {
    flash_set('error', 'Link inválido o expirado.');
    redirect('index.php?pagina=login');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer contraseña</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= url('public/css/layout/login.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/layout/header.css') ?>">
</head>
<body class="container-fluid overflow-x-hidden p-0 m-0 d-flex flex-column align-items-center">

    <?php require './src/views/layouts/header.php' ?>

    <section class="container-xxl main-section row p-0 m-0">
        <?php require __DIR__ . '/../components/alertToast.php'; ?>

        <div class="col-12 d-flex flex-column align-items-center justify-content-center pt-5">
            <form action="<?= url('src/routes/usuarios.php?accion=reset-password') ?>"
                  method="POST"
                  id="formReset"
                  class="formContainer rounded-3 shadow-lg p-4"
                  novalidate>

                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                <h3 class="fs-1 fw-semibold titleForm">Restablecer contraseña</h3>
                <p class="text-muted">Ingresá tu nueva contraseña de al menos 8 caracteres.</p>

                <div class="form-group d-flex flex-column p-0 mb-1">
                    <label class="form-label-t" for="contrasena">Nueva contraseña:</label>
                    <input type="password" name="contrasena" id="contrasena" class="form-control ctm-inp" placeholder="Mínimo 8 caracteres" autocomplete="new-password">
                    <p id="infoPass" class="form-text-info text-danger m-0 mt-1" style="font-size: 0.82rem; min-height: 1.25rem;">&nbsp;</p>
                </div>

                <div class="form-group d-flex flex-column p-0 mb-1">
                    <label class="form-label-t" for="confirmar_contrasena">Confirmar contraseña:</label>
                    <input type="password" name="confirmar_contrasena" id="confirmar_contrasena" class="form-control ctm-inp" placeholder="Repetí tu nueva contraseña" autocomplete="new-password">
                    <p id="infoConfirm" class="form-text-info text-danger m-0 mt-1" style="font-size: 0.82rem; min-height: 1.25rem;">&nbsp;</p>
                </div>

                <button type="submit" class="btn btnLogin py-2 w-100 mt-2">
                    Guardar contraseña
                </button>

                <a href="<?= url('index.php?pagina=login') ?>" class="d-block text-decoration-none text-center fs-6 mt-4">
                    <span class="text-dark">Volver al</span> inicio de sesión
                </a>
            </form>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const form = document.getElementById("formReset");
        const passInput = document.getElementById("contrasena");
        const confirmInput = document.getElementById("confirmar_contrasena");

        const infoPass = document.getElementById("infoPass");
        const infoConfirm = document.getElementById("infoConfirm");

        function validarPass() {
            const val = passInput.value;
            if (val === "") {
                passInput.classList.add("is-invalid");
                passInput.classList.remove("is-valid");
                infoPass.textContent = "Ingresá una nueva contraseña.";
                return false;
            }
            if (val.length < 8) {
                passInput.classList.add("is-invalid");
                passInput.classList.remove("is-valid");
                infoPass.textContent = "La contraseña debe contener al menos 8 caracteres.";
                return false;
            }
            passInput.classList.remove("is-invalid");
            passInput.classList.add("is-valid");
            infoPass.innerHTML = "&nbsp;";
            return true;
        }

        function validarConfirmacion() {
            const valPass = passInput.value;
            const valConfirm = confirmInput.value;

            if (valConfirm === "") {
                confirmInput.classList.add("is-invalid");
                confirmInput.classList.remove("is-valid");
                infoConfirm.textContent = "Repetí la nueva contraseña.";
                return false;
            }

            if (valPass !== valConfirm) {
                confirmInput.classList.add("is-invalid");
                confirmInput.classList.remove("is-valid");
                infoConfirm.textContent = "Las contraseñas no coinciden.";
                return false;
            }

            confirmInput.classList.remove("is-invalid");
            confirmInput.classList.add("is-valid");
            infoConfirm.innerHTML = "&nbsp;";
            return true;
        }

        passInput.addEventListener("input", function() {
            validarPass();
            if (confirmInput.value !== "") validarConfirmacion();
        });

        confirmInput.addEventListener("input", validarConfirmacion);

        form.addEventListener("submit", function (e) {
            const v1 = validarPass();
            const v2 = validarConfirmacion();

            if (!v1 || !v2) {
                e.preventDefault();
                e.stopPropagation();
            }
        });
    });
    </script>
</body>
</html>