<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['usuario']);
$usuario = $isLoggedIn ? $_SESSION['usuario'] : null;
$rol = $usuario['rol'] ?? 'cliente';

// Definir la ruta del panel según el rol del usuario
$urlPanel = match($rol) {
    'admin' => url('index.php?pagina=aerolinea&seccion=listado'),
    'ceo'   => url('index.php?pagina=ceo'),
    default => url('index.php?pagina=cliente')
};

// Todos los roles apuntan a la misma vista unificada de perfil
$urlPerfil = url('index.php?pagina=perfil');

// Determinar la URL del botón "Inicio" dependiendo de si está logueado y su rol
if ($isLoggedIn && $rol === 'cliente') {
    $urlInicio = url('index.php?pagina=cliente');
} else {
    $urlInicio = url('index.php?pagina=inicio');
}

// Obtener la página actual para marcar el menú activo
$paginaActual = $_GET['pagina'] ?? 'inicio';
?>

<header class="container-xxl">
    <div class="fila_head row mt-3 d-flex align-items-center justify-content-between">

        <div class="col-2 d-flex d-md-none justify-content-center align-items-center">
            <?php require './src/views/components/menuMobile.php' ?>
        </div>
        
        <div class="col-1 d-flex justify-content-center align-items-center ">
            <a class="navbar-brand logoLink" href="<?= url('index.php?pagina=inicio') ?>">
                <img class="logo" src="<?= url('public/img/aerologo.webp') ?>" alt="Logo de Volaré">
            </a>
        </div>
        
        <div class="col d-none d-md-flex align-items-center justify-content-center">
                <ul class="nav navbar-custom d-flex overflow-hidden rounded-2">
                    <li class="nav-item <?= ($paginaActual === 'inicio' || $paginaActual === 'cliente') ? 'active' : '' ?>">
                        <a href="<?= $urlInicio ?>" class="nav-link">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link">Vuelos</a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link">Promociones</a>
                    </li>
                    <li class="nav-item <?= ($paginaActual === 'cliente-novedades' || $paginaActual === 'cliente-novedad-detalle') ? 'active' : '' ?>">
                        <a href="<?= url('index.php?pagina=cliente-novedades') ?>" class="nav-link">Novedades</a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= url('index.php?pagina=ayuda') ?>" class="nav-link">Ayuda</a>
                    </li>
                </ul>
        </div>

        <div class="col-1 d-flex justify-content-center align-items-center">

            <?php if ($isLoggedIn): ?>
                <!-- ESTADO LOGUEADO -->
                <div class="dropdown">
                    <button class="btn btn-custom dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="<?= url('public/img/icons/usuario.png') ?>" alt="Icono de perfil" class="icono_perfil">
                        <!-- Muestra el nombre en desktop y lo oculta en mobile si querés ahorrar espacio -->
                        <span class="d-none d-md-inline"><?= htmlspecialchars($usuario['nombre'] ?? 'Mi Cuenta') ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <?php if ($rol === 'admin' || $rol === 'ceo'): ?>
                            <li><a class="dropdown-item" href="<?= $urlPanel ?>">Mi Panel (<?= strtoupper($rol) ?>)</a></li>
                            <li><hr class="dropdown-divider"></li>
                        <?php endif; ?>
                        
                        <li><a class="dropdown-item" href="<?= url('index.php?pagina=perfil') ?>">Mi Perfil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= url('src/routes/usuarios.php?accion=logout') ?>">Cerrar Sesión</a></li>
                    </ul>
                </div>
            <?php else: ?>
                <!-- ESTADO NO LOGUEADO -->
                <a href="<?= url('index.php?pagina=login') ?>" class="btn btn-custom align-items-center gap-2">
                    <img src="<?= url('public/img/icons/usuario.png') ?>" alt="Icono de perfil" class="icono_perfil">
                    <span class="d-none d-md-inline">Ingresar</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>