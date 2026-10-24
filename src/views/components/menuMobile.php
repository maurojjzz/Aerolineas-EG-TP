<?php
require_once __DIR__ . '/navLinks.php';
?>
<nav class="navbar">
    <button class="navbar-toggler custom-toggler"
            type="button"
            data-bs-toggle="offcanvas"
            data-bs-target="#offcanvasNavbar"
            aria-controls="offcanvasNavbar"
            aria-label="Abrir menú de navegación">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="offcanvas offcanvas-start"
         tabindex="-1"
         id="offcanvasNavbar"
         aria-labelledby="offcanvasNavbarLabel">
        <div class="offcanvas-header">
            <a class="navbar-brand logoLink m-0" href="<?= htmlspecialchars($urlInicio) ?>">
                <img class="logo" src="<?= url('public/img/aerologo.webp') ?>" alt="Logo de Volaré">
            </a>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Cerrar menú"></button>
        </div>

        <div class="offcanvas-body d-flex flex-column">
            <h5 class="visually-hidden" id="offcanvasNavbarLabel">Menú de navegación</h5>

            <?php if ($isLoggedIn): ?>
                <div class="mobile-user-box mb-3">
                    <img src="<?= url('public/img/icons/usuario.png') ?>" alt="" class="icono_perfil-mobile">
                    <div class="min-width-0">
                        <p class="mobile-user-name text-truncate"><?= htmlspecialchars($usuario['nombre'] ?? 'Mi Cuenta') ?></p>
                        <p class="mobile-user-role"><?= htmlspecialchars($rolLabel) ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <ul class="navbar-nav pe-1">
                <?php foreach ($navLinks as $navLink): ?>
                    <li class="nav-item">
                        <a class="nav-link text-white <?= $navLink['active'] ? 'active fw-bold' : '' ?>"
                           href="<?= htmlspecialchars($navLink['href']) ?>"
                           <?= $navLink['active'] ? 'aria-current="page"' : '' ?>>
                            <?= htmlspecialchars($navLink['label']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="mt-auto pt-4">
                <hr class="border-light opacity-25">
                <?php if ($isLoggedIn): ?>
                    <a class="nav-link mobile-logout fw-semibold px-2" href="<?= htmlspecialchars($urlLogout) ?>">Cerrar Sesión</a>
                <?php else: ?>
                    <a class="btn btn-light w-100 fw-semibold" href="<?= htmlspecialchars($urlLogin) ?>">Iniciar sesión</a>
                    <a class="nav-link text-white text-center mt-2" href="<?= htmlspecialchars($urlRegistro) ?>">Crear cuenta</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
