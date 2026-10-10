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
            'label'  => 'Inicio',
            'href'   => 'index.php?pagina=inicio',
            'icon'   => 'casa.png',
            'activo' => $pagina === 'inicio',
        ],
        [
            'label'  => 'Aerolíneas',
            'href'   => 'index.php?pagina=aerolinea&seccion=listado',
            'icon'   => 'avionSidebar.png',
            // activo si pagina=aerolinea, sin importar la seccion
            'activo' => $pagina === 'aerolinea',
        ],
        [
            'label'  => 'Promociones',
            'href'   => '#',
            'icon'   => 'promoSidebar.png',
            'activo' => $pagina === 'promociones',
        ],
        [
            'label'  => 'Novedades',
            'href'   => '#',
            'icon'   => 'novedadesSidebar.png',
            'activo' => $pagina === 'novedades',
        ],
        [
            'label'  => 'Usuarios',
            'href'   => 'index.php?pagina=usuarios&seccion=listado',
            'icon'   => 'usuario.png',
            'activo' => $pagina === 'usuarios',
        ],
        [
            'label'  => 'CEOs',
            'href'   => '#',
            'icon'   => 'ceo.png',
            'activo' => $pagina === 'ceos',
        ],
        [
            'label'  => 'Reportes',
            'href'   => '#',
            'icon'   => 'reporteSidebar.png',
            'activo' => $pagina === 'reportes',
        ],
        [
            'label'  => 'Configuración',
            'href'   => '#',
            'icon'   => 'configuracionSidebar.png',
            'activo' => $pagina === 'configuracion',
        ],
    ];
}

if ($rol === 'ceo') {
    $navLinks = [
        [
            'label'  => 'Mi Aerolínea',
            'href'   => 'index.php?pagina=ceo',
            'icon'   => 'casa.png',
            'activo' => $pagina === 'ceo',
        ],
        [
            'label'  => 'Vuelos',
            'href'   => 'index.php?pagina=vuelos',
            'icon'   => 'avionSidebar.png',
            'activo' => $pagina === 'vuelos',
        ],
        [
            'label'  => 'Promociones',
            'href'   => '#',
            'icon'   => 'promoSidebar.png',
            'activo' => $pagina === 'promociones',
        ],
        [
            'label'  => 'Novedades',
            'href'   => '#',
            'icon'   => 'novedadesSidebar.png',
            'activo' => $pagina === 'novedades',
        ],
        [
            'label'  => 'Reportes',
            'href'   => '#',
            'icon'   => 'reporteSidebar.png',
            'activo' => $pagina === 'reportes',
        ],
        [
            'label'  => 'Configuración',
            'href'   => '#',
            'icon'   => 'configuracionSidebar.png',
            'activo' => $pagina === 'configuracion',
        ],
    ];
}
?>

<div class="d-flex flex-column align-items-center m-0 ctm-sidebar">

    <div class="d-flex flex-column align-items-center justify-content-center gap-2 py-3 px-3 custom-logo">
        <img src="<?= url('public/img/aerologo.webp') ?>" alt="logo" class="logo-sidebar">
        <h6>Vuela sin limites</h6>
    </div>

    <ul class="nav navbar-custom d-flex flex-column align-items-center w-100 gap-3 rounded-2 py-3 px-3">
        <?php foreach ($navLinks as $link): ?>
        <li class="nav-item">
            <a class="nav-link rounded-3 <?= $link['activo'] ? 'active' : '' ?>"
                href="<?= url($link['href']) ?>"
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                data-bs-title="<?= htmlspecialchars($link['label']) ?>"
                data-bs-trigger="hover"
            >
                <img src="<?= url('public/img/icons/' . $link['icon']) ?>"
                    alt="<?= htmlspecialchars($link['label']) ?>"
                    class="iconos-sidebar">
                <span><?= htmlspecialchars($link['label']) ?></span>
            </a>
        </li>
        <?php endforeach; ?>
        <li class="nav-item">
            <a class="nav-link rounded-3" 
                href="/src/routes/usuarios.php?accion=logout"
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                data-bs-title="Cerrar Sesion"
                data-bs-trigger="hover"
            >
                <img src="<?= url('public/img/icons/cerrar-sesion.png') ?>" alt="icono reportes seccion admin" class="iconos-sidebar">
                <span>Cerrar Sesion</span>
            </a>
        </li>
    </ul>

</div>