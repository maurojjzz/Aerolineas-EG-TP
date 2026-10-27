<?php
date_default_timezone_set('America/Argentina/Buenos_Aires');
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../controllers/VueloController.php';
require_once __DIR__ . '/../../controllers/ReservaController.php';

$ciudades = require __DIR__ . '/../../data/aeropuertos.php';


$origen = trim($_GET['origen']    ?? '');
$destino = trim($_GET['destino']   ?? '');
$fecha = trim($_GET['fecha']     ?? '');
$pasajeros = max(1, (int)($_GET['pasajeros'] ?? 1));
$pagina = max(1, (int)($_GET['pag'] ?? 1));
$orden     = in_array($_GET['orden'] ?? '', ['salida', 'precio_asc', 'precio_desc'])? $_GET['orden']: 'salida';
$porPagina = 5;

// ── Filtros de la columna lateral ─────────────────────────────────────────────
$franjasValidas   = ['madrugada', 'manana', 'tarde', 'noche'];
$horariosFiltro   = array_values(array_intersect((array)($_GET['horario'] ?? []), $franjasValidas));
$aerolineasFiltro = array_values(array_unique(array_filter(
    array_map('intval', (array)($_GET['aerolinea'] ?? [])),
    fn($id) => $id > 0
)));

$precioMinFiltro = (isset($_GET['precio_min']) && is_numeric($_GET['precio_min']))
    ? max(0, (float)$_GET['precio_min'])
    : null;
$precioMaxFiltro = (isset($_GET['precio_max']) && is_numeric($_GET['precio_max']))
    ? max(0, (float)$_GET['precio_max'])
    : null;

if ($precioMinFiltro !== null && $precioMaxFiltro !== null && $precioMinFiltro > $precioMaxFiltro) {
    [$precioMinFiltro, $precioMaxFiltro] = [$precioMaxFiltro, $precioMinFiltro];
}

$filtros = [
    'aerolineas' => $aerolineasFiltro,
    'horarios'   => $horariosFiltro,
    'precioMin'  => $precioMinFiltro,
    'precioMax'  => $precioMaxFiltro,
];

$cantidadFiltros = count($aerolineasFiltro)
    + count($horariosFiltro)
    + ($precioMinFiltro !== null ? 1 : 0)
    + ($precioMaxFiltro !== null ? 1 : 0);

$etiquetasHorario = [
    'madrugada' => 'Madrugada (00 a 06)',
    'manana'    => 'Mañana (06 a 12)',
    'tarde'     => 'Tarde (12 a 18)',
    'noche'     => 'Noche (18 a 24)',
];

$tipoViaje = ($_GET['tipo_viaje'] ?? 'solo_ida') === 'ida_vuelta' ? 'ida_vuelta' : 'solo_ida';
$esIdaVuelta = $tipoViaje === 'ida_vuelta';
$fechaVuelta = trim($_GET['fecha_vuelta'] ?? '');
$paso = $esIdaVuelta && (($_GET['paso'] ?? '') === 'vuelta') ? 'vuelta' : 'ida';
$idVueloIda = max(0, (int)($_GET['idVueloIda'] ?? 0));

$errorFechaVuelta = '';
$errorBusqueda = '';
if ($esIdaVuelta && $paso === 'vuelta') {
    if ($fechaVuelta === '') {
        $errorFechaVuelta = 'Elegí una fecha de vuelta para ver los vuelos disponibles.';
    } elseif ($fecha !== '' && $fechaVuelta < $fecha) {
        $errorFechaVuelta = 'La fecha de vuelta no puede ser anterior a la fecha de ida.';
    }
}

$origenActual  = $paso === 'vuelta' ? $destino : $origen;
$destinoActual = $paso === 'vuelta' ? $origen : $destino;
$fechaActual   = $paso === 'vuelta' ? $fechaVuelta : $fecha;

$ctrl= new VueloController($link);

if ($errorBusqueda === '' && $errorFechaVuelta === '') {
    $resultado = $ctrl->buscarVuelos($origenActual, $destinoActual, $fechaActual, $pasajeros, $pagina, $porPagina, $orden, $filtros);
} else {
    $resultado = ['data' => [], 'total' => 0, 'totalPaginas' => 0];
}

