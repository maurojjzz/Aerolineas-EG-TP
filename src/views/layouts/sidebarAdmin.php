<?php

require_once __DIR__ . '/../components/adminNavLinks.php';
?>

<nav class="d-flex flex-column align-items-center m-0 ctm-sidebar" id="sidebarAdmin" aria-label="Menú lateral de administración">

    <div class="d-flex flex-column align-items-center justify-content-center gap-2 py-3 px-3 custom-logo">
        <a class="navbar-brand logoLink d-flex flex-column align-items-center text-center" href="<?= url('index.php?pagina=inicio') ?>">
            <img src="<?= url('public/img/aerologo.webp') ?>" alt="Logo de Volaré" class="logo-sidebar">
            <span class="h6 d-block mb-0 mt-2 text-white">Volaré</span>
        </a>
    </div>

    <ul class="nav navbar-custom d-flex flex-column align-items-center w-100 gap-3 rounded-2 py-3 px-3">
        <?php foreach ($navLinks as $navLink): ?>
        <li class="nav-item">
            <a class="nav-link rounded-3 <?= $navLink['activo'] ? 'active' : '' ?>"
                href="<?= url($navLink['href']) ?>"
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                data-bs-title="<?= htmlspecialchars($navLink['label']) ?>"
                data-bs-trigger="hover"
                <?= $navLink['activo'] ? 'aria-current="page"' : '' ?>
            >
                <img src="<?= url('public/img/icons/' . $navLink['icon']) ?>"
                    alt=""
                    class="iconos-sidebar">
                <span><?= htmlspecialchars($navLink['label']) ?></span>
            </a>
        </li>
        <?php endforeach; ?>
        <li class="nav-item">
            <a class="nav-link rounded-3" 
                href="<?= url('src/routes/usuarios.php?accion=logout') ?>"
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                data-bs-title="Cerrar Sesión"
                data-bs-trigger="hover"
            >
                <img src="<?= url('public/img/icons/cerrar-sesion.png') ?>" alt="" class="iconos-sidebar">
                <span>Cerrar Sesión</span>
            </a>
        </li>
    </ul>

</nav>