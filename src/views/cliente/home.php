<?php date_default_timezone_set('America/Argentina/Buenos_Aires');?>
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
            <h5 class="text-uppercase" >Vuela sin limites</h5>
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

        <!-- Reservas + Novedades hecho con claude por el momento hasta q sea implementado reservas y novedades -->
        <section class="container px-3 px-lg-5 mb-5">
            <div class="row g-4">

                <!-- Mis próximas reservas -->
                <div class="col-12 col-lg-8">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <img src="<?= url('public/img/icons/reserva.png') ?>" style="width:22px;height:22px;object-fit:contain;" alt="">
                            <h5 class="fw-bold m-0">Mis próximas reservas</h5>
                        </div>
                        <a href="#" class="text-primary text-decoration-none" style="font-size:0.875rem;">
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
                                    <a href="#" class="btn btn-outline-primary btn-sm" style="font-size:0.8rem;">Ver detalles</a>
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
                                    <a href="#" class="btn btn-primary btn-sm" style="font-size:0.8rem;">Continuar pago</a>
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
                        <a href="#" class="text-primary text-decoration-none" style="font-size:0.875rem;">
                            Ver todas las novedades →
                        </a>
                    </div>

                    <div class="d-flex flex-column gap-3">

                        <?php
                        $novedades = [
                            [
                                'img'    => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=80&h=80&fit=crop',
                                'titulo' => 'Nuevas rutas a destinos de ensueño',
                                'desc'   => 'Descubrí nuestras nuevas rutas a Cancún, Punta Cana y más destinos increíbles.',
                                'fecha'  => '18/05/2024',
                            ],
                            [
                                'img'    => 'https://images.unsplash.com/photo-1553531384-411a247ccd73?w=80&h=80&fit=crop',
                                'titulo' => 'Equipaje sin complicaciones',
                                'desc'   => 'Conocé nuestras nuevas políticas de equipaje más flexibles y beneficiosas.',
                                'fecha'  => '12/05/2024',
                            ],
                            [
                                'img'    => 'https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?w=80&h=80&fit=crop',
                                'titulo' => 'Check-in más fácil y rápido',
                                'desc'   => 'Ahorrá tiempo con el check-in online desde nuestra app.',
                                'fecha'  => '05/05/2024',
                            ],
                        ];
                        ?>

                        <?php foreach ($novedades as $nov): ?>
                        <div class="bg-white rounded-3 border p-3 d-flex gap-3 align-items-start">
                            <img src="<?= $nov['img'] ?>" alt="novedad"
                                 class="rounded-2 flex-shrink-0"
                                 style="width:70px;height:70px;object-fit:cover;">
                            <div class="flex-grow-1 min-width-0">
                                <p class="fw-semibold m-0" style="font-size:0.9rem;"><?= $nov['titulo'] ?></p>
                                <p class="text-muted m-0 mt-1" style="font-size:0.8rem;line-height:1.3;"><?= $nov['desc'] ?></p>
                                <p class="text-muted m-0 mt-1" style="font-size:0.75rem;"><?= $nov['fecha'] ?></p>
                            </div>
                            <span class="text-muted flex-shrink-0">›</span>
                        </div>
                        <?php endforeach; ?>

                    </div>
                </div>

            </div>
        </section>

        <?php require_once './src/views/layouts/footer.php'; ?>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
        <script src="<?= url('public/js/searchMobile.js') ?>"></script>
        <script src="<?= url('public/js/searchDesktop.js') ?>"></script>

    </body>

</html>