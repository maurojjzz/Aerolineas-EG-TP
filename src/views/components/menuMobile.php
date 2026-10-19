<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['usuario']);
$usuario = $isLoggedIn ? $_SESSION['usuario'] : null;
$rol = $usuario['rol'] ?? 'cliente';

// Definir la ruta del panel según el rol
$urlPanel = match($rol) {
    'admin' => url('index.php?pagina=aerolinea&seccion=listado'),
    'ceo'   => url('index.php?pagina=ceo'),
    default => url('index.php?pagina=cliente')
};

// Determinar la URL de Inicio
if ($isLoggedIn && $rol === 'cliente') {
    $urlInicio = url('index.php?pagina=cliente');
} else {
    $urlInicio = url('index.php?pagina=inicio');
}

$paginaActual = $_GET['pagina'] ?? 'inicio';
?>
<nav class="navbar">
    <button class="navbar-toggler custom-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
        <div class="offcanvas-header">
<<<<<<< HEAD
            <a class="navbar-brand logoLink" href="<?= $urlInicio ?>">
                <img class="logo" src="<?= url('public/img/aerologo.webp') ?>" alt="Logo de la pagina" >
=======
            <a class="navbar-brand logoLink" href="#">
                <img class="logo" src="<?= url('public/img/aerologo.webp') ?>" alt="Logo de Volaré" >
>>>>>>> master
            </a>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>

        <div class="offcanvas-body">
            <ul class="navbar-nav justify-content-end flex-grow-1 pe-3">
                <li class="nav-item">
                    <a class="nav-link text-white <?= ($paginaActual === 'inicio' || $paginaActual === 'cliente') ? 'active fw-bold' : '' ?>" href="<?= $urlInicio ?>">Inicio</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="#">Vuelos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="#">Promociones</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white <?= ($paginaActual === 'cliente-novedades' || $paginaActual === 'cliente-novedad-detalle') ? 'active fw-bold' : '' ?>" href="<?= url('index.php?pagina=cliente-novedades') ?>">Novedades</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="#">Ayuda</a>
                </li>
                
                <li class="nav-item mt-5">
                    <?php if ($isLoggedIn): ?>
                        <a class="nav-link text-white fw-semibold" href="<?= $urlPanel ?>">Mi Panel (<?= strtoupper($rol) ?>)</a>
                        <a class="nav-link text-danger mt-2" href="/src/routes/usuarios.php?accion=logout">Cerrar Sesión</a>
                    <?php else: ?>
                        <a class="nav-link text-white" href="<?= url('index.php?pagina=login') ?>">Iniciar sesión</a>
                    <?php endif; ?>
                </li>
            </ul>
        </div>
    </div>
</nav>