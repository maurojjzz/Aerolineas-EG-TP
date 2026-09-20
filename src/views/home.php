<?php date_default_timezone_set('America/Argentina/Buenos_Aires');?>
<!DOCTYPE html>
<html lang="es">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Aerolinea</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

        <link rel="stylesheet" href="<?= url('public/css/index.css') ?>">
        <link rel="stylesheet" href="<?= url('public/css/layout/header.css') ?>">
        <link rel="stylesheet" href="<?= url('public/css/components/menuMobile.css') ?>">
        <link rel="stylesheet" href="<?= url('public/css/components/searchMobile.css') ?>">
        <link rel="stylesheet" href="<?= url('public/css/components/searchDesktop.css') ?>">

    </head>

    <body class="container-fluid overflow-x-hidden p-0 m-0">
        <?php require './src/views/layouts/header.php' ?>
        
        <img src=" <?= url('public/img/clouds.png') ?>" class="nubes " alt="fondo de nubes">

        <section class="container-xxl custom-main">
            
            <div class="hero-content text-center mt-3 mt-sm-4 mt-md-5 user-select-none">
                <h5 >Vuela sin limites</h5>
                <h2 >DONDE CADA <span>VIAJE</span> COMIENZA</h2>
                <p>Conectamos personas y destinos con una experiencia rapida, segura y confiable.</p>
            </div>

            <img src="<?= url('public/img/airplane.png') ?>" alt="Imagen avion" class="hero-plane user-select-none">
        </section>


        <!-- Mobile -->
        <section class="searchbox d-lg-none px-3 mb-5">
            <?php require './src/views/components/searchMobile.php' ?>
        </section>

        <!-- Desktop -->
        <section class="searchbox-desktop d-none d-lg-block mb-5">
            <?php require './src/views/components/searchDesktop.php' ?>
        </section>


        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
        <script src="<?= url('public/js/searchMobile.js') ?>"></script>
        <script src="<?= url('public/js/searchDesktop.js') ?>"></script>

    </body>

</html>