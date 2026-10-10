<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar contraseña</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= url('public/css/layout/login.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/layout/header.css') ?>">
</head>
<body class="container-fluid overflow-x-hidden p-0 m-0 d-flex flex-column align-items-center">

    <?php require './src/views/layouts/header.php' ?>

    <section class="container-xxl main-section row p-0 m-0">
        <?php require __DIR__ . '/../components/alertToast.php'; ?>

        <div class="col-12 d-flex flex-column align-items-center justify-content-center pt-5">
            <form action="<?= url('src/routes/usuarios.php?accion=olvide-contrasena') ?>"
                  method="POST"
                  id="formOlvide"
                  class="formContainer rounded-3 shadow-lg p-4"
                  novalidate>

                <h3 class="fs-1 fw-semibold titleForm">Recuperar contraseña</h3>
                <p class="text-muted">Ingresá tu email y te enviaremos un link para restablecer tu contraseña.</p>

                <div class="form-group d-flex flex-column p-0">
                    <label class="form-label-t" for="email">Email:</label>
                    <input type="email" name="email" id="email" class="form-control ctm-inp" placeholder="tu@email.com" autocomplete="off">
                    <!-- Mantiene espacio constante de 1 línea mediante &nbsp; por defecto -->
                    <p id="infoEmail" class="form-text-info text-danger m-0 mt-1" style="font-size: 0.82rem; min-height: 1.25rem;">&nbsp;</p>
                </div>

                <button type="submit" class="btn btnLogin py-2 w-100 mt-2">
                    Enviar link
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
        const form = document.getElementById("formOlvide");
        const emailInput = document.getElementById("email");
        const infoEmail = document.getElementById("infoEmail");

        // Regex que exige dominio completo de al menos 2 letras al final (ej. .com, .ar)
        const emailRegex = /^[^\s@]+@[^\s@]+\.[a-zA-Z]{2,}$/;

        function validarEmail() {
            const value = emailInput.value.trim();

            if (value === "") {
                emailInput.classList.add("is-invalid");
                emailInput.classList.remove("is-valid");
                infoEmail.textContent = "Ingresá tu correo electrónico.";
                return false;
            }

            if (!emailRegex.test(value)) {
                emailInput.classList.add("is-invalid");
                emailInput.classList.remove("is-valid");
                infoEmail.textContent = "El formato de correo no es válido (ej. usuario@dominio.com).";
                return false;
            }

            emailInput.classList.remove("is-invalid");
            emailInput.classList.add("is-valid");
            // Usamos innerHTML con &nbsp; para evitar que la altura se colapse cuando está OK
            infoEmail.innerHTML = "&nbsp;";
            return true;
        }

        emailInput.addEventListener("input", validarEmail);
        emailInput.addEventListener("blur", validarEmail);

        form.addEventListener("submit", function (e) {
            if (!validarEmail()) {
                e.preventDefault();
                e.stopPropagation();
            }
        });
    });
    </script>
</body>
</html>