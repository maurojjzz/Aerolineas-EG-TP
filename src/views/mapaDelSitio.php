<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['usuario']);
$rol        = strtolower(trim((string)($_SESSION['usuario']['rol'] ?? '')));
$esStaff    = in_array($rol, ['admin', 'ceo'], true);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mapa del sitio — Volaré</title>
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
            <div class="col-12 col-lg-9">

                <div class="card shadow-sm border-0 rounded-4 p-4 p-md-5 bg-white">

                    <div class="mb-4 pb-3 border-bottom">
                        <h1 class="fw-bold text-dark display-6 mb-2">Mapa del sitio</h1>
                        <p class="text-secondary m-0">Todas las secciones de Volaré, agrupadas por tipo de usuario.</p>
                    </div>

                    <div class="row g-4">

                        <section class="col-12 col-md-6" aria-labelledby="mapaPublico">
                            <h2 class="h5 fw-bold text-dark mb-3" id="mapaPublico">Acceso público</h2>
                            <ul class="list-unstyled">
                                <li class="mb-2"><a href="<?= url('index.php?pagina=inicio') ?>">Inicio</a></li>
                                <li class="mb-2"><a href="<?= url('index.php?pagina=buscar-vuelos') ?>">Buscar vuelos</a></li>
                                <li class="mb-2"><a href="<?= url('index.php?pagina=cliente-novedades&tab=promociones') ?>">Promociones</a></li>
                                <li class="mb-2"><a href="<?= url('index.php?pagina=cliente-novedades&tab=novedades') ?>">Novedades</a></li>
                                <li class="mb-2"><a href="<?= url('index.php?pagina=ayuda') ?>">Centro de ayuda</a></li>
                                <li class="mb-2"><a href="<?= url('index.php?pagina=contacto') ?>">Contacto</a></li>
                                <li class="mb-2"><a href="<?= url('index.php?pagina=sobre') ?>">Sobre Volaré</a></li>
                                <li class="mb-2"><a href="<?= url('index.php?pagina=terminos') ?>">Términos y Condiciones</a></li>
                                <li class="mb-2"><a href="<?= url('index.php?pagina=privacidad') ?>">Políticas de Privacidad</a></li>
                                <li class="mb-2"><a href="<?= url('index.php?pagina=mapa-del-sitio') ?>" aria-current="page">Mapa del sitio</a></li>
                            </ul>
                        </section>

                        <section class="col-12 col-md-6" aria-labelledby="mapaCuenta">
                            <h2 class="h5 fw-bold text-dark mb-3" id="mapaCuenta">Tu cuenta</h2>
                            <ul class="list-unstyled">
                                <?php if (!$isLoggedIn): ?>
                                    <li class="mb-2"><a href="<?= url('index.php?pagina=login') ?>">Iniciar sesión</a></li>
                                    <li class="mb-2"><a href="<?= url('index.php?pagina=registro') ?>">Registrarme como usuario</a></li>
                                    <li class="mb-2"><a href="<?= url('index.php?pagina=registro-ceo') ?>">Solicitar registro como CEO</a></li>
                                    <li class="mb-2"><a href="<?= url('index.php?pagina=olvide-contrasena') ?>">Recuperar contraseña</a></li>
                                <?php else: ?>
                                    <?php if ($rol === 'cliente'): ?>
                                        <li class="mb-2"><a href="<?= url('index.php?pagina=cliente') ?>">Mi panel</a></li>
                                        <li class="mb-2"><a href="<?= url('index.php?pagina=mis-reservas') ?>">Mis reservas</a></li>
                                        <li class="mb-2"><a href="<?= url('index.php?pagina=historial-compras') ?>">Historial de compras</a></li>
                                    <?php elseif ($rol === 'ceo'): ?>
                                        <li class="mb-2"><a href="<?= url('index.php?pagina=ceo') ?>">Panel de CEO</a></li>
                                        <li class="mb-2"><a href="<?= url('index.php?pagina=vuelos') ?>">Gestión de vuelos</a></li>
                                        <li class="mb-2"><a href="<?= url('index.php?pagina=promociones&seccion=listado') ?>">Gestión de promociones</a></li>
                                        <li class="mb-2"><a href="<?= url('index.php?pagina=ceo&seccion=reportes') ?>">Reportes</a></li>
                                    <?php elseif ($rol === 'admin'): ?>
                                        <li class="mb-2"><a href="<?= url('index.php?pagina=aerolinea&seccion=listado') ?>">Gestión de aerolíneas</a></li>
                                        <li class="mb-2"><a href="<?= url('index.php?pagina=promociones&seccion=listado') ?>">Gestión de promociones</a></li>
                                        <li class="mb-2"><a href="<?= url('index.php?pagina=novedades&seccion=listado') ?>">Gestión de novedades</a></li>
                                        <li class="mb-2"><a href="<?= url('index.php?pagina=usuarios&seccion=listado') ?>">Gestión de usuarios</a></li>
                                        <li class="mb-2"><a href="<?= url('index.php?pagina=ceos&seccion=listado') ?>">Gestión de CEOs</a></li>
                                        <li class="mb-2"><a href="<?= url('index.php?pagina=reportes') ?>">Reportes</a></li>
                                    <?php endif; ?>
                                    <li class="mb-2"><a href="<?= url('index.php?pagina=perfil') ?>">Mi perfil</a></li>
                                    <li class="mb-2"><a href="<?= url('src/routes/usuarios.php?accion=logout') ?>">Cerrar sesión</a></li>
                                <?php endif; ?>
                            </ul>
                        </section>

                    </div>

                    <div class="mt-5 pt-3 border-top text-center">
                        <a href="<?= url('index.php?pagina=inicio') ?>" class="btn btn-outline-primary px-4 fw-semibold">
                            <i class="bi bi-arrow-left me-2" aria-hidden="true"></i>Volver al Inicio
                        </a>
                    </div>

                </div>

            </div>
        </div>
    </main>

    <!-- Footer unificado -->
    <?php require __DIR__ . '/layouts/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
