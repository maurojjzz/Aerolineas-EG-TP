<?php

require_once __DIR__ . '/../../config/auth.php';

$rol     = rolActual();
$pagina  = $_GET['pagina'] ?? '';
$seccion = $_GET['seccion'] ?? '';

// Links por rol
$navLinks = [];

if ($rol === 'admin') {
    $navLinks = [
        [
            'label'  => 'Aerolíneas',
            'href'   => 'index.php?pagina=aerolinea&seccion=listado',
            'icon'   => 'avionSidebar.png',
            // activo si pagina=aerolinea, sin importar la seccion
            'activo' => $pagina === 'aerolinea',
        ],
        [
            'label'  => 'Promociones',
            'href'   => 'index.php?pagina=promociones&seccion=listado',
            'icon'   => 'promoSidebar.png',
            'activo' => $pagina === 'promociones',
        ],
        [
            'label'  => 'Novedades',
            'href'   => 'index.php?pagina=novedades&seccion=listado',
            'icon'   => 'novedadesSidebar.png',
            'activo' => $pagina === 'novedades' || $pagina === 'novedad',
        ],
        [
            'label'  => 'Usuarios',
            'href'   => 'index.php?pagina=usuarios&seccion=listado',
            'icon'   => 'usuario.png',
            'activo' => in_array($pagina, ['usuario', 'usuarios'], true),
        ],
        [
            'label'  => 'Aprobar CEOs',
            'href'   => 'index.php?pagina=ceos&seccion=listado',
            'icon'   => 'ceo.png',
            'activo' => $pagina === 'ceos',
        ],
        [
            'label'  => 'Reportes',
            'href'   => 'index.php?pagina=reportes&seccion=listado',
            'icon'   => 'reporteSidebar.png',
            'activo' => $pagina === 'reportes',
        ],
        
    ];
}

if ($rol === 'ceo') {
    $navLinks = [
        [
            'label'  => 'Aerolínea',
            'href'   => 'index.php?pagina=ceo',
            'icon'   => 'avionSidebar.png',
            'activo' => $pagina === 'ceo' && $seccion === '',
        ],
        [
            'label'  => 'Vuelos',
            'href'   => 'index.php?pagina=vuelos',
            'icon'   => 'avionSidebar.png',
            'activo' => $pagina === 'vuelos',
        ],
        [
            'label'  => 'Promociones',
            'href'   => 'index.php?pagina=ceo&seccion=promociones',
            'icon'   => 'promoSidebar.png',
            'activo' => $pagina === 'ceo' && in_array($seccion, ['promociones', 'promocion-detalle']),
        ],
        [
            'label'  => 'Reportes',
            'href'   => 'index.php?pagina=ceo&seccion=reportes',
            'icon'   => 'reporteSidebar.png',
            'activo' => $pagina === 'ceo' && $seccion === 'reportes',
        ],
    ];
}
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