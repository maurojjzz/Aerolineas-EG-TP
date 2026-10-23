<?php 
date_default_timezone_set('America/Argentina/Buenos_Aires');
require_once __DIR__ . '/../../controllers/NovedadController.php';
require_once __DIR__ . '/../../config/conexion.php';

$novedadCtrl = new NovedadController($link);
$novedades = $novedadCtrl->obtenerUltimasPublicadas(3); // Obtiene las últimas 3 publicadas
?>
<!DOCTYPE html>
<html lang="es">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Cliente Home</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

        <link rel="stylesheet" href="<?= url('public/css/layout/homeCliente.css') ?>">
        <link rel="stylesheet" href="<?= url('public/css/layout/header.css') ?>">
        <link rel="stylesheet" href="<?= url('public/css/components/menuMobile.css') ?>">
        <link rel="stylesheet" href="<?= url('public/css/components/searchMobile.css') ?>">
        <link rel="stylesheet" href="<?= url('public/css/components/searchDesktop.css') ?>">
        <link rel="stylesheet" href="<?= url('public/css/bootstrap-icons.css') ?>">

    </head>

    <body class="container-fluid overflow-x-hidden p-0 m-0">
        <?php require './src/views/layouts/header.php' ?>

        <div class="hero-content text-center mt-4 user-select-none">
            <h5 class="text-uppercase" >Volaré</h5>
            <h2 >DONDE CADA <span>VIAJE</span> COMIENZA</h2>                
            <p>Busca vuelos, gestiona tus reservas y descubre destinos increibles.</p>
        </div>
        <!-- Mobile -->
        <section class="searchbox d-lg-none px-3 mb-5">
            <?php require './src/views/components/searchMobile.php' ?>
        </section>

        <!-- Desktop -->
        <section class="searchbox-desktop d-none d-lg-block mb-5">
            <?php require './src/views/components/searchDesktop.php' ?>
        </section>

        <!-- Reservas + Novedades -->
        <section class="container px-3 px-lg-5 mb-5">
            <div class="row g-4">

                <!-- Mis próximas reservas -->
                <div class="col-12 col-lg-8">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <img src="<?= url('public/img/icons/reserva.png') ?>" style="width:30px;height:30px;object-fit:contain;" alt="">
                            <h5 class="fw-bold m-0">Mis próximas reservas</h5>
                        </div>
                        <a href="<?= url('index.php?pagina=mis-reservas') ?>" class="text-primary text-decoration-none" style="font-size:0.875rem;">
                            Ver todas mis reservas →
                        </a>
                    </div>

                    <div class="row g-3">

                        <!-- Reserva 1 -->
                        <div class="col-12 col-md-6">
                            <div class="bg-white rounded-3 border p-3 h-100">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <p class="text-muted m-0" style="font-size:0.75rem;">Código de reserva</p>
                                        <p class="fw-bold fs-5 m-0">VSL8X7</p>
                                    </div>
                                    <span class="badge text-bg-success px-2 py-1" style="font-size:0.75rem;">✓ Confirmada</span>
                                </div>

                                <div class="d-flex align-items-center justify-content-between my-3">
                                    <div>
                                        <p class="fw-semibold m-0">Bogotá (BOG)</p>
                                        <p class="text-muted m-0" style="font-size:0.8rem;">15 Jun 2024 • 08:45</p>
                                    </div>
                                    <span class="text-primary fs-5">✈</span>
                                    <div class="text-end">
                                        <p class="fw-semibold m-0">Madrid (MAD)</p>
                                        <p class="text-muted m-0" style="font-size:0.8rem;">15 Jun 2024 • 23:35</p>
                                    </div>
                                </div>

                                <hr class="my-2">

                                <div class="d-flex justify-content-between align-items-center">
                                    <p class="text-muted m-0" style="font-size:0.8rem;">1 pasajero &nbsp;|&nbsp; Económica &nbsp;|&nbsp; Ida</p>
                                    <a href="<?= url('index.php?pagina=mis-reservas') ?>" class="btn btn-outline-primary btn-sm" style="font-size:0.8rem;">Ver detalles</a>
                                </div>
                            </div>
                        </div>

                        <!-- Reserva 2 -->
                        <div class="col-12 col-md-6">
                            <div class="bg-white rounded-3 border p-3 h-100">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <p class="text-muted m-0" style="font-size:0.75rem;">Código de reserva</p>
                                        <p class="fw-bold fs-5 m-0">K9Z2Q1</p>
                                    </div>
                                    <span class="badge bg-warning text-dark px-2 py-1" style="font-size:0.75rem;">⏳ Pendiente de pago</span>
                                </div>

                                <div class="d-flex align-items-center justify-content-between my-3">
                                    <div>
                                        <p class="fw-semibold m-0">Medellín (MDE)</p>
                                        <p class="text-muted m-0" style="font-size:0.8rem;">02 Jul 2024 • 11:20</p>
                                    </div>
                                    <span class="text-primary fs-5">✈</span>
                                    <div class="text-end">
                                        <p class="fw-semibold m-0">Cancún (CUN)</p>
                                        <p class="text-muted m-0" style="font-size:0.8rem;">02 Jul 2024 • 16:30</p>
                                    </div>
                                </div>

                                <hr class="my-2">

                                <div class="d-flex justify-content-between align-items-center">
                                    <p class="text-muted m-0" style="font-size:0.8rem;">2 pasajeros &nbsp;|&nbsp; Económica &nbsp;|&nbsp; Ida y vuelta</p>
                                    <a href="<?= url('index.php?pagina=mis-reservas') ?>" class="btn btn-primary btn-sm" style="font-size:0.8rem;">Continuar pago</a>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Novedades -->
                <div class="col-12 col-lg-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-primary fs-5">📢</span>
                            <h5 class="fw-bold m-0">Novedades</h5>
                        </div>
                        <a href="<?= url('index.php?pagina=cliente-novedades') ?>" class="text-primary text-decoration-none" style="font-size:0.875rem;">
                            Ver todas las novedades →
                        </a>
                    </div>

                    <div class="d-flex flex-column gap-3">
                        <?php if (empty($novedades)): ?>
                            <p class="text-muted small">No hay novedades registradas.</p>
                        <?php else: ?>
                            <?php foreach ($novedades as $nov): ?>
                            <div class="bg-white rounded-3 border p-3 d-flex gap-3 align-items-start" style="cursor:pointer;" onclick="window.location.href='<?= url('index.php?pagina=cliente-novedad-detalle&id=' . $nov['idNovedad']) ?>'">
                                <?php if (!empty($nov['imagen'])): ?>
                                    <img src="<?= htmlspecialchars($nov['imagen']) ?>" alt="novedad" class="rounded-2 flex-shrink-0" style="width:70px;height:70px;object-fit:cover;">
                                <?php else: ?>
                                    <div class="rounded-2 bg-light d-flex align-items-center justify-content-center text-primary flex-shrink-0" style="width:70px;height:70px;">
                                        <i class="bi bi-megaphone fs-4"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="flex-grow-1" style="min-width: 0;">
                                    <p class="fw-semibold m-0 text-truncate" style="font-size:0.9rem;"><?= htmlspecialchars($nov['titulo']) ?></p>
                                    <p class="text-muted m-0 mt-1 text-truncate" style="font-size:0.8rem;line-height:1.3;"><?= htmlspecialchars($nov['subtitulo'] ?? $nov['contenido']) ?></p>
                                    <p class="text-muted m-0 mt-1" style="font-size:0.75rem;"><?= date('d/m/Y', strtotime($nov['fechaPublicacion'])) ?></p>
                                </div>
                                <span class="text-muted flex-shrink-0">›</span>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </section>

        <?php require_once './src/views/layouts/footer.php'; ?>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
        <script src="<?= url('public/js/aeropuertos.js') ?>"></script>
        <script src="<?= url('public/js/searchMobile.js') ?>"></script>
        <script src="<?= url('public/js/searchDesktop.js') ?>"></script>

    </body>

</html>