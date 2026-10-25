<?php

require_once __DIR__ . '/../../config/auth.php';

$rol = rolActual();
$pagina = $_GET['pagina'] ?? '';
$seccion = $_GET['seccion'] ?? '';
$navLinks = [];

if ($rol === 'admin') {
    $navLinks = [
        ['label' => 'Aerolíneas', 'href' => 'index.php?pagina=aerolinea&seccion=listado', 'icon' => 'avionSidebar.png', 'activo' => $pagina === 'aerolinea'],
        ['label' => 'Promociones', 'href' => 'index.php?pagina=promociones&seccion=listado', 'icon' => 'promoSidebar.png', 'activo' => $pagina === 'promociones'],
        ['label' => 'Novedades', 'href' => 'index.php?pagina=novedades&seccion=listado', 'icon' => 'novedadesSidebar.png', 'activo' => $pagina === 'novedades' || $pagina === 'novedad'],
        ['label' => 'Usuarios', 'href' => 'index.php?pagina=usuarios&seccion=listado', 'icon' => 'usuario.png', 'activo' => in_array($pagina, ['usuario', 'usuarios'], true)],
        ['label' => 'Aprobar CEOs', 'href' => 'index.php?pagina=ceos&seccion=listado', 'icon' => 'ceo.png', 'activo' => $pagina === 'ceos'],
        ['label' => 'Reportes', 'href' => 'index.php?pagina=reportes&seccion=listado', 'icon' => 'reporteSidebar.png', 'activo' => $pagina === 'reportes'],
    ];
} elseif ($rol === 'ceo') {
    $navLinks = [
        ['label' => 'Aerolínea', 'href' => 'index.php?pagina=ceo', 'icon' => 'avionSidebar.png', 'activo' => $pagina === 'ceo' && $seccion === ''],
        ['label' => 'Vuelos', 'href' => 'index.php?pagina=vuelos', 'icon' => 'avionSidebar.png', 'activo' => $pagina === 'vuelos'],
        ['label' => 'Promociones', 'href' => 'index.php?pagina=ceo&seccion=promociones', 'icon' => 'promoSidebar.png', 'activo' => $pagina === 'ceo' && in_array($seccion, ['promociones', 'promocion-detalle'], true)],
        ['label' => 'Reportes', 'href' => 'index.php?pagina=ceo&seccion=reportes', 'icon' => 'reporteSidebar.png', 'activo' => $pagina === 'ceo' && $seccion === 'reportes'],
    ];
}
