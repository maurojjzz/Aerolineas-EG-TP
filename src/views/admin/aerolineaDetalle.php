<?php
date_default_timezone_set('America/Argentina/Buenos_Aires');
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../controllers/AerolineaController.php';

$ctrl = new AerolineaController($link);

$id = (int)($_GET['id'] ?? 0);
if (!$id) redirect('index.php?pagina=aerolinea&seccion=listado');

$aero = $ctrl->obtenerAerolineaPorId($id);
if (!$aero) {
    http_response_code(404);
    echo '<p class="text-center py-5 text-muted">Aerolínea no encontrada.</p>';
    return;
}

// Tab activo: resumen por defecto
$tabActiva = $_GET['tab'] ?? 'resumen';
if (!in_array($tabActiva, ['resumen', 'vuelos', 'promociones'], true)) {
    $tabActiva = 'resumen';
}

// ── Helpers de formato ────────────────────────────────────────────────────

function adminAeroNumero($valor): string {
    return number_format((float)$valor, 0, ',', '.');
}

function adminAeroMoneda($valor): string {
    return '$' . number_format((float)$valor, 0, ',', '.');
}

/**
 * Devuelve [icono, texto, color] para la tendencia vs mes anterior.
 */
function adminAeroTendencia($actual, $anterior, bool $porcentaje = false): array {
    $actual   = (float)$actual;
    $anterior = (float)$anterior;

    if ($porcentaje) {
        $pct = $anterior > 0
            ? (int)round((($actual - $anterior) / $anterior) * 100)
            : ($actual > 0 ? 100 : 0);

        if ($pct > 0) {
            return ['flecha-up.png', '+' . $pct . '% vs mes anterior', 'text-success'];
        }
        if ($pct < 0) {
            return ['flecha-down.png', $pct . '% vs mes anterior', 'text-danger'];
        }
        return ['simbolo-igual.png', 'Sin variación vs mes anterior', 'text-muted'];
    }

    $diff = (int)round($actual - $anterior);

    if ($diff > 0) {
        return ['flecha-up.png', '+' . adminAeroNumero($diff) . ' vs mes anterior', 'text-success'];
    }
    if ($diff < 0) {
        return ['flecha-down.png', adminAeroNumero($diff) . ' vs mes anterior', 'text-danger'];
    }
    return ['simbolo-igual.png', 'Igual que el mes anterior', 'text-muted'];
}

function adminAeroBadgeEstadoPromo(string $estado): string {
    return match ($estado) {
        'Aprobada' => 'text-bg-success',
        'Denegada' => 'text-bg-danger',
        default    => 'text-bg-warning',
    };
}

function adminAeroBadgeVigenciaPromo(array $p): string {
    if ($p['estado'] === 'Denegada') {
        return '<span class="badge text-bg-danger">Denegada</span>';
    }
    if ($p['estado'] === 'Pendiente') {
        return '<span class="badge text-bg-warning">Pendiente</span>';
    }

    $hoy = new DateTime('today');
    $fin = new DateTime($p['fechaFin']);

    if ($fin < $hoy) {
        return '<span class="badge text-bg-danger">Vencida</span>';
    }

    $diff = $hoy->diff($fin)->days;
    if ($diff <= 7) {
        return '<span class="badge text-bg-warning">Quedan ' . $diff . ' día' . ($diff === 1 ? '' : 's') . '</span>';
    }

    return '<span class="badge text-bg-success">Vigente</span>';
}

function adminAeroTextoAlcancePromo(array $p): string {
    if (!empty($p['idVuelo'])) {
        $origen  = $p['codigoOrigen'] ?? '—';
        $destino = $p['codigoDestino'] ?? '—';
        return "Vuelo #{$p['idVuelo']} ($origen → $destino)";
    }
    if (!empty($p['codigoOrigen']) && !empty($p['codigoDestino'])) {
        return "Ruta {$p['codigoOrigen']} → {$p['codigoDestino']}";
    }
    return 'Aerolínea completa';
}

function adminAeroUrlDetalle(int $id, string $tab, array $extra = []): string {
    $params = array_merge([
        'pagina'  => 'aerolinea',
        'seccion' => 'detalle',
        'id'      => $id,
        'tab'     => $tab,
    ], $extra);

    return url('index.php?' . http_build_query($params));
}

function adminAeroUrlPagVuelos(int $id, int $pag, ?int $porPagina = null): string {
    $params = $_GET;
    $params['pagina']  = 'aerolinea';
    $params['seccion'] = 'detalle';
    $params['id']      = $id;
    $params['tab']     = 'vuelos';
    $params['pagVuelos'] = $pag;

    if ($porPagina !== null) {
        $params['porPaginaVuelos'] = $porPagina;
    }

    return 'index.php?' . http_build_query($params);
}

