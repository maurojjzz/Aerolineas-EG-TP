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
?>

<header class="container-xxl">
    <div class="fila_head row mt-3 d-flex align-items-center justify-content-between">

        <div class="col-2 d-flex d-md-none justify-content-center align-items-center">
            <?php require './src/views/components/menuMobile.php' ?>
        </div>
        
        <div class="col-1 d-flex justify-content-center align-items-center ">
            <a class="navbar-brand logoLink" href="#">
                <img class="logo" src=" <?= url('public/img/aerologo.webp') ?>" alt="Logo de la pagina" >
            </a>
        </div>
        
        <div class="col d-none d-md-flex align-items-center justify-content-center">
                <ul class="nav navbar-custom d-flex overflow-hidden rounded-2">
                    <li class="nav-item active">
                        <a href="<?= url('index.php?pagina=inicio') ?>" class="nav-link">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link">Vuelos</a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link">Promociones</a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link">Novedades</a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link">Ayuda</a>
                    </li>
                </ul>
        </div>

        <div class="col-1 d-flex justify-content-center align-items-center">

            <?php if ($isLoggedIn): ?>
                <!-- ESTADO LOGUEADO -->
                <div class="dropdown d-none d-md-block">
                    <button class="btn btn-custom dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="<?= url('public/img/icons/usuario.png') ?>" alt="Icono de perfil" class="icono_perfil">
                        <span><?= htmlspecialchars($usuario['nombre'] ?? 'Mi Cuenta') ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li><a class="dropdown-item" href="<?= $urlPanel ?>">Mi Panel (<?= strtoupper($rol) ?>)</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="/src/routes/usuarios.php?accion=logout">Cerrar Sesión</a></li>
                    </ul>
                </div>

                <!-- Botón Mobile Logueado -->
                <a href="<?= $urlPanel ?>" class="btn d-md-none btn-custom-perfil">
                    <img src="<?= url('public/img/icons/usuario.png') ?>" alt="Icono de perfil" class="icono_perfil-mobile">
                </a>
            <?php else: ?>
                <!-- ESTADO NO LOGUEADO -->
                <a href="<?= url('index.php?pagina=login') ?>" class="btn d-md-none btn-custom-perfil">
                    <img src="<?= url('public/img/icons/usuario.png') ?>" alt="Icono de perfil" class="icono_perfil-mobile">
                </a>
                
                <a href="<?= url('index.php?pagina=login') ?>" class="btn d-none d-md-flex px-lg-4 btn-custom align-items-center gap-2">
                    <img src="<?= url('public/img/icons/usuario.png') ?>" alt="Icono de perfil" class="icono_perfil">
                    Ingresar
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>