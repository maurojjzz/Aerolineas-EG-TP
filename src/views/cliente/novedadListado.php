<?php
date_default_timezone_set('America/Argentina/Buenos_Aires');
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../controllers/NovedadController.php';
require_once __DIR__ . '/../../controllers/PromocionController.php';

$novedadCtrl = new NovedadController($link);
$novedades = $novedadCtrl->listarTodasPublicadas();

$promocionCtrl = new PromocionController($link);
$promociones = $promocionCtrl->listarTodasPublicadas();

// Determinar la pestaña activa ('novedades' o 'promociones')
$tabActual = $_GET['tab'] ?? 'novedades';
if (!in_array($tabActual, ['novedades', 'promociones'], true)) {
    $tabActual = 'novedades';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novedades y Promociones — Volaré</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= url('public/css/layout/homeCliente.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/layout/header.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/components/menuMobile.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/bootstrap-icons.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/layout/reservas.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/layout/misReservas.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/accesibilidad.css') ?>">
</head>
<body class="container-fluid overflow-x-hidden p-0 m-0 bg-light">
    
    <!-- Header principal compartido del cliente -->
    <?php require __DIR__ . '/../layouts/header.php'; ?>

    <main id="contenido" tabindex="-1" class="container pb-5">

        <div class="d-flex justify-content-start pt-3">
            <a href="<?= url('index.php?pagina=inicio') ?>" class="text-decoration-none d-inline-flex align-items-center gap-1 text-muted">
                <i class="bi bi-arrow-left"></i> Volver al inicio
            </a>
        </div>

        <div class="titulo-cuadro text-center mb-4">
            <h1 >NOVEDADES Y <span>PROMOCIONES</span> </h1>
        </div>

        <!-- Pestañas para alternar entre Novedades y Promociones -->
        <ul class="nav nav-pills justify-content-center mb-4 gap-2">
            <li class="nav-item">
                <a class="nav-link <?= $tabActual === 'novedades' ? 'active fw-bold' : 'bg-white text-dark border' ?>" 
                   <?= $tabActual === 'novedades' ? 'aria-current="page"' : '' ?>
                   href="<?= url('index.php?pagina=cliente-novedades&tab=novedades') ?>">
                   📢 Novedades
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $tabActual === 'promociones' ? 'active fw-bold' : 'bg-white text-dark border' ?>" 
                   <?= $tabActual === 'promociones' ? 'aria-current="page"' : '' ?>
                   href="<?= url('index.php?pagina=cliente-novedades&tab=promociones') ?>">
                   🏷️ Promociones
                </a>
            </li>
        </ul>

        <!-- CONTENIDO: NOVEDADES -->
        <?php if ($tabActual === 'novedades'): ?>
            <div class="row g-4">
                <?php if (empty($novedades)): ?>
                    <div class="col-12 text-center">
                        <div class="alert alert-info d-inline-block px-4">No hay novedades disponibles en este momento.</div>
                    </div>
                <?php else: ?>
                    <?php foreach ($novedades as $n): ?>
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm h-100 rounded-3">
                                <?php if (!empty($n['imagen'])): ?>
                                    <img src="<?= htmlspecialchars($n['imagen']) ?>" class="card-img-top rounded-top-3" alt="Imagen de la novedad: <?= htmlspecialchars($n['titulo']) ?>" style="height: 180px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="card-img-top rounded-top-3 bg-primary text-white d-flex align-items-center justify-content-center" style="height: 180px;">
                                        <i class="bi bi-megaphone fs-1"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="card-body d-flex flex-column">
                                    <div class="mb-2">
                                        <span class="badge text-bg-primary text-capitalize"><?= htmlspecialchars($n['categoria']) ?></span>
                                        <small class="text-muted ms-2"><?= date('d/m/Y', strtotime($n['fechaPublicacion'])) ?></small>
                                    </div>
                                    <h2 class="card-title fw-bold h5"><?= htmlspecialchars($n['titulo']) ?></h2>
                                    <p class="card-text text-muted small flex-grow-1"><?= htmlspecialchars($n['subtitulo'] ?? '') ?></p>
                                    <a href="<?= url('index.php?pagina=cliente-novedad-detalle&id=' . $n['idNovedad']) ?>" class="btn btn-outline-primary btn-sm mt-3 align-self-start">Leer más</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        <!-- CONTENIDO: PROMOCIONES -->
        <!-- CONTENIDO: PROMOCIONES -->
        <?php else: ?>
            <div class="row g-4">
                <?php if (empty($promociones)): ?>
                    <div class="col-12 text-center">
                        <div class="alert alert-info d-inline-block px-4">No hay promociones activas en este momento.</div>
                    </div>
                <?php else: ?>
                    <?php foreach ($promociones as $p): 
                        $porcentaje = $p['descuentoPorcentaje'] ? round($p['descuentoPorcentaje']) : 0;
                        $fInicio = !empty($p['fechaInicio']) ? date('d/m/Y', strtotime($p['fechaInicio'])) : '';
                        $fFin = !empty($p['fechaFin']) ? date('d/m/Y', strtotime($p['fechaFin'])) : '';
                    ?>
                        <div class="col-12">
                            <div class="bg-white rounded-3 border p-3 shadow-sm" style="border-left: 5px solid #048d42 !important;">
                                
                                <!-- ================= VISTA DESKTOP (md en adelante) ================= -->
                                <div class="d-none d-md-flex align-items-center justify-content-between gap-4">
                                    <!-- 1. Descuento -->
                                    <div class="rounded-3 text-white d-flex flex-column align-items-center justify-content-center text-center p-2 flex-shrink-0" style="min-width: 90px; min-height: 75px; background-color: #048d42;">
                                        <span class="fw-bold" style="font-size: 1.7rem; line-height: 1.1;"><?= $porcentaje > 0 ? $porcentaje . '%' : 'PROMO' ?></span>
                                        <span class="text-uppercase fw-semibold" style="font-size: 0.65rem; letter-spacing: 1px; opacity: 0.9;">OFF</span>
                                    </div>

                                    <!-- 2. Título y Código -->
                                    <div class="flex-grow-1" style="min-width: 0;">
                                        <h2 class="fw-bold text-dark m-0 text-truncate" style="font-size: 1.3rem;"><?= htmlspecialchars($p['nombre']) ?></h2>
                                        <div class="d-flex align-items-center gap-2 mt-1">
                                            <span class="badge bg-light text-secondary border fw-semibold" style="font-size: 0.7rem; letter-spacing: 0.5px;"><?= htmlspecialchars($p['codigo']) ?></span>
                                        </div>
                                        <?php if (!empty($p['descripcion'])): ?>
                                            <p class="text-muted small m-0 text-truncate mt-1"><?= htmlspecialchars($p['descripcion']) ?></p>
                                        <?php endif; ?>
                                    </div>

                                    <!-- 3. Aerolínea -->
                                    <div class="px-2" style="min-width: 150px;">
                                        <span class="small text-muted">Aerolínea:</span><br>
                                        <strong class="text-dark"><?= htmlspecialchars($p['nombreAerolinea'] ?? 'Aerolínea') ?></strong>
                                    </div>

                                    <!-- 4. Fechas -->
                                    <div class="d-flex flex-column text-start px-3 border-start text-nowrap" style="min-width: 180px;">
                                        <div class="d-flex align-items-center gap-1 text-muted" style="font-size: 0.95rem;">
                                            <i class="bi bi-calendar-check text-success"></i>
                                            <span>Del <strong><?= $fInicio ?></strong> al <strong><?= $fFin ?></strong></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- ================= VISTA MOBILE (menor a md) ================= -->
                                <div class="d-flex d-md-none flex-column gap-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <!-- Descuento compacto -->
                                        <div class="rounded-3 text-white d-flex flex-column align-items-center justify-content-center text-center p-2 flex-shrink-0" style="min-width: 75px; min-height: 65px; background-color: #048d42;">
                                            <span class="fw-bold" style="font-size: 1.3rem; line-height: 1.1;"><?= $porcentaje > 0 ? $porcentaje . '%' : 'PROMO' ?></span>
                                            <span class="text-uppercase fw-semibold" style="font-size: 0.55rem; letter-spacing: 1px; opacity: 0.9;">OFF</span>
                                        </div>
                                        <!-- Título y Código -->
                                        <div class="flex-grow-1" style="min-width: 0;">
                                            <h2 class="fw-bold text-dark m-0 text-truncate" style="font-size: 1.1rem;"><?= htmlspecialchars($p['nombre']) ?></h2>
                                            <span class="badge bg-light text-secondary border fw-semibold mt-1" style="font-size: 0.65rem;"><?= htmlspecialchars($p['codigo']) ?></span>
                                        </div>
                                    </div>

                                    <?php if (!empty($p['descripcion'])): ?>
                                        <p class="text-muted small m-0"><?= htmlspecialchars($p['descripcion']) ?></p>
                                    <?php endif; ?>

                                    <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-1 small text-muted">
                                        <span>Aerolínea: <strong class="text-dark"><?= htmlspecialchars($p['nombreAerolinea'] ?? 'Aerolínea') ?></strong></span>
                                        <div class="d-flex align-items-center gap-1">
                                            <i class="bi bi-calendar-check text-success"></i>
                                            <span><?= $fInicio ?> - <?= $fFin ?></span>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </main>

    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>