function adminAeroUrlPagPromos(int $id, int $pag, ?int $porPagina = null): string {
    $params = $_GET;
    $params['pagina']  = 'aerolinea';
    $params['seccion'] = 'detalle';
    $params['id']      = $id;
    $params['tab']     = 'promociones';
    $params['pagPromos'] = $pag;

    if ($porPagina !== null) {
        $params['porPaginaPromos'] = $porPagina;
    }

    return 'index.php?' . http_build_query($params);
}

// ── Datos del resumen ─────────────────────────────────────────────────────

$metricas              = $ctrl->obtenerMetricasDetalle($id);
$resumen               = $ctrl->obtenerResumenAerolinea($id);
$vuelosMasVendidos     = $ctrl->obtenerVuelosMasVendidos($id, 5);
$promocionesVigentes   = $ctrl->obtenerPromocionesVigentes($id, 3);
$tramosMasRecaudados   = $ctrl->obtenerTramosMasRecaudados($id, 5);

$cardsResumen = [
    [
        'titulo'    => 'Vuelos totales',
        'valor'     => adminAeroNumero($resumen['vuelos']['total']),
        'icono'     => 'avion.png',
        'tendencia' => adminAeroTendencia($resumen['vuelos']['actual'], $resumen['vuelos']['anterior']),
    ],
    [
        'titulo'    => 'Promociones activas',
        'valor'     => adminAeroNumero($resumen['promociones']['total']),
        'icono'     => 'etiqueta.png',
        'tendencia' => adminAeroTendencia($resumen['promociones']['actual'], $resumen['promociones']['anterior']),
    ],
    [
        'titulo'    => 'Pasajeros transportados',
        'valor'     => adminAeroNumero($resumen['pasajeros']['total']),
        'icono'     => 'ceo.png',
        'tendencia' => adminAeroTendencia($resumen['pasajeros']['actual'], $resumen['pasajeros']['anterior']),
    ],
    [
        'titulo'    => 'Ventas totales',
        'valor'     => adminAeroMoneda($resumen['ventas']['total']),
        'icono'     => 'ceo.png',
        'tendencia' => adminAeroTendencia($resumen['ventas']['actual'], $resumen['ventas']['anterior'], true),
    ],
];

// ── Datos del tab Vuelos ──────────────────────────────────────────────────

$porPaginaPermitidos = [5, 10, 25, 50];

$pagVuelos = max(1, (int)($_GET['pagVuelos'] ?? 1));
$porPaginaVuelosRaw = (int)($_GET['porPaginaVuelos'] ?? 10);
$porPaginaVuelos = in_array($porPaginaVuelosRaw, $porPaginaPermitidos, true) ? $porPaginaVuelosRaw : 10;
$busquedaVuelos = trim($_GET['busquedaVuelos'] ?? '');
$estadoVuelos = in_array($_GET['estadoVuelos'] ?? '', ['proximos', 'pasados'], true)
    ? $_GET['estadoVuelos']
    : '';

$resultadoVuelos = $ctrl->listarVuelosAerolineaPaginado($id, $pagVuelos, $porPaginaVuelos, $busquedaVuelos, $estadoVuelos);
$totalPaginasVuelos = max(1, (int)$resultadoVuelos['totalPaginas']);

if ($pagVuelos > $totalPaginasVuelos) {
    $pagVuelos = $totalPaginasVuelos;
    $resultadoVuelos = $ctrl->listarVuelosAerolineaPaginado($id, $pagVuelos, $porPaginaVuelos, $busquedaVuelos, $estadoVuelos);
}

$vuelosTab       = $resultadoVuelos['data'];
$totalVuelosTab  = (int)$resultadoVuelos['total'];
$desdeVuelos     = $totalVuelosTab === 0 ? 0 : ($pagVuelos - 1) * $porPaginaVuelos + 1;
$hastaVuelos     = min($pagVuelos * $porPaginaVuelos, $totalVuelosTab);

// ── Datos del tab Promociones ─────────────────────────────────────────────

$pagPromos = max(1, (int)($_GET['pagPromos'] ?? 1));
$porPaginaPromosRaw = (int)($_GET['porPaginaPromos'] ?? 10);
$porPaginaPromos = in_array($porPaginaPromosRaw, $porPaginaPermitidos, true) ? $porPaginaPromosRaw : 10;
$busquedaPromos = trim($_GET['busquedaPromos'] ?? '');
$estadoPromos = in_array($_GET['estadoPromos'] ?? '', ['Pendiente', 'Aprobada', 'Denegada'], true)
    ? $_GET['estadoPromos']
    : '';

$resultadoPromos = $ctrl->listarPromocionesAerolineaPaginado($id, $pagPromos, $porPaginaPromos, $busquedaPromos, $estadoPromos);
$totalPaginasPromos = max(1, (int)$resultadoPromos['totalPaginas']);

if ($pagPromos > $totalPaginasPromos) {
    $pagPromos = $totalPaginasPromos;
    $resultadoPromos = $ctrl->listarPromocionesAerolineaPaginado($id, $pagPromos, $porPaginaPromos, $busquedaPromos, $estadoPromos);
}