$facetas = $errorFechaVuelta === ''
    ? $ctrl->obtenerFacetasVuelos($origenActual, $destinoActual, $fechaActual, $pasajeros)
    : ['aerolineas' => [], 'precioMin' => 0, 'precioMax' => 0, 'total' => 0];

$vuelos  = $resultado['data'];
$total = $resultado['total'];
$totalPaginas = $resultado['totalPaginas'];

$vuelosConReservaActiva = [];
if (estaLogueado() && rolActual() === 'cliente' && !empty($vuelos)) {
    $reservaCtrl = new ReservaController($link);
    $vuelosConReservaActiva = $reservaCtrl->obtenerVuelosConReservaActiva(
        (int)($_SESSION['usuario']['idUsuario'] ?? 0),
        array_column($vuelos, 'idVuelo')
    );
}

$desde = $total === 0 ? 0 : ($pagina - 1) * $porPagina + 1;
$hasta = min($pagina * $porPagina, $total);

function buscarCiudadPorCodigo(array $aeropuertos, string $codigo): string {
    if ($codigo === '') return '';

    foreach ($aeropuertos as $a) {
        if (strcasecmp($a['code'], $codigo) === 0) {
            return $a['city'] . ' (' . $a['code'] . ')';
        }
    }
    return $codigo;
}

$origenTexto  = buscarCiudadPorCodigo($ciudades, $origen);
$destinoTexto = buscarCiudadPorCodigo($ciudades, $destino);
$origenActualTexto  = buscarCiudadPorCodigo($ciudades, $origenActual);
$destinoActualTexto = buscarCiudadPorCodigo($ciudades, $destinoActual);

function urlBusqueda(array $cambios = []): string {
    $params = $_GET;
    unset($params['pagina']);
    $params = array_merge($params, $cambios);
    $query  = http_build_query($params);

    return url('index.php?pagina=buscar-vuelos' . ($query !== '' ? '&' . $query : ''));
}

function urlPaginaBusqueda(int $pag): string {
    return urlBusqueda(['pag' => $pag]);
}

function urlQuitarFiltros(): string {
    $params = $_GET;
    foreach (['aerolinea', 'horario', 'precio_min', 'precio_max', 'pag', 'pagina'] as $clave) {
        unset($params[$clave]);
    }
    $query = http_build_query($params);

    return url('index.php?pagina=buscar-vuelos' . ($query !== '' ? '&' . $query : ''));
}

function urlQuitarFiltro(string $clave, ?string $valor = null): string {
    $params = $_GET;
    unset($params['pag'], $params['pagina']);

    if ($valor === null || !isset($params[$clave]) || !is_array($params[$clave])) {
        unset($params[$clave]);
    } else {
        $params[$clave] = array_values(array_filter(
            (array)$params[$clave],
            fn($item) => (string)$item !== $valor
        ));

        if (empty($params[$clave])) {
            unset($params[$clave]);
        }
    }

    $query = http_build_query($params);

    return url('index.php?pagina=buscar-vuelos' . ($query !== '' ? '&' . $query : ''));
}

$urlLimpiarFiltros = urlQuitarFiltros();
$urlLimpiar        = $urlLimpiarFiltros;

$filtrosActivos = [
    'aerolineas' => $aerolineasFiltro,
    'horarios'   => $horariosFiltro,
    'precioMin'  => $precioMinFiltro,
    'precioMax'  => $precioMaxFiltro,
];

// Parámetros que el formulario de filtros tiene que conservar.
$paramsFiltros = array_filter([
    'origen'       => $origen,
    'destino'      => $destino,
    'fecha'        => $fecha,
    'pasajeros'    => $pasajeros,
    'tipo_viaje'   => $tipoViaje,
    'fecha_vuelta' => $fechaVuelta,
    'paso'         => $paso,
    'idVueloIda'   => $idVueloIda > 0 ? $idVueloIda : '',
    'orden'        => $orden,
], fn($valor) => $valor !== '' && $valor !== null);

