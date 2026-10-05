<?php
$token = trim($_GET['token'] ?? '');

// Si no hay token, no tiene sentido mostrar el form
if (!$token) {
    flash_set('error', 'Link inválido.');
    redirect('index.php?pagina=login');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva contraseña</title>
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
                    class="formContainer rounded-3 shadow-lg p-4">

                <h3 class="fs-1 fw-semibold titleForm">Nueva contraseña</h3>
                <p class="text-muted">Elegí una contraseña nueva para tu cuenta.</p>

                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                <div class="form-group d-flex flex-column p-0">
                    <label class="form-label-t" for="contrasena">Nueva contraseña:</label>
                    <input type="password" name="contrasena" id="contrasena" class="form-control ctm-inp" required placeholder="********" minlength="8">
                </div>

                <div class="form-group d-flex flex-column mt-2">
                    <label class="form-label-t" for="confirmar_contrasena">Confirmar contraseña:</label>
                    <input type="password" name="confirmar_contrasena" id="confirmar_contrasena" class="form-control ctm-inp" required placeholder="********" minlength="8">
                </div>

                <button type="submit" class="btn btnLogin py-2 w-100 mt-3">
                    Cambiar contraseña
                </button>
            </form>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>