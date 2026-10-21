<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['usuario']);
$usuario    = $isLoggedIn ? $_SESSION['usuario'] : null;
$rol        = strtolower(trim((string)($usuario['rol'] ?? 'cliente')));
$esStaff    = in_array($rol, ['admin', 'ceo'], true);

$urlPanel = match ($rol) {
    'admin' => url('index.php?pagina=aerolinea&seccion=listado'),
    'ceo'   => url('index.php?pagina=ceo'),
    default => url('index.php?pagina=cliente'),
};

$urlPerfil   = url('index.php?pagina=perfil');
$urlLogin    = url('index.php?pagina=login');
$urlRegistro = url('index.php?pagina=registro');
$urlLogout   = url('src/routes/usuarios.php?accion=logout');

$urlInicio = match (true) {
    $isLoggedIn && $rol === 'cliente' => url('index.php?pagina=cliente'),
    $isLoggedIn && $esStaff           => $urlPanel,
    default                           => url('index.php?pagina=inicio'),
};

$paginaActual = $_GET['pagina'] ?? 'inicio';

$rolLabel = match ($rol) {
    'admin' => 'Administrador',
    'ceo'   => 'CEO',
    default => 'Cliente',
};

$navLinks = [];

if (!$isLoggedIn) {
    $navLinks = [
        ['label' => 'Inicio', 'href' => url('index.php?pagina=inicio'), 'active' => $paginaActual === 'inicio'],
        ['label' => 'Vuelos', 'href' => url('index.php?pagina=buscar-vuelos'), 'active' => $paginaActual === 'buscar-vuelos'],
        ['label' => 'Ayuda', 'href' => url('index.php?pagina=ayuda'), 'active' => $paginaActual === 'ayuda'],
        ['label' => 'Contacto', 'href' => url('index.php?pagina=contacto'), 'active' => $paginaActual === 'contacto'],
    ];
} elseif ($rol === 'cliente') {
    $navLinks = [
        ['label' => 'Inicio', 'href' => $urlInicio, 'active' => in_array($paginaActual, ['cliente', 'inicio'], true)],
        ['label' => 'Mis reservas', 'href' => url('index.php?pagina=mis-reservas'), 'active' => $paginaActual === 'mis-reservas'],
        ['label' => 'Historial de compras', 'href' => url('index.php?pagina=historial-compras'), 'active' => $paginaActual === 'historial-compras'],
        ['label' => 'Perfil', 'href' => $urlPerfil, 'active' => $paginaActual === 'perfil'],
        ['label' => 'Ayuda', 'href' => url('index.php?pagina=ayuda'), 'active' => $paginaActual === 'ayuda'],
    ];
} else {
    $panelPages = $rol === 'admin'
        ? ['aerolinea', 'usuario', 'usuarios', 'novedad', 'novedades', 'promociones', 'ceos']
        : ['ceo', 'vuelo', 'vuelos', 'promociones'];

    $navLinks = [
        ['label' => 'Mi Panel', 'href' => $urlPanel, 'active' => in_array($paginaActual, $panelPages, true)],
        ['label' => 'Perfil', 'href' => $urlPerfil, 'active' => $paginaActual === 'perfil'],
        ['label' => 'Ayuda', 'href' => url('index.php?pagina=ayuda'), 'active' => $paginaActual === 'ayuda'],
    ];
}
