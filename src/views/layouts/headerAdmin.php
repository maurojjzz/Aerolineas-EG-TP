<?php

require_once __DIR__ . '/../../config/auth.php';

$rol = rolActual();

$usuario = usuarioActual();

?>
<div class="container-header d-flex align-items-center bg-light row border-bottom border-2 w-100 m-0 p-0 g-0">
    <button type="button" class=" col-1 btn ctm-btn-header d-none d-md-block" id="sidebarToggleHamburguesa">
        <img src="<?= url('public/img/icons/menu-hamburguesa.png') ?>" alt="icono menu hamburguesa" class="logo-header-admin opacity-75"> 
    </button>

    <!-- menu hamburguesa para mobile -->
    <button 
        type="button" 
        class=" col-1 btn ctm-btn-header d-md-none" 
        id="sidebarToggleHamburguesaMobile"
        data-bs-toggle="offcanvas"
        data-bs-target="#offcanvasAdmin"
        aria-controls="offcanvasAdmin"   
    >
            <img src="<?= url('public/img/icons/menu-hamburguesa.png') ?>" alt="icono menu hamburguesa" class="logo-header-admin opacity-75"> 
    </button>

    <h5 class="col-6 col-sm m-0 p-0 ">
        <span class="d-none d-sm-block">
            <?php if ($rol === 'ceo') { echo 'Panel de CEO'; } else { echo 'Panel de Administración'; } ?>
        </span>
        <span class="d-sm-none ms-3">
            <?php if ($rol === 'ceo') { echo 'CEO'; } else { echo 'Admin'; } ?>
        </span>

    </h5>

    <div class="col ctm-user-info d-flex justify-content-end align-items-center gap-2 pe-4 position-relative">

        <button type="button" class="btn btn-bell position-relative p-0 m-0 me-4 d-none d-sm-block">
            <img src="<?= url('public/img/icons/bell.png') ?>" alt="icono campana notificacion" class="bell-noti opacity-75"> 
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">
                1
                <span class="visually-hidden">unread messages</span>
            </span>
        </button>

        <div class="dropdown">
            <button class="btn p-0 border-0 bg-transparent d-flex align-items-center gap-2"
                    type="button"
                    data-bs-toggle="dropdown"
                    aria-expanded="false">

                <div class="pfp-icon d-flex align-items-center justify-content-center rounded-circle">
                    <?= strtoupper(substr($usuario['nombre'] ?? 'A', 0, 1) . substr($usuario['apellido'] ?? 'J', 0, 1)) ?>
                </div>

                <div class="d-none d-md-flex flex-column align-items-start justify-content-center gap-0 ctm-user-text">
                    <p class="m-0 p-0 ctm-rol"><?= htmlspecialchars(ucfirst($rol)) ?></p>
                    <p class="m-0 p-0 ctm-name">
                        <?= htmlspecialchars($usuario['nombre'] ?? '') . ' ' . htmlspecialchars($usuario['apellido'] ?? '') ?>
                    </p>
                </div>

                <span class="down-arrow" style="font-size:1.5rem;font-weight:500;">⌄</span>
            </button>

            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                <li>
                    <div class="px-3 py-2 border-bottom">
                        <p class="m-0 fw-semibold" style="font-size:0.9rem;">
                            <?= htmlspecialchars(($usuario['nombre'] ?? '') . ' ' . ($usuario['apellido'] ?? '')) ?>
                        </p>
                        <p class="m-0 text-muted" style="font-size:0.8rem;">
                            <?= htmlspecialchars($usuario['email'] ?? '') ?>
                        </p>
                    </div>
                </li>
                <li>
                    <a class="dropdown-item py-2" href="#">
                        Mi perfil
                    </a>
                </li>
                <li>
                    <a class="dropdown-item py-2" href="#">
                        Configuración
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item py-2 text-danger" href="/src/routes/usuarios.php?accion=logout">
                        Cerrar sesión
                    </a>
                </li>
            </ul>
        </div>

    </div>


</div>