function urlSeleccionarVuelo(array $vuelo, string $paso, bool $esIdaVuelta, int $idVueloIda, int $pasajeros): string {
    if (!$esIdaVuelta) {
        return url('index.php?pagina=reservar&idVuelo=' . (int)$vuelo['idVuelo'] . '&pasajeros=' . $pasajeros);
    }

    if ($paso === 'ida') {
        return urlBusqueda([
            'paso'       => 'vuelta',
            'idVueloIda' => (int)$vuelo['idVuelo'],
            'pag'        => 1,
        ]);
    }

    if ($idVueloIda > 0) {
        return url('index.php?pagina=reservar&idVuelo=' . $idVueloIda
            . '&idVueloVuelta=' . (int)$vuelo['idVuelo']
            . '&pasajeros=' . $pasajeros);
    }

    // Sin ida elegida: la vuelta se compra como una reserva simple.
    return url('index.php?pagina=reservar&idVuelo=' . (int)$vuelo['idVuelo'] . '&pasajeros=' . $pasajeros);
}

$urlPasoIda = urlBusqueda(['paso' => 'ida', 'pag' => 1]);
$urlPasoVuelta = urlBusqueda(['paso' => 'vuelta', 'pag' => 1]);

$textoVacio = !$esIdaVuelta
    ? 'Todavía no hay vuelos disponibles para esta búsqueda'
    : ($paso === 'vuelta' ? 'Todavía no hay vuelos de vuelta para esta búsqueda' : 'Todavía no hay vuelos de ida para esta búsqueda');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados de vuelos — Volaré</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= url('public/css/layout/header.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/components/menuMobile.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/components/searchDesktop.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/bootstrap-icons.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/layout/reservas.css') ?>">
    
    <link rel="stylesheet" href="<?= url('public/css/accesibilidad.css') ?>">
</head>
<body class="container-fluid overflow-x-hidden p-0 m-0">

<?php require './src/views/layouts/header.php'; ?>
<?php require './src/views/components/alertToast.php'; ?>