$promosTab       = $resultadoPromos['data'];
$totalPromosTab  = (int)$resultadoPromos['total'];
$desdePromos     = $totalPromosTab === 0 ? 0 : ($pagPromos - 1) * $porPaginaPromos + 1;
$hastaPromos     = min($pagPromos * $porPaginaPromos, $totalPromosTab);

?>

<div>
    <nav class="breadcrumbCont" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Inicio</a></li>
            <li class="breadcrumb-item"><a href="<?= url('index.php?pagina=aerolinea&seccion=listado') ?>">Listado aerolíneas</a></li>
            <li class="breadcrumb-item active">Aerolinea: <?= htmlspecialchars($aero['nombreAerolinea']) ?></li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center gap-2 my-2">
        <a href="<?= url('index.php?pagina=aerolinea&seccion=listado') ?>" class="btn btn-sm d-flex flex-row gap-1 btn-atras ms-3">
            <img src="<?= url('public/img/icons/flecha-izquierda.png') ?>" class="img-fluid" style="width: 18px;height: 18px;" alt="icono flecha atras">
            <span class="text-dark fw-semibold text-decoration-none text-uppercase">Volver a listado</span>
        </a>
        <a href="<?= url('index.php?pagina=aerolinea&seccion=editar&id=' . $aero['idAerolinea']) ?>" class="btn btn-primary btn-sm me-4 fw-semibold fs-6 text-decoration-none text-capitalize">Editar Aerolinea</a>
    </div>

    <div class="contForm col-12 d-flex flex-column p-2 gap-3">
        <div class="p-0 m-0 d-flex flex-column flex-md-row justify-content-between gap-4">

            <div class="bg-white shadow rounded-3 p-3 mainInfoAerolinea">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                    <div class="d-flex flex-column flex-sm-row gap-3 align-items-center">
                        <?php if ($aero['logoUrl']): ?>
                            <img src="<?= $aero['logoUrl'] ?>" class="rounded-circle border" style="width:150px;height:150px;object-fit:contain;" alt="logo">
                        <?php else: ?>
                            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width:150px;height:150px;">
                                <span class="text-white fw-bold text-uppercase fs-1"><?= htmlspecialchars(substr($aero['codigoIATA'], 0, 2)) ?></span>
                            </div>
                        <?php endif; ?>

                        <div class="d-flex flex-column flex-grow-1 justify-content-between" style="min-height:152px;">
                            <div class="d-flex align-items-center gap-4 flex-wrap">
                                <h2 class="fw-bold m-0"><?= htmlspecialchars($aero['nombreAerolinea']) ?></h2>
                                <span class="badge text-primary border fs-6 text-uppercase" style="background-color: #E3F1FE;"><?= htmlspecialchars($aero['codigoIATA']) ?></span>
                            </div>

                            <p class="text-muted m-0 mt-1" style="font-size:0.9rem;">
                                <?php if (!empty($aero['descripcion'])): ?>
                                    <?= htmlspecialchars($aero['descripcion']) ?>
                                <?php endif; ?>
                            </p>

                            <div class="d-flex flex-wrap gap-5 mt-2" style="font-size:0.85rem;">
                                <span class="d-flex flex-column gap-1">
                                    <span class="text-muted">País</span>
                                    <strong><?= htmlspecialchars($aero['codPais']) ?></strong>
                                </span>
                                <span class="d-flex flex-column gap-1">
                                    <span class="text-muted">IATA</span>
                                    <strong class="text-uppercase"><?= htmlspecialchars($aero['codigoIATA']) ?></strong>
                                </span>
                                <span class="d-flex flex-column gap-1">
                                    <span class="text-muted">CEO asociado</span>
                                    <strong><?= $aero['ceoNombre'] ? htmlspecialchars($aero['ceoNombre'] . ' ' . $aero['ceoApellido']) : '—' ?></strong>
                                </span>
                                <span class="d-flex flex-column gap-1">
                                    <span class="text-muted">Correo de contacto</span>
                                    <strong><?= htmlspecialchars($aero['email'] ?? '—') ?></strong>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-column p-3 rounded-3 bg-white shadow subInfoAero">
                <p class="d-flex flex-row align-items-center justify-content-between fw-semibold m-0 mb-2">
                    Estado de la aerolinea
                    <span class="badge p-1 px-2 text-bg-<?= $aero['activo'] ? 'success' : 'secondary' ?>">
                        <?= $aero['activo'] ? '● Activa' : '● Inactiva' ?>
                    </span>
                </p>
                <div class="d-flex flex-column justify-content-evenly gap-2 h-100">
                    <p class="d-flex flex-row align-items-center justify-content-between fw-semibold m-0 mb-2" style="font-size:0.95rem;">
                        Fecha de creacion
                        <span class="badge p-1 px-2 text-dark fw-normal">
                            <?= date('d/m/Y', strtotime($aero['fechaCreacion'])) ?>
                        </span>
                    </p>

                    <p class="d-flex flex-row align-items-center justify-content-between fw-semibold m-0 mb-2" style="font-size:0.95rem;">
                        Vuelos activos
                        <span class="badge p-1 px-2 text-dark fw-normal">
                            <?= adminAeroNumero($metricas['vuelosActivos']) ?>
                        </span>
                    </p>

                    <p class="d-flex flex-row align-items-center justify-content-between fw-semibold m-0 mb-2" style="font-size:0.95rem;">
                        Promociones vigentes
                        <span class="badge p-1 px-2 text-dark fw-normal">
                            <?= adminAeroNumero($metricas['promocionesVigentes']) ?>
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="bg-white shadow rounded-3">

            <ul class="nav nav-tabs px-3 d-flex align-items-center justify-content-center justify-content-md-start" id="detalleTab">
                <li class="nav-item">
                    <button class="nav-link <?= $tabActiva === 'resumen' ? 'active' : '' ?>"
                            data-bs-toggle="tab" data-bs-target="#tab-resumen" data-tab="resumen">
                        Resumen
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link <?= $tabActiva === 'vuelos' ? 'active' : '' ?>"
                            data-bs-toggle="tab" data-bs-target="#tab-vuelos" data-tab="vuelos">
                        Vuelos
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link <?= $tabActiva === 'promociones' ? 'active' : '' ?>"
                            data-bs-toggle="tab" data-bs-target="#tab-promociones" data-tab="promociones">
                        Promociones
                    </button>
                </li>
            </ul>

            <div class="tab-content p-4">

                <!-- Tab Resumen -->
                <div class="tab-pane fade <?= $tabActiva === 'resumen' ? 'show active' : '' ?>" id="tab-resumen">

                    <div class="d-flex flex-column flex-md-row justify-content-md-between row p-0 m-0 gap-3 gap-md-0 mb-3">
                        <?php foreach ($cardsResumen as $card): ?>
                            <div class="col-md-3">
                                <div class="bg-white shadow rounded-3 py-2 px-4 overflow-hidden">
                                    <div class="d-flex flex-row align-items-center justify-content-start gap-3">
                                        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:55px;height:55px;background-color:#E3F0FE;">
                                            <img src="<?= url('public/img/icons/placeholdersCards/' . $card['icono']) ?>" class="img-fluid" alt="<?= htmlspecialchars($card['titulo']) ?>" style="width:32px;height:32px;object-fit:contain;">
                                        </div>
                                        <div class="d-flex flex-column justify-content-center p-0 m-0">
                                            <p class="fw-semibold m-0 p-0 text-muted" style="font-size:14px;"><?= htmlspecialchars($card['titulo']) ?></p>
                                            <p class="fs-3 fw-bold text-dark m-0 p-0"><?= $card['valor'] ?></p>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-row justify-content-start p-0 m-0 gap-2 mt-1">
                                        <img src="<?= url('public/img/icons/placeholdersCards/' . $card['tendencia'][0]) ?>" class="img-fluid" alt="tendencia" style="width:20px;height:20px;object-fit:contain;">
                                        <p class="fw-normal m-0 p-0 <?= $card['tendencia'][2] ?>" style="font-size:14px;"><?= htmlspecialchars($card['tendencia'][1]) ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="row g-3">

                        <!-- Vuelos más vendidos -->
                        <div class="col-12 col-lg-4">
                            <div class="bg-white border rounded-3 h-100">
                                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="<?= url('public/img/icons/placeholdersCards/avion.png') ?>" style="width:18px;height:18px;object-fit:contain;" alt="">
                                        <span class="fw-semibold" style="font-size:0.9rem;">Vuelos más vendidos</span>
                                    </div>
                                    <a href="<?= adminAeroUrlDetalle($id, 'vuelos') ?>"
                                       class="btn btn-outline-secondary btn-sm" style="font-size:0.75rem;">
                                        Ver todos
                                    </a>
                                </div>
                                <div class="px-3 py-2">
                                    <table class="table table-sm table-hover align-middle mb-0" style="font-size:0.82rem;">
                                        <thead>
                                            <tr class="text-muted">
                                                <th class="fw-medium border-0 ps-0">RUTA</th>
                                                <th class="fw-medium border-0 text-end">VUELOS</th>
                                                <th class="fw-medium border-0 text-end">PASAJEROS</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($vuelosMasVendidos)): ?>
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted py-4 border-0">
                                                        Todavía no hay ventas registradas para esta aerolínea.
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($vuelosMasVendidos as $v): ?>
                                                    <tr>
                                                        <td class="ps-0 border-0">
                                                            <div class="d-flex align-items-center gap-2">
                                                                <img src="<?= url('public/img/icons/placeholdersCards/avion.png') ?>" style="width:14px;height:14px;object-fit:contain;opacity:0.4;" alt="">
                                                                <?= htmlspecialchars($v['ruta']) ?>
                                                            </div>
                                                        </td>
                                                        <td class="text-end border-0"><?= adminAeroNumero($v['vuelos']) ?></td>
                                                        <td class="text-end border-0"><?= adminAeroNumero($v['pasajeros']) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Promociones activas -->
                        <div class="col-12 col-lg-4">
                            <div class="bg-white border rounded-3 h-100">
                                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="<?= url('public/img/icons/placeholdersCards/etiqueta.png') ?>" style="width:18px;height:18px;object-fit:contain;" alt="">
                                        <span class="fw-semibold" style="font-size:0.9rem;">Promociones activas</span>
                                    </div>
                                    <a href="<?= adminAeroUrlDetalle($id, 'promociones') ?>"
                                       class="btn btn-outline-secondary btn-sm" style="font-size:0.75rem;">
                                        Ver todas
                                    </a>
                                </div>
                                <div class="px-3 py-2 d-flex flex-column gap-2">
                                    <?php if (empty($promocionesVigentes)): ?>
                                        <p class="text-muted text-center py-4 m-0">
                                            No hay promociones activas en este momento.
                                        </p>
                                    <?php else: ?>
                                        <?php foreach ($promocionesVigentes as $p): ?>
                                            <div class="d-flex justify-content-between align-items-start gap-2 border-bottom pb-2">
                                                <div>
                                                    <p class="fw-semibold m-0" style="font-size:0.82rem;"><?= htmlspecialchars($p['nombre']) ?></p>
                                                    <p class="text-muted m-0" style="font-size:0.75rem;">
                                                        <?= date('d/m/Y', strtotime($p['fechaInicio'])) ?> - <?= date('d/m/Y', strtotime($p['fechaFin'])) ?>
                                                    </p>
                                                </div>
                                                <?= adminAeroBadgeVigenciaPromo($p) ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Tramos más recaudados -->
                        <div class="col-12 col-lg-4">
                            <div class="bg-white border rounded-3 h-100">
                                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="<?= url('public/img/icons/placeholdersCards/ceo.png') ?>" style="width:18px;height:18px;object-fit:contain;" alt="">
                                        <span class="fw-semibold" style="font-size:0.9rem;">Tramos más recaudados</span>
                                    </div>
                                </div>
                                <div class="px-3 py-2">
                                    <table class="table table-sm table-hover align-middle mb-0" style="font-size:0.82rem;">
                                        <thead>
                                            <tr class="text-muted">
                                                <th class="fw-medium border-0 ps-0">RUTA</th>
                                                <th class="fw-medium border-0 text-end">RECAUDADO</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($tramosMasRecaudados)): ?>
                                                <tr>
                                                    <td colspan="2" class="text-center text-muted py-4 border-0">
                                                        Todavía no hay recaudación registrada para esta aerolínea.
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($tramosMasRecaudados as $t): ?>
                                                    <tr>
                                                        <td class="ps-0 border-0">
                                                            <div class="d-flex align-items-center gap-2">
                                                                <img src="<?= url('public/img/icons/placeholdersCards/avion.png') ?>" style="width:14px;height:14px;object-fit:contain;opacity:0.4;" alt="">
                                                                <?= htmlspecialchars($t['ruta']) ?>
                                                            </div>
                                                        </td>
                                                        <td class="text-end border-0 fw-semibold text-primary"><?= adminAeroMoneda($t['recaudado']) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

                <!-- Tab Vuelos -->
                <div class="tab-pane fade <?= $tabActiva === 'vuelos' ? 'show active' : '' ?>" id="tab-vuelos">

                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3 mb-3">
                        <h4 class="m-0 fs-5 fw-bold">Vuelos de la aerolínea</h4>

                        <form method="GET" action="index.php" class="d-flex flex-column flex-sm-row gap-2 align-items-stretch">
                            <input type="hidden" name="pagina" value="aerolinea">
                            <input type="hidden" name="seccion" value="detalle">
                            <input type="hidden" name="id" value="<?= $id ?>">
                            <input type="hidden" name="tab" value="vuelos">
                            <input type="hidden" name="porPaginaVuelos" value="<?= $porPaginaVuelos ?>">

                            <input type="text"
                                   name="busquedaVuelos"
                                   class="form-control form-control-sm"
                                   placeholder="Buscar origen, destino o código..."
                                   autocomplete="off"
                                   value="<?= htmlspecialchars($busquedaVuelos) ?>"
                                   style="min-width: 220px;">

                            <select name="estadoVuelos" class="form-select form-select-sm" style="min-width: 140px;">
                                <option value="" <?= $estadoVuelos === '' ? 'selected' : '' ?>>Todos</option>
                                <option value="proximos" <?= $estadoVuelos === 'proximos' ? 'selected' : '' ?>>Próximos</option>
                                <option value="pasados" <?= $estadoVuelos === 'pasados' ? 'selected' : '' ?>>Pasados</option>
                            </select>

                            <button type="submit" class="btn btn-sm btn-outline-primary text-nowrap">Filtrar</button>
                            <a href="<?= adminAeroUrlDetalle($id, 'vuelos') ?>" class="btn btn-sm btn-outline-secondary text-nowrap">Limpiar</a>
                        </form>
                    </div>

                    <div class="table-responsive tablaContenedor">
                        <table class="table table-hover align-middle border-top mt-2">
                            <thead>
                                <tr>
                                    <th scope="col" class="fw-bold">ORIGEN</th>
                                    <th scope="col" class="fw-bold">DESTINO</th>
                                    <th scope="col" class="fw-bold d-none d-md-table-cell">SALIDA</th>
                                    <th scope="col" class="fw-bold d-none d-md-table-cell">LLEGADA</th>
                                    <th scope="col" class="fw-bold d-none d-sm-table-cell">ASIENTOS</th>
                                    <th scope="col" class="fw-bold d-none d-sm-table-cell">DISPONIBLES</th>
                                    <th scope="col" class="fw-bold text-end">PRECIO</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($vuelosTab)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            No hay vuelos registrados con los filtros seleccionados.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($vuelosTab as $v): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <img src="<?= url('public/img/icons/placeholdersCards/avion.png') ?>"
                                                         style="width:16px;height:16px;object-fit:contain;opacity:0.5;" alt="">
                                                    <span class="fw-medium"><?= htmlspecialchars($v['origenVuelo']) ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <img src="<?= url('public/img/icons/placeholdersCards/avion.png') ?>"
                                                         style="width:16px;height:16px;object-fit:contain;opacity:0.5;" alt="">
                                                    <span class="fw-medium"><?= htmlspecialchars($v['destinoVuelo']) ?></span>
                                                </div>
                                            </td>
                                            <td class="d-none d-md-table-cell">
                                                <?= date('d/m/Y', strtotime($v['fechaHoraSalidaVuelo'])) ?>
                                                <span class="text-muted ms-1" style="font-size:0.85rem;">
                                                    <?= date('H:i', strtotime($v['fechaHoraSalidaVuelo'])) ?>
                                                </span>
                                            </td>
                                            <td class="d-none d-md-table-cell">
                                                <?= date('d/m/Y', strtotime($v['fechaHoraLlegadaVuelo'])) ?>
                                                <span class="text-muted ms-1" style="font-size:0.85rem;">
                                                    <?= date('H:i', strtotime($v['fechaHoraLlegadaVuelo'])) ?>
                                                </span>
                                            </td>
                                            <td class="d-none d-sm-table-cell"><?= (int)$v['asientosTotales'] ?></td>
                                            <td class="d-none d-sm-table-cell"><?= (int)$v['asientosDisponibles'] ?></td>
                                            <td class="fw-semibold text-end">
                                                $<?= number_format((float)$v['precioVuelo'], 2, ',', '.') ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="border-top px-3 py-3">
                        <div class="d-flex flex-column flex-sm-row justify-content-center justify-content-sm-between align-items-center flex-wrap gap-3">

                            <span class="text-muted" style="font-size:0.8rem;">
                                Mostrando <strong><?= $desdeVuelos ?></strong> a <strong><?= $hastaVuelos ?></strong> de <strong><?= $totalVuelosTab ?></strong> vuelos
                            </span>

                            <nav aria-label="Paginación">
                                <ul class="pagination pagination-sm m-0 gap-1">

                                    <li class="page-item <?= $pagVuelos <= 1 ? 'disabled' : '' ?>">
                                        <a class="page-link rounded-2 border-0 <?= $pagVuelos <= 1 ? 'text-muted' : '' ?>"
                                           href="<?= $pagVuelos > 1 ? adminAeroUrlPagVuelos($id, 1) : '#' ?>">«</a>
                                    </li>
                                    <li class="page-item <?= $pagVuelos <= 1 ? 'disabled' : '' ?>">
                                        <a class="page-link rounded-2 border-0 <?= $pagVuelos <= 1 ? 'text-muted' : '' ?>"
                                           href="<?= $pagVuelos > 1 ? adminAeroUrlPagVuelos($id, $pagVuelos - 1) : '#' ?>">‹</a>
                                    </li>

                                    <?php
                                    $rangoVuelos = 2;
                                    $inicioVuelos = max(1, $pagVuelos - $rangoVuelos);
                                    $finVuelos = min($totalPaginasVuelos, $pagVuelos + $rangoVuelos);
                                    ?>

                                    <?php if ($inicioVuelos > 1): ?>
                                        <li class="page-item disabled">
                                            <span class="page-link border-0 text-muted rounded-2">…</span>
                                        </li>
                                    <?php endif; ?>

                                    <?php for ($i = $inicioVuelos; $i <= $finVuelos; $i++): ?>
                                        <li class="page-item">
                                            <a class="page-link rounded-2 border-0 <?= $i === $pagVuelos ? 'active-pag' : 'text-secondary' ?>"
                                               href="<?= adminAeroUrlPagVuelos($id, $i) ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($finVuelos < $totalPaginasVuelos): ?>
                                        <li class="page-item disabled">
                                            <span class="page-link border-0 text-muted rounded-2">…</span>
                                        </li>
                                    <?php endif; ?>

                                    <li class="page-item <?= $pagVuelos >= $totalPaginasVuelos ? 'disabled' : '' ?>">
                                        <a class="page-link rounded-2 border-0 <?= $pagVuelos >= $totalPaginasVuelos ? 'text-muted' : '' ?>"
                                           href="<?= $pagVuelos < $totalPaginasVuelos ? adminAeroUrlPagVuelos($id, $pagVuelos + 1) : '#' ?>">›</a>
                                    </li>
                                    <li class="page-item <?= $pagVuelos >= $totalPaginasVuelos ? 'disabled' : '' ?>">
                                        <a class="page-link rounded-2 border-0 <?= $pagVuelos >= $totalPaginasVuelos ? 'text-muted' : '' ?>"
                                           href="<?= $pagVuelos < $totalPaginasVuelos ? adminAeroUrlPagVuelos($id, $totalPaginasVuelos) : '#' ?>">»</a>
                                    </li>

                                </ul>
                            </nav>

                            <div class="d-flex align-items-center gap-2">
                                <span class="text-muted" style="font-size:0.8rem;">Por página:</span>
                                <select
                                    class="form-select form-select-sm border-0 bg-light rounded-2"
                                    style="width:auto; font-size:0.8rem; cursor:pointer;"
                                    onchange="window.location.href=this.value"
                                >
                                    <?php foreach ($porPaginaPermitidos as $op): ?>
                                        <option value="<?= adminAeroUrlPagVuelos($id, 1, $op) ?>" <?= $op === $porPaginaVuelos ? 'selected' : '' ?>>
                                            <?= $op ?> por página
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                        </div>
                    </div>

                </div>

                <!-- Tab Promociones -->
                <div class="tab-pane fade <?= $tabActiva === 'promociones' ? 'show active' : '' ?>" id="tab-promociones">

                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3 mb-3">
                        <h4 class="m-0 fs-5 fw-bold">Promociones de la aerolínea</h4>

                        <form method="GET" action="index.php" class="d-flex flex-column flex-sm-row gap-2 align-items-stretch">
                            <input type="hidden" name="pagina" value="aerolinea">
                            <input type="hidden" name="seccion" value="detalle">
                            <input type="hidden" name="id" value="<?= $id ?>">
                            <input type="hidden" name="tab" value="promociones">
                            <input type="hidden" name="porPaginaPromos" value="<?= $porPaginaPromos ?>">

                            <input type="text"
                                   name="busquedaPromos"
                                   class="form-control form-control-sm"
                                   placeholder="Buscar código o nombre..."
                                   autocomplete="off"
                                   value="<?= htmlspecialchars($busquedaPromos) ?>"
                                   style="min-width: 220px;">

                            <select name="estadoPromos" class="form-select form-select-sm" style="min-width: 140px;">
                                <option value="" <?= $estadoPromos === '' ? 'selected' : '' ?>>Todos los estados</option>
                                <option value="Pendiente" <?= $estadoPromos === 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                <option value="Aprobada" <?= $estadoPromos === 'Aprobada' ? 'selected' : '' ?>>Aprobada</option>
                                <option value="Denegada" <?= $estadoPromos === 'Denegada' ? 'selected' : '' ?>>Denegada</option>
                            </select>

                            <button type="submit" class="btn btn-sm btn-outline-primary text-nowrap">Filtrar</button>
                            <a href="<?= adminAeroUrlDetalle($id, 'promociones') ?>" class="btn btn-sm btn-outline-secondary text-nowrap">Limpiar</a>
                        </form>
                    </div>

                    <div class="table-responsive tablaContenedor">
                        <table class="table table-hover align-middle border-top mt-2">
                            <thead>
                                <tr>
                                    <th scope="col" class="text-center fw-bold">CODIGO</th>
                                    <th scope="col" class="fw-bold">NOMBRE</th>
                                    <th scope="col" class="fw-bold text-center">DESCUENTO</th>
                                    <th scope="col" class="fw-bold d-none d-md-table-cell">ALCANCE</th>
                                    <th scope="col" class="fw-bold d-none d-md-table-cell">VIGENCIA</th>
                                    <th scope="col" class="fw-bold d-none d-md-table-cell">ESTADO VIGENCIA</th>
                                    <th scope="col" class="fw-bold d-none d-sm-table-cell">ESTADO</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($promosTab)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            No hay promociones registradas con los filtros seleccionados.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($promosTab as $p): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center justify-content-center gap-2">
                                                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center" style="width:30px; height:30px; flex-shrink:0;">
                                                        <span class="text-white fw-bold text-uppercase" style="font-size:0.6rem;"><?= htmlspecialchars(substr($p['codigo'], 0, 2)) ?></span>
                                                    </div>
                                                    <span class="text-uppercase fw-medium"><?= htmlspecialchars($p['codigo']) ?></span>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($p['nombre']) ?></td>
                                            <td class="text-center fw-bold text-success"><?= (float)$p['descuentoPorcentaje'] ?>%</td>
                                            <td class="d-none d-md-table-cell text-muted" style="font-size:0.8rem;">
                                                <?= htmlspecialchars(adminAeroTextoAlcancePromo($p)) ?>
                                            </td>
                                            <td class="d-none d-md-table-cell text-muted" style="font-size:0.85rem;">
                                                <?= date('d/m/Y', strtotime($p['fechaInicio'])) ?> - <?= date('d/m/Y', strtotime($p['fechaFin'])) ?>
                                            </td>
                                            <td class="d-none d-md-table-cell">
                                                <?= adminAeroBadgeVigenciaPromo($p) ?>
                                            </td>
                                            <td class="d-none d-sm-table-cell">
                                                <span class="badge <?= adminAeroBadgeEstadoPromo($p['estado']) ?>"><?= htmlspecialchars($p['estado']) ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="border-top px-3 py-3">
                        <div class="d-flex flex-column flex-sm-row justify-content-center justify-content-sm-between align-items-center flex-wrap gap-3">

                            <span class="text-muted" style="font-size:0.8rem;">
                                Mostrando <strong><?= $desdePromos ?></strong> a <strong><?= $hastaPromos ?></strong> de <strong><?= $totalPromosTab ?></strong> promociones
                            </span>

                            <nav aria-label="Paginación">
                                <ul class="pagination pagination-sm m-0 gap-1">

                                    <li class="page-item <?= $pagPromos <= 1 ? 'disabled' : '' ?>">
                                        <a class="page-link rounded-2 border-0 <?= $pagPromos <= 1 ? 'text-muted' : '' ?>"
                                           href="<?= $pagPromos > 1 ? adminAeroUrlPagPromos($id, 1) : '#' ?>">«</a>
                                    </li>
                                    <li class="page-item <?= $pagPromos <= 1 ? 'disabled' : '' ?>">
                                        <a class="page-link rounded-2 border-0 <?= $pagPromos <= 1 ? 'text-muted' : '' ?>"
                                           href="<?= $pagPromos > 1 ? adminAeroUrlPagPromos($id, $pagPromos - 1) : '#' ?>">‹</a>
                                    </li>

                                    <?php
                                    $rangoPromos = 2;
                                    $inicioPromos = max(1, $pagPromos - $rangoPromos);
                                    $finPromos = min($totalPaginasPromos, $pagPromos + $rangoPromos);
                                    ?>

                                    <?php if ($inicioPromos > 1): ?>
                                        <li class="page-item disabled">
                                            <span class="page-link border-0 text-muted rounded-2">…</span>
                                        </li>
                                    <?php endif; ?>

                                    <?php for ($i = $inicioPromos; $i <= $finPromos; $i++): ?>
                                        <li class="page-item">
                                            <a class="page-link rounded-2 border-0 <?= $i === $pagPromos ? 'active-pag' : 'text-secondary' ?>"
                                               href="<?= adminAeroUrlPagPromos($id, $i) ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($finPromos < $totalPaginasPromos): ?>
                                        <li class="page-item disabled">
                                            <span class="page-link border-0 text-muted rounded-2">…</span>
                                        </li>
                                    <?php endif; ?>

                                    <li class="page-item <?= $pagPromos >= $totalPaginasPromos ? 'disabled' : '' ?>">
                                        <a class="page-link rounded-2 border-0 <?= $pagPromos >= $totalPaginasPromos ? 'text-muted' : '' ?>"
                                           href="<?= $pagPromos < $totalPaginasPromos ? adminAeroUrlPagPromos($id, $pagPromos + 1) : '#' ?>">›</a>
                                    </li>
                                    <li class="page-item <?= $pagPromos >= $totalPaginasPromos ? 'disabled' : '' ?>">
                                        <a class="page-link rounded-2 border-0 <?= $pagPromos >= $totalPaginasPromos ? 'text-muted' : '' ?>"
                                           href="<?= $pagPromos < $totalPaginasPromos ? adminAeroUrlPagPromos($id, $totalPaginasPromos) : '#' ?>">»</a>
                                    </li>

                                </ul>
                            </nav>

                            <div class="d-flex align-items-center gap-2">
                                <span class="text-muted" style="font-size:0.8rem;">Por página:</span>
                                <select
                                    class="form-select form-select-sm border-0 bg-light rounded-2"
                                    style="width:auto; font-size:0.8rem; cursor:pointer;"
                                    onchange="window.location.href=this.value"
                                >
                                    <?php foreach ($porPaginaPermitidos as $op): ?>
                                        <option value="<?= adminAeroUrlPagPromos($id, 1, $op) ?>" <?= $op === $porPaginaPromos ? 'selected' : '' ?>>
                                            <?= $op ?> por página
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                        </div>
                    </div>

                </div>

            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll('#detalleTab [data-tab]').forEach(function (btn) {
        btn.addEventListener('shown.bs.tab', function () {
            const url = new URL(window.location.href);
            url.searchParams.set('tab', this.dataset.tab);
            history.replaceState({}, '', url);
        });
    });
});
</script>
