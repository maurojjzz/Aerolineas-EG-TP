<!DOCTYPE html>
<html lang="es">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Iniciar Sesión</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">


        <!-- <link rel="stylesheet" href="<?= url('public/css/layout/login.css') ?>"> -->
        <link rel="stylesheet" href="<?= url('public/css/layout/login.css') ?>?v=<?= time() ?>">
        <link rel="stylesheet" href="<?= url('public/css/layout/header.css') ?>">
        <link rel="stylesheet" href="<?= url('public/css/components/menuMobile.css') ?>">

    </head>

    <body class="container-fluid overflow-x-hidden p-0 m-0 d-flex flex-column align-items-center"> 
        <?php require './src/views/layouts/header.php' ?>
        
        <section class="container-xxl main-section row p-0 m-0">
            <div class="d-none d-lg-flex col-lg-5  mt-5 pt-3  h-100">
                
                <div class="hero-content text-left mt-5 user-select-none">
                    <h5 class="fw-bold">Vuela sin limites</h5>
                    <h2 >DONDE CADA <span>VIAJE</span> COMIENZA</h2>
                    <p>Conectamos personas y destinos con una experiencia rapida, segura y confiable.</p>
                </div>
            </div>

            <div class="col-12 col-lg-7 d-flex flex-column align-items-center align-items-lg-end justify-content-center ">
                <form action="" class="formContainer  rounded-3 shadow-lg p-4 me-lg-5 " >
                    <h3 class="fs-1 fw-semibold titleForm">Iniciar Sesión</h3>
                    <p class="text-muted text-break">Accede a tu cuenta para gestionar reservas, vuelos o promociones</p>
                    
                    <div class="form-group d-flex flex-column p-0">
                        <label class="form-label-t" for="email">Email:</label>
                        <input type="email" name="email" id="email" class="form-control ctm-inp" required placeholder="aeroflux@email.com">
                        <p id="infoEmail" class=" form-text-info"></p>
                    </div>
                    
                    <div class="form-group d-flex flex-column ">
                        <label class="form-label-t" for="password">Contraseña:</label>
                        <input type="password" name="password" id="password" class="form-control ctm-inp" required placeholder="********">
                        <p id="infoContrasena" class="form-text-info"></p>
                    </div>

                    <a href="#" class="d-block text-decoration-none text-end" >¿Olvidaste tu contraseña?</a>

                    <button type="submit" class="btn btnLogin py-2 w-100 d-flex align-items-center justify-content-center gap-2 mt-3">
                        <img src=" <?= url('public/img/icons/usuario.png') ?>" alt="Icono de crear usuario" class="iconoBtnLgn">
                        <span class="fs-5 fw-medium">Ingresar</span>
                    </button>

                    <hr class="mt-4">

                    <div class=" d-flex flex-column flex-sm-row justify-content-center justify-content-md-between align-items-center gap-4 m-0 p-0 ">
                        <button type="submit" class="btn btn-outline-primary gap-1 BtnRegisterOnLogin d-flex align-items-center justify-content-center">
                            <img src=" <?= url('public/img/icons/usuario.png') ?>" alt="Icono usuario" class="iconoBtnRegister">
                            <span class="fs-6 fw-medium">Registro usuario</span>
                        </button>

                        <button type="submit" class="btn btn-outline-primary gap-1 BtnRegisterOnLogin d-flex align-items-center justify-content-center ">
                            <img src=" <?= url('public/img/icons/administracion-de-empresas.png') ?>" alt="Icono de registrar ceo" class="iconoBtnRegisterCEO">
                            <span class="fs-6 fw-medium">Registro CEO</span>
                        </button>
                    </div>

                    <p class="text-muted text-break text-center mt-4 fs-6">Los registros de CEO requieren aprobacion del administrador.</p>
                    
                </form>
            </div>
        </section>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

    </body>

</html>