<!-- Hero con datos de búsqueda -->
<main id="contenido" tabindex="-1" style="min-height: 100vh;">
    <div class="container-xxl py-4 px-3 px-lg-5">
        <?php $migas = [['label' => 'Resultados de vuelos']]; require __DIR__ . '/../components/breadcrumbCliente.php'; ?>

        <div class="titulo-cuadro-resul text-center ">
            <h1 class="fw-bold text-dark display-6">RESULTADO DE <span>VUELOS</span></h1>
        </div>

        <?php if ($esIdaVuelta): ?>
            <div class="d-flex justify-content-center mb-3 px-2">
                <div class="d-flex align-items-center w-100" style="max-width:420px;">
                    <a href="<?= $urlPasoIda ?>" class="text-decoration-none d-flex flex-column align-items-center">
                        <div class="step-circle <?= ($paso === 'ida' || $idVueloIda > 0) ? 'bg-primary text-white' : 'bg-white text-secondary border' ?>">1</div>
                        <small class="text-dark fw-semibold mt-1" style="font-size:0.7rem;white-space:nowrap;">Ida</small>
                    </a>
                    <div class="step-line <?= $paso === 'vuelta' ? 'done' : '' ?>"></div>
                    <a href="<?= $urlPasoVuelta ?>" class="text-decoration-none d-flex flex-column align-items-center">
                        <div class="step-circle <?= $paso === 'vuelta' ? 'bg-primary text-white' : 'bg-white text-secondary border' ?>">2</div>
                        <small class="text-dark fw-semibold mt-1" style="font-size:0.7rem;white-space:nowrap;">Vuelta</small>
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <div class="d-flex flex-column mb-2">
            <p class="d-flex align-items-center justify-content-center gap-2 text-dark ">
                <span class="fw-semibold fs-5">
                    <?= $origenActual ? htmlspecialchars($origenActualTexto) : 'Cualquier origen' ?>
                </span> 

                <img class="img-fluid" src="<?= url('public/img/icons/flecha-right-white.png') ?>" alt="" aria-hidden="true" style="width: 35px; height: 30px; filter: brightness(0); opacity: .75;">
                
                <span class="fw-semibold fs-5"><?= $destinoActual ? htmlspecialchars($destinoActualTexto) : 'Cualquier destino' ?></span>
            </p>

            <div class="d-flex flex-column flex-sm-row justify-content-center justify-content-md-end align-items-center gap-4 ">
                <span class="fw-semibold text-dark">
                    <?= $esIdaVuelta ? ($paso === 'vuelta' ? 'Vuelta: ' : 'Ida: ') : '' ?>
                    <?= $fechaActual ? htmlspecialchars($fechaActual) : 'Sin fecha especifica' ?>
                </span> 

                <span class="fw-semibold text-dark">
                    <?= $pasajeros ?> pasajero<?= $pasajeros !== 1 ? 's' : '' ?> 
                </span> 

                <a href="<?= estaLogueado() ? url('index.php?pagina=cliente') : url('index.php?pagina=inicio') ?>" class="btn btn-outline-dark fw-semibold text-decoration-none" style="">
                    Modificar búsqueda
                </a>
            </div >
        </div>

        <?php if ($esIdaVuelta && $paso === 'vuelta'): ?>
            <div class="alert alert-light border shadow-sm d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                <?php if ($idVueloIda > 0): ?>
                    <span>
                        <strong>Ida elegida:</strong>
                        <?= htmlspecialchars($origenTexto) ?> → <?= htmlspecialchars($destinoTexto) ?>
                        <?= $fecha ? '· ' . htmlspecialchars($fecha) : '' ?>
                    </span>
                    <a href="<?= $urlPasoIda ?>" class="btn btn-sm btn-outline-primary fw-semibold">Cambiar ida</a>
                <?php else: ?>
                    <span>No elegiste un vuelo de ida. Si comprás solo la vuelta, se creará una reserva simple.</span>
                    <a href="<?= $urlPasoIda ?>" class="btn btn-sm btn-outline-primary fw-semibold">Elegir ida</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="row g-4">

            <!-- Columna lateral: filtros -->
            <div class="col-1 d-none d-lg-flex flex-column  rounded-4 filtros-sidebar" id="filtrosColumna">
                
                <button type="button"
                        class="d-flex align-items-center justify-content-end p-3 rounded-circle bg-white border-0"
                        id="filtroBtn"
                        aria-expanded="false"
                        aria-controls="filtros-contenido"
                        aria-label="Mostrar u ocultar filtros"
                        data-bs-toggle="tooltip"
                        data-bs-placement="right"
                        data-bs-title="Ver filtros"
                        data-bs-trigger="hover"
                        style="width:fit-content;">
                    <img
                        class="img-fluid filtro-toggle-icon"
                        src="<?= url('public/img/icons/filtrar.png') ?>"
                        alt="Icono de filtro"
                        style="width:22px;height:22px;"
                    >
                </button>

                <div class="filtros-contenido px-3 pb-3" id="filtros-contenido">
                    <h2 class="filtro-titulo-panel fw-semibold mb-2">Filtros</h2>

                    <?php
                    $idPrefijo = 'filtrosDesktop';
                    require __DIR__ . '/../components/filtrosBusqueda.php';
                    ?>
                </div>
            </div>

            <!-- Columna principal: resultados -->
            <div class="col-12 col-lg-11 " id="resultadosColumna">

                <!-- Botón de filtros en mobile -->
                <div class="d-lg-none mb-3">
                    <button type="button"
                            class="btn btn-light border shadow-sm w-100 d-flex align-items-center justify-content-center gap-2 fw-semibold"
                            data-bs-toggle="offcanvas"
                            data-bs-target="#filtrosMobile"
                            aria-controls="filtrosMobile">
                        <i class="bi bi-funnel" aria-hidden="true"></i>
                        Filtros<?= $cantidadFiltros > 0 ? ' (' . $cantidadFiltros . ')' : '' ?>
                    </button>
                </div>

                <?php if ($cantidadFiltros > 0): ?>
                    <div class="filtros-chips mb-3">
                        <span class="filtros-chips-label">Filtros activos:</span>

                        <?php
                        $nombresAerolineas = [];
                        foreach ($facetas['aerolineas'] as $facAerolinea) {
                            $nombresAerolineas[(int)$facAerolinea['idAerolinea']] = $facAerolinea['nombreAerolinea'];
                        }
                        ?>

                        <?php foreach ($aerolineasFiltro as $idAerolineaFiltro): ?>
                            <a class="filtros-chip"
                               href="<?= urlQuitarFiltro('aerolinea', (string)$idAerolineaFiltro) ?>"
                               aria-label="Quitar filtro de aerolínea <?= htmlspecialchars($nombresAerolineas[$idAerolineaFiltro] ?? '') ?>">
                                <?= htmlspecialchars($nombresAerolineas[$idAerolineaFiltro] ?? 'Aerolínea') ?>
                                <span aria-hidden="true">×</span>
                            </a>
                        <?php endforeach; ?>

                        <?php foreach ($horariosFiltro as $horarioFiltro): ?>
                            <a class="filtros-chip"
                               href="<?= urlQuitarFiltro('horario', $horarioFiltro) ?>"
                               aria-label="Quitar filtro de horario <?= htmlspecialchars($etiquetasHorario[$horarioFiltro] ?? '') ?>">
                                <?= htmlspecialchars($etiquetasHorario[$horarioFiltro] ?? $horarioFiltro) ?>
                                <span aria-hidden="true">×</span>
                            </a>
                        <?php endforeach; ?>

                        <?php if ($precioMinFiltro !== null): ?>
                            <a class="filtros-chip"
                               href="<?= urlQuitarFiltro('precio_min') ?>"
                               aria-label="Quitar filtro de precio mínimo <?= number_format($precioMinFiltro, 0, ',', '.') ?> pesos">
                                Desde $ <?= number_format($precioMinFiltro, 0, ',', '.') ?>
                                <span aria-hidden="true">×</span>
                            </a>
                        <?php endif; ?>

                        <?php if ($precioMaxFiltro !== null): ?>
                            <a class="filtros-chip"
                               href="<?= urlQuitarFiltro('precio_max') ?>"
                               aria-label="Quitar filtro de precio máximo <?= number_format($precioMaxFiltro, 0, ',', '.') ?> pesos">
                                Hasta $ <?= number_format($precioMaxFiltro, 0, ',', '.') ?>
                                <span aria-hidden="true">×</span>
                            </a>
                        <?php endif; ?>

                        <a class="filtros-chip filtros-chip--limpiar" href="<?= $urlLimpiarFiltros ?>">Limpiar todo</a>
                    </div>
                <?php endif; ?>

                <?php if ($errorBusqueda !== ''): ?>
                    <div class="alert alert-warning rounded-4 shadow-sm p-4 text-center">
                        <p class="fs-5 fw-semibold mb-2">Faltan datos para buscar</p>
                        <p class="mb-0"><?= htmlspecialchars($errorBusqueda) ?></p>
                    </div>
                <?php elseif ($errorFechaVuelta !== ''): ?>
                    <div class="alert alert-warning rounded-4 shadow-sm p-4 text-center">
                        <p class="fs-5 fw-semibold mb-2">No pudimos buscar la vuelta</p>
                        <p class="mb-3"><?= htmlspecialchars($errorFechaVuelta) ?></p>
                        <a href="<?= $urlPasoIda ?>" class="btn btn-primary">Volver a los vuelos de ida</a>
                    </div>
                <?php elseif (empty($vuelos)): ?>
                    <div class="bg-white rounded-4 p-5 text-center shadow-sm">
                        <p class="fs-4 fw-semibold text-muted mb-2"><?= htmlspecialchars($textoVacio) ?></p>
                        <p class="text-muted">Probá cambiando las fechas o el destino.</p>

                        <?php if ($cantidadFiltros > 0): ?>
                            <p class="text-muted mb-2">Ningún vuelo coincide con los filtros aplicados.</p>
                            <a href="<?= $urlLimpiarFiltros ?>" class="btn btn-outline-primary mt-1">Quitar filtros</a>
                        <?php endif; ?>

                        <?php if ($esIdaVuelta && $paso === 'ida'): ?>
                            <a href="<?= $urlPasoVuelta ?>" class="btn btn-primary mt-2">Ver vuelos de vuelta</a>
                        <?php elseif ($esIdaVuelta && $paso === 'vuelta' && $idVueloIda > 0): ?>
                            <a href="<?= url('index.php?pagina=reservar&idVuelo=' . $idVueloIda . '&pasajeros=' . $pasajeros) ?>"
                               class="btn btn-primary mt-2">
                                Continuar solo con la ida
                            </a>
                        <?php elseif ($esIdaVuelta && $paso === 'vuelta'): ?>
                            <a href="<?= $urlPasoIda ?>" class="btn btn-primary mt-2">Volver a los vuelos de ida</a>
                        <?php else: ?>
                            <a href="<?= url('index.php?pagina=cliente') ?>" class="btn btn-primary mt-2">Volver al inicio</a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>

                    <div class="bg-light rounded-2 p-3 mb-3 d-flex flex-column flex-md-row justify-content-between align-items-center shadow gap-3 gap-md-0">

                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="text-muted fw-semibold" style="font-size:0.9rem;">Ordenar por:</span>

                            <?php
                            $opciones = [
                                'salida'      => 'Salida más temprana',
                                'precio_asc'  => 'Menor precio',
                                'precio_desc' => 'Mayor precio',
                            ];
                            foreach ($opciones as $val => $label):
                                $activo = $orden === $val;
                                $href = urlBusqueda(['orden' => $val, 'pag' => 1]);
                            ?>
                                <a href="<?= $href ?>"
                                class="btn btn-sm <?= $activo ? 'btn-primary' : 'btn-outline-secondary' ?> fw-semibold"
                                style="font-size:0.85rem;">
                                    <?= $label ?>
                                </a>
                            <?php endforeach; ?>
                        </div>

                        <p class="text-muted text-end fs-6 fw-semibold p-0 m-0">
                            <?= $total ?> resultado<?= $total !== 1 ? 's' : '' ?> encontrado<?= $total !== 1 ? 's' : '' ?>
                        </p>
                    </div>

                    <div class="d-flex flex-column gap-3 ">
                    <?php foreach ($vuelos as $v):
                        $fechaSalida  = date('d/m/Y', strtotime($v['fechaHoraSalidaVuelo']));
                        $horaSalida   = date('H:i',   strtotime($v['fechaHoraSalidaVuelo']));
                        $precioFmt    = '$ ' . number_format($v['precioVuelo'], 2, ',', '.');
                        $esVueloSeleccionado = $esIdaVuelta && $paso === 'ida' && $idVueloIda === (int)$v['idVuelo'];
                        $tieneReservaActiva = in_array((int)$v['idVuelo'], $vuelosConReservaActiva, true);
                        $textoBoton   = $esIdaVuelta
                            ? ($paso === 'ida' ? 'Seleccionar ida' : 'Seleccionar vuelta')
                            : 'Seleccionar';
                    ?>
                        <div class="bg-white pb-3 rounded-4 p-4 shadow resultado-card <?= $esVueloSeleccionado ? 'border border-2 border-primary' : '' ?>">

                            <div class="row align-items-center g-3 ">

                                <!-- Aerolínea -->
                                <div class="col-12 col-md-2 d-flex align-items-center gap-2 ">
                                    <?php if ($v['logoUrl'] && $v['logoUrl'] !== '0'): ?>
                                        <img src="<?= htmlspecialchars($v['logoUrl']) ?>" alt="Logo de <?= htmlspecialchars($v['nombreAerolinea']) ?>" style="width:60px;height:60px;object-fit:contain;">
                                    <?php else: ?>
                                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center"
                                            style="width:60px;height:60px;">
                                            <span class="text-white fw-bold" style="font-size:0.7rem;">
                                                <?= htmlspecialchars(substr($v['codigoIATA'], 0, 2)) ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <p class="fw-semibold m-0" style="font-size:0.85rem;">
                                            <?= htmlspecialchars($v['nombreAerolinea']) ?>
                                        </p>
                                        <p class="text-muted m-0" style="font-size:0.75rem;">
                                            <?= htmlspecialchars($v['codigoIATA']) ?>
                                        </p>
                                    </div>
                                </div>

                                <!-- Ruta -->
                                <div class="col-12 col-md-6 ">
                                    <div class="ruta-vuelo">
                                        <div class="ruta-vuelo-punto text-center">
                                            <p class="fw-bold fs-5 m-0"><?= $horaSalida ?></p>
                                            <p class="m-0" style="font-size:0.8rem;"><?= htmlspecialchars($v['origenVuelo']) ?></p>
                                            <p class="text-muted m-0" style="font-size:0.75rem;"><?= $fechaSalida ?></p>
                                        </div>
                                        <div class="linea-vuelo" aria-hidden="true"></div>
                                        <div class="ruta-vuelo-punto text-center">
                                            <?php if (!empty($v['fechaHoraLlegadaVuelo'])): ?>
                                                <p class="fw-bold fs-5 m-0"><?= date('H:i', strtotime($v['fechaHoraLlegadaVuelo'])) ?></p>
                                            <?php else: ?>
                                                <p class="fw-bold fs-5 m-0 text-muted">—</p>
                                            <?php endif; ?>
                                            <p class="m-0" style="font-size:0.8rem;"><?= htmlspecialchars($v['destinoVuelo']) ?></p>
                                            <?php if (!empty($v['fechaHoraLlegadaVuelo'])): ?>
                                                <p class="text-muted m-0" style="font-size:0.75rem;">
                                                    <?= date('d/m/Y', strtotime($v['fechaHoraLlegadaVuelo'])) ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Asientos -->
                                <div class="col-12 col-sm-6 col-md-1 text-center ">
                                    <p class="text-muted m-0" style="font-size:0.75rem;">Asientos</p>
                                    <p class="fw-semibold m-0"><?= $v['asientosDisponibles'] ?></p>
                                </div>

                                <!-- Precio y botón -->
                                <div class="col-12 col-sm-6 col-md-3 text-center text-sm-end">
                                    <?php if ($tieneReservaActiva): ?>
                                        <div class="alert alert-warning py-2 px-3 mb-2 small text-center" role="status">
                                            <i class="bi bi-check-circle me-1" aria-hidden="true"></i>
                                            Ya tenés una reserva activa para este vuelo.
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($v['promo_descuento_total']) && $v['promo_descuento_total'] > 0): ?>
                                        <!-- Badge de descuento -->
                                        <div class="mb-1">
                                            <span class="badge bg-success fs-6 px-3 py-1">
                                                🎉 -<?= number_format($v['promo_descuento_total'], 2) ?>%
                                            </span>
                                            <?php if (!empty($v['promo_nombres']) && count($v['promo_nombres']) > 1): ?>
                                                <div class="text-muted small mt-1" title="<?= htmlspecialchars(implode(' + ', $v['promo_nombres'])) ?>">
                                                    <?= count($v['promo_nombres']) ?> promos
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <!-- Precio original tachado -->
                                        <p class="text-muted text-decoration-line-through m-0" style="font-size:0.85rem;">
                                            $ <?= number_format($v['precio_original'], 2, ',', '.') ?>
                                        </p>
                                        <!-- Precio con descuento -->
                                        <p class="fw-bold text-success fs-6 m-0">
                                            $ <?= number_format($v['precio_con_descuento'], 2, ',', '.') ?>
                                        </p>
                                        <p class="text-muted m-0" style="font-size:0.7rem;">Por pasajero</p>
                                    <?php else: ?>
                                        <p class="precio-principal m-0"><?= $precioFmt ?></p>
                                        <p class="text-muted m-0" style="font-size:0.75rem;">Por pasajero</p>
                                    <?php endif; ?>
                                    <?php if ($tieneReservaActiva): ?>
                                        <a href="<?= url('index.php?pagina=mis-reservas') ?>"
                                           class="btn btn-outline-secondary btn-sm mt-2 px-4">
                                            Ver mis reservas
                                        </a>
                                    <?php elseif (estaLogueado() && rolActual() === 'cliente'): ?>
                                        <a href="<?= urlSeleccionarVuelo($v, $paso, $esIdaVuelta, $idVueloIda, $pasajeros) ?>"
                                        class="btn btn-primary btn-sm mt-2 px-4">
                                            <?= $textoBoton ?>
                                        </a>
                                    <?php elseif (!estaLogueado()): ?>
                                        <a href="<?= url('index.php?pagina=login') ?>"
                                        class="btn btn-outline-primary btn-sm mt-2 px-4">
                                            Iniciar sesión para reservar
                                        </a>
                                    <?php else: ?>
                                        <span class="badge bg-secondary mt-2">Solo clientes</span>
                                    <?php endif; ?>
                                </div>

                            </div>
                        </div>
                    <?php endforeach; ?>
                    </div>

                    <!-- Paginación -->
                    <?php if ($totalPaginas > 1): ?>
                    <div class="d-flex justify-content-center mt-4">
                        <ul class="pagination">
                            <?php if ($pagina > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= urlPaginaBusqueda($pagina - 1) ?>">‹</a>
                                </li>
                            <?php endif; ?>
                            <?php for ($i = max(1, $pagina - 2); $i <= min($totalPaginas, $pagina + 2); $i++): ?>
                                <li class="page-item <?= $i === $pagina ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= urlPaginaBusqueda($i) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            <?php if ($pagina < $totalPaginas): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= urlPaginaBusqueda($pagina + 1) ?>">›</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                <?php endif; ?>
            </div>

            

        </div>
    </div>
</main>

<!-- Filtros en mobile -->
<div class="offcanvas offcanvas-bottom filtros-offcanvas" tabindex="-1" id="filtrosMobile" role="dialog" aria-labelledby="filtrosMobileLabel">
    <div class="offcanvas-header">
        <h2 class="offcanvas-title h5" id="filtrosMobileLabel">Filtros</h2>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar filtros"></button>
    </div>
    <div class="offcanvas-body">
        <?php
        $idPrefijo = 'filtrosMobile';
        require __DIR__ . '/../components/filtrosBusqueda.php';
        ?>
    </div>
</div>

<?php require_once './src/views/layouts/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
            new bootstrap.Tooltip(el);
        });
    });
