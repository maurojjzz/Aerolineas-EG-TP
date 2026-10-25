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
    // $isLoggedIn && $esStaff           => $urlPanel,
    default                           => url('index.php?pagina=inicio'),
};

$urlNovedades = url('index.php?pagina=cliente-novedades&tab=novedades');
$urlPromociones = url('index.php?pagina=cliente-novedades&tab=promociones');

$paginaActual = $_GET['pagina'] ?? 'inicio';
$tabActual = $_GET['tab'] ?? 'novedades';

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
        ['label' => 'Vuelos', 'href' => url('index.php?pagina=buscar-vuelos'), 'active' => $paginaActual === 'buscar-vuelos'],
        ['label' => 'Promociones', 'href' => $urlPromociones, 'active' => $paginaActual === 'cliente-novedades' && $tabActual === 'promociones'],
        ['label' => 'Historial de compras', 'href' => url('index.php?pagina=historial-compras'), 'active' => $paginaActual === 'historial-compras'],
        ['label' => 'Perfil', 'href' => $urlPerfil, 'active' => $paginaActual === 'perfil'],
        ['label' => 'Ayuda', 'href' => url('index.php?pagina=ayuda'), 'active' => $paginaActual === 'ayuda'],
    ];
} else {
    if ($rol === 'admin') {
        $navLinks = [
            ['label' => 'Aerolíneas', 'href' => url('index.php?pagina=aerolinea&seccion=listado'), 'active' => $paginaActual === 'aerolinea'],
            ['label' => 'Promociones', 'href' => url('index.php?pagina=promociones&seccion=listado'), 'active' => $paginaActual === 'promociones'],
            ['label' => 'Novedades', 'href' => url('index.php?pagina=novedades&seccion=listado'), 'active' => in_array($paginaActual, ['novedad', 'novedades'], true)],
            ['label' => 'Usuarios', 'href' => url('index.php?pagina=usuarios&seccion=listado'), 'active' => in_array($paginaActual, ['usuario', 'usuarios'], true)],
            ['label' => 'Aprobar CEOs', 'href' => url('index.php?pagina=ceos&seccion=listado'), 'active' => $paginaActual === 'ceos'],
            ['label' => 'Reportes', 'href' => url('index.php?pagina=reportes&seccion=listado'), 'active' => $paginaActual === 'reportes'],
        ];
    } else {
        $navLinks = [
            ['label' => 'Aerolínea', 'href' => url('index.php?pagina=ceo'), 'active' => $paginaActual === 'ceo' && empty($_GET['seccion'])],
            ['label' => 'Vuelos', 'href' => url('index.php?pagina=vuelos'), 'active' => in_array($paginaActual, ['vuelo', 'vuelos'], true)],
            ['label' => 'Promociones', 'href' => url('index.php?pagina=ceo&seccion=promociones'), 'active' => $paginaActual === 'ceo' && in_array($_GET['seccion'] ?? '', ['promociones', 'promocion-detalle'], true)],
            ['label' => 'Reportes', 'href' => url('index.php?pagina=ceo&seccion=reportes'), 'active' => $paginaActual === 'ceo' && ($_GET['seccion'] ?? '') === 'reportes'],
        ];
    }
}
