<?php
date_default_timezone_set('America/Argentina/Buenos_Aires');
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../controllers/NovedadController.php';

$ctrl = new NovedadController($link);
$novedades = $ctrl->listarTodasPublicadas();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novedades y Promociones - Vuela sin límites</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= url('public/css/layout/homeCliente.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/layout/header.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/components/menuMobile.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/bootstrap-icons.css') ?>">
</head>
<body class="container-fluid overflow-x-hidden p-0 m-0 bg-light">
    
    <!-- Header principal compartido del cliente -->
    <?php require __DIR__ . '/../layouts/header.php'; ?>

    <div class="container py-5">
        <h2 class="fw-bold mb-4">Novedades y Promociones</h2>
        
        <div class="row g-4">
            <?php if (empty($novedades)): ?>
                <div class="col-12">
                    <div class="alert alert-info">No hay novedades disponibles en este momento.</div>
                </div>
            <?php else: ?>
                <?php foreach ($novedades as $n): ?>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100 rounded-3">
                            <?php if (!empty($n['imagen'])): ?>
                                <img src="<?= htmlspecialchars($n['imagen']) ?>" class="card-img-top rounded-top-3" alt="imagen" style="height: 180px; object-fit: cover;">
                            <?php else: ?>
                                <div class="card-img-top rounded-top-3 bg-light d-flex align-items-center justify-content-center text-primary" style="height: 180px;">
                                    <i class="bi bi-megaphone fs-1"></i>
                                </div>
                            <?php endif; ?>
                            <div class="card-body d-flex flex-column">
                                <div class="mb-2">
                                    <span class="badge text-bg-primary text-capitalize"><?= htmlspecialchars($n['categoria']) ?></span>
                                    <small class="text-muted ms-2"><?= date('d/m/Y', strtotime($n['fechaPublicacion'])) ?></small>
                                </div>
                                <h5 class="card-title fw-bold"><?= htmlspecialchars($n['titulo']) ?></h5>
                                <p class="card-text text-muted small flex-grow-1"><?= htmlspecialchars($n['subtitulo'] ?? '') ?></p>
                                <a href="<?= url('index.php?pagina=cliente-novedad-detalle&id=' . $n['idNovedad']) ?>" class="btn btn-outline-primary btn-sm mt-3 align-self-start">Leer más</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>