</script>

<script>
    const filtroBtn          = document.getElementById('filtroBtn');
    const filtrosColumna     = document.getElementById('filtrosColumna');
    const resultadosColumna  = document.getElementById('resultadosColumna');
    const filtrosContenido   = document.querySelector('.filtros-contenido');

    let abierto = false;

    filtroBtn.addEventListener('click', function () {
        abierto = !abierto;

        filtroBtn.setAttribute('aria-expanded', abierto ? 'true' : 'false');

        if (abierto) {
            filtrosColumna.classList.remove('col-1');
            filtrosColumna.classList.add('col-3');
            filtrosColumna.classList.add('filtros-sidebar-abierto');
            filtrosColumna.classList.add('bg-white', 'shadow');

            resultadosColumna.classList.remove('col-lg-11');
            resultadosColumna.classList.add('col-lg-9');

            filtroBtn.classList.remove('justify-content-center');
            filtroBtn.classList.add('justify-content-end');

        } else {
            // Cerrar: ocultar contenido ANTES de achicar la columna
            filtrosColumna.classList.remove('filtros-sidebar-abierto');
            filtrosColumna.classList.remove('bg-white', 'shadow');

            // Esperar que el opacity transition termine (150ms) antes de achicar
            setTimeout(() => {
                filtrosColumna.classList.remove('col-3');
                filtrosColumna.classList.add('col-1');

                resultadosColumna.classList.remove('col-lg-9');
                resultadosColumna.classList.add('col-lg-11');

                filtroBtn.classList.remove('justify-content-end');
                filtroBtn.classList.add('justify-content-center');
            }, 150);
        }
    });

</script>


</body>
</html>