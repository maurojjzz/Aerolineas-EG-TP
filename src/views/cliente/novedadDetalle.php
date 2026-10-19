<?php
date_default_timezone_set('America/Argentina/Buenos_Aires');
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../controllers/NovedadController.php';

$idNovedad = $_GET['id'] ?? null;

if (!$idNovedad || !is_numeric($idNovedad)) {
    header('Location: ' . url('index.php?pagina=cliente-novedades'));
    exit;
}

$ctrl = new NovedadController($link);
$novedad = $ctrl->obtenerPorId($idNovedad);

if (!$novedad || $novedad['estado'] !== 'publicada') {
    header('Location: ' . url('index.php?pagina=cliente-novedades'));
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($novedad['titulo']) ?> - Novedades</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= url('public/css/layout/homeCliente.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/layout/header.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/components/menuMobile.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/bootstrap-icons.css') ?>">
</head>
<body class="container-fluid overflow-x-hidden p-0 m-0 bg-light">
    
    <!-- Header compartido -->
    <?php require __DIR__ . '/../layouts/header.php'; ?>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <!-- Botón volver -->
                <a href="<?= url('index.php?pagina=cliente-novedades') ?>" class="text-decoration-none d-inline-flex align-items-center gap-1 mb-4 text-muted">
                    <i class="bi bi-arrow-left"></i> Volver a novedades
                </a>

                <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                    <?php if (!empty($novedad['imagen'])): ?>
                        <!-- Reducimos la altura máxima a 200px para que no se deforme ni ocupe tanto espacio -->
                        <img src="<?= htmlspecialchars($novedad['imagen']) ?>" class="w-100" alt="Imagen de novedad" style="max-height: 200px; object-fit: cover;">
                    <?php endif; ?>

                    <div class="card-body p-4 p-md-5">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="badge text-bg-primary text-capitalize px-3 py-2"><?= htmlspecialchars($novedad['categoria']) ?></span>
                            <span class="badge text-bg-secondary px-3 py-2 text-capitalize">Prioridad: <?= htmlspecialchars($novedad['prioridad']) ?></span>
                            <small class="text-muted ms-auto"><i class="bi bi-calendar3 me-1"></i> <?= date('d/m/Y H:i', strtotime($novedad['fechaPublicacion'])) ?></small>
                        </div>

                        <h1 class="fw-bold mb-3 display-6"><?= htmlspecialchars($novedad['titulo']) ?></h1>

                        <?php if (!empty($novedad['subtitulo'])): ?>
                            <p class="text-muted fs-5 fw-medium mb-4 pb-2 border-bottom"><?= htmlspecialchars($novedad['subtitulo']) ?></p>
                        <?php endif; ?>

                        <div class="novedad-contenido text-secondary lh-lg" style="font-size: 1.05rem; white-space: pre-line;">
                            <?= htmlspecialchars($novedad['contenido']) ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>