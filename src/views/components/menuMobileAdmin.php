<?php
require_once __DIR__ . '/adminNavLinks.php';

$urlPanel = $rol === 'ceo'
    ? url('index.php?pagina=ceo')
    : url('index.php?pagina=aerolinea&seccion=listado');
$usuario = usuarioActual();
?>

<div
    class="offcanvas offcanvas-start mobile-admin-menu"
    tabindex="-1"
    id="offcanvasAdmin"
    aria-labelledby="offcanvasAdminLabel"
    role="dialog"
>
    <div class="offcanvas-header">
        <span class="visually-hidden" id="offcanvasAdminLabel">Menú de <?= $rol === 'ceo' ? 'CEO' : 'administración' ?></span>
        <a class="navbar-brand logoLink m-0" href="<?= htmlspecialchars($urlPanel) ?>">
            <img
                src="<?= url('public/img/aerologo.webp') ?>"
                alt="Logo de Volaré"
                class="logo"
            >
        </a>
        <button
            type="button"
            class="btn-close btn-close-white"
            data-bs-dismiss="offcanvas"
            aria-label="Cerrar menú"
        ></button>
    </div>

    <div class="offcanvas-body d-flex flex-column">
        <div class="mobile-user-box mb-3">
            <img src="<?= url('public/img/icons/usuario.png') ?>" alt="Icono de usuario" class="icono_perfil-mobile">
            <div class="min-width-0">
                <p class="mobile-user-name text-truncate">
                    <?= htmlspecialchars(($usuario['nombre'] ?? '') . ' ' . ($usuario['apellido'] ?? '')) ?>
                </p>
                <p class="mobile-user-role"><?= htmlspecialchars(ucfirst($rol)) ?></p>
            </div>
        </div>

        <nav aria-label="<?= $rol === 'ceo' ? 'Menú de CEO' : 'Menú de administración' ?>">
            <ul class="navbar-nav pe-1">
                <?php foreach ($navLinks as $navLink): ?>
                    <li class="nav-item">
                        <a
                            class="nav-link mobile-menu-link <?= $navLink['activo'] ? 'active fw-bold' : '' ?>"
                            href="<?= url($navLink['href']) ?>"
                            <?= $navLink['activo'] ? 'aria-current="page"' : '' ?>
                        >
                            <?= htmlspecialchars($navLink['label']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <div class="mt-auto pt-4">
            <div class="mobile-menu-divider" role="presentation"></div>
            <ul class="navbar-nav pe-1">
                <li class="nav-item">
                    <a class="nav-link mobile-menu-link" href="<?= $urlPanel ?>">Mi panel</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link mobile-menu-link" href="<?= url('index.php?pagina=perfil') ?>">Mi perfil</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link mobile-menu-link" href="<?= url('index.php?pagina=ayuda') ?>">Ayuda</a>
                </li>
            </ul>
            <div class="mobile-menu-divider" role="presentation"></div>
            <a
                class="nav-link mobile-menu-link mobile-logout fw-semibold px-2"
                href="<?= url('src/routes/usuarios.php?accion=logout') ?>"
            >
                Cerrar sesión
            </a>
        </div>
    </div>
</div>