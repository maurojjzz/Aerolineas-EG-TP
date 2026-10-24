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
    <title>Página no encontrada — Volaré</title>
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
            <div class="col-12 col-lg-8">

                <div class="card shadow-sm border-0 rounded-4 p-4 p-md-5 bg-white text-center">

                    <p class="display-1 fw-bold text-primary mb-0" aria-hidden="true">404</p>
                    <h1 class="fw-bold text-dark display-6 mb-3">No encontramos esta página</h1>

                    <p class="text-secondary mb-4">
                        El enlace puede estar roto o la página pudo haberse movido. Podés volver al inicio,
                        buscar un vuelo o escribirnos si necesitás ayuda.
                    </p>

                    <div class="d-flex flex-column flex-sm-row justify-content-center gap-2 mb-4">
                        <a href="<?= url('index.php?pagina=inicio') ?>" class="btn btn-primary px-4 fw-semibold">
                            <i class="bi bi-house-door me-2" aria-hidden="true"></i>Ir al inicio
                        </a>
                        <a href="<?= url('index.php?pagina=buscar-vuelos') ?>" class="btn btn-outline-primary px-4 fw-semibold">
                            <i class="bi bi-airplane me-2" aria-hidden="true"></i>Buscar vuelos
                        </a>
                    </div>

                    <nav aria-label="Enlaces útiles">
                        <p class="fw-semibold text-dark mb-2">También te puede servir</p>
                        <ul class="list-inline mb-0">
                            <li class="list-inline-item mb-2"><a href="<?= url('index.php?pagina=ayuda') ?>">Centro de ayuda</a></li>
                            <li class="list-inline-item mb-2">·</li>
                            <li class="list-inline-item mb-2"><a href="<?= url('index.php?pagina=contacto') ?>">Contacto</a></li>
                            <li class="list-inline-item mb-2">·</li>
                            <li class="list-inline-item mb-2"><a href="<?= url('index.php?pagina=mapa-del-sitio') ?>">Mapa del sitio</a></li>
                        </ul>
                    </nav>

                </div>

            </div>
        </div>
    </main>

    <!-- Footer unificado -->
    <?php require __DIR__ . '/layouts/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
