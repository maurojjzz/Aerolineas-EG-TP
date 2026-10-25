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
    <meta name="robots" content="noindex">
    <title>Error del servidor — Volaré</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <main class="container min-vh-100 d-flex align-items-center justify-content-center py-5">
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 text-center" style="max-width: 680px;">
            <p class="display-2 fw-bold text-primary mb-0" aria-hidden="true">500</p>
            <h1 class="h2 fw-bold mb-3">No pudimos completar la solicitud</h1>
            <p class="text-secondary mb-4">
                Ocurrió un problema inesperado. Intentá nuevamente en unos minutos.
                Si el problema continúa, podés volver al inicio o contactarnos.
            </p>
            <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
                <a href="<?= htmlspecialchars(url('index.php?pagina=inicio'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary">
                    Ir al inicio
                </a>
                <a href="<?= htmlspecialchars(url('index.php?pagina=contacto'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline-primary">
                    Contacto
                </a>
            </div>
        </div>
    </main>
</body>
</html>
