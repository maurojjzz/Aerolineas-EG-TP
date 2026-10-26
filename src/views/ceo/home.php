<?php
date_default_timezone_set('America/Argentina/Buenos_Aires');

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../controllers/AerolineaController.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../components/aerolineaResumenHelpers.php';

// El CEO tiene su aerolínea en la sesión
$idAerolinea = aerolineaCEO();

if (!$idAerolinea) {
    redirect('index.php?pagina=login');
}

$ctrl = new AerolineaController($link);
$aero = $ctrl->obtenerAerolineaPorId($idAerolinea);

if (!$aero) {
    redirect('index.php?pagina=login');
}

// ── Datos reales del resumen ──────────────────────────────────────────────
$metricas            = $ctrl->obtenerMetricasDetalle($idAerolinea);
$resumen             = $ctrl->obtenerResumenAerolinea($idAerolinea);
$vuelosMasVendidos   = $ctrl->obtenerVuelosMasVendidos($idAerolinea, 5);
$promocionesVigentes = $ctrl->obtenerPromocionesVigentes($idAerolinea, 3);
$tramosMasRecaudados = $ctrl->obtenerTramosMasRecaudados($idAerolinea, 5);

$cards = [
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($aero['nombreAerolinea']) ?> — Panel de CEO — Volaré</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= url('public/css/admin.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/layout/headerAdmin.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/components/tablaLista.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= url('public/css/bootstrap-icons.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/accesibilidad.css') ?>">
</head>
<body>
<?php require __DIR__ . '/../layouts/skipLink.php'; ?>

<div class="d-flex min-vh-100 row g-0 m-0 p-2 pe-2">

    <!-- Sidebar con los anchos correctos por defecto -->
    <aside class="admin-sidebar text-white d-none d-md-block col-md-3 col-xxl-2 pe-1">
        <?php require __DIR__ . '/../layouts/sidebarAdmin.php'; ?>
    </aside>

    <div class="admin-main col-12 col-md-9 col-xxl-10 bg-light rounded-3 overflow-hidden">
        <header class="admin-header">
            <?php require __DIR__ . '/../layouts/headerAdmin.php'; ?>
        </header>

        <main id="contenido" tabindex="-1" class="container-fluid d-flex flex-column gap-3 p-2 admin-content">

            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <!-- <li class="breadcrumb-item"><a href="#">Inicio</a></li> -->
                    <li class="breadcrumb-item active" aria-current="page">Mi Aerolínea</li>
                </ol>
            </nav>

            <div class="contForm col-12 d-flex flex-column p-2 gap-3">

                <!-- Header aerolínea -->
                <div class="p-0 m-0 d-flex flex-column flex-md-row justify-content-between gap-4">

                    <div class="bg-white shadow rounded-3 p-3 mainInfoAerolinea">
                        <div class="d-flex flex-column flex-sm-row gap-3 align-items-center">
                            <?php if ($aero['logoUrl']): ?>
                                <img src="<?= $aero['logoUrl'] ?>" class="rounded-circle border"
                                    style="width:150px;height:150px;object-fit:contain;"
                                    alt="Logo de <?= htmlspecialchars($aero['nombreAerolinea']) ?>">
                            <?php else: ?>
                                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center flex-shrink-0"
                                    style="width:150px;height:150px;">
                                    <span class="text-white fw-bold text-uppercase fs-1">
                                        <?= htmlspecialchars(substr($aero['codigoIATA'], 0, 2)) ?>
                                    </span>
                                </div>
                            <?php endif; ?>

                            <div class="d-flex flex-column flex-grow-1 justify-content-between" style="min-height:152px;">
                                <div class="d-flex align-items-center gap-4 flex-wrap">
                                    <h1 class="fw-bold m-0"><?= htmlspecialchars($aero['nombreAerolinea']) ?></h1>
                                    <span class="badge text-primary border fs-6 text-uppercase"
                                        style="background-color:#E3F1FE;">
                                        <?= htmlspecialchars($aero['codigoIATA']) ?>
                                    </span>
                                </div>

                                <?php if (!empty($aero['descripcion'])): ?>
                                    <p class="text-muted m-0 mt-1" style="font-size:0.9rem;">
                                        <?= htmlspecialchars($aero['descripcion']) ?>
                                    </p>
                                <?php endif; ?>

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
                                        <span class="text-muted">Correo de contacto</span>
                                        <strong><?= htmlspecialchars($aero['email'] ?? '—') ?></strong>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-column p-3 rounded-3 bg-white shadow subInfoAero">
                        <p class="d-flex flex-row align-items-center justify-content-between fw-semibold m-0 mb-2">
                            Estado de la aerolínea
                            <span class="badge p-1 px-2 text-bg-<?= $aero['activo'] ? 'success' : 'secondary' ?>">
                                <?= $aero['activo'] ? '● Activa' : '● Inactiva' ?>
                            </span>
                        </p>
                        <div class="d-flex flex-column justify-content-evenly gap-2 h-100">
                            <p class="d-flex flex-row align-items-center justify-content-between fw-semibold m-0 mb-2"
                               style="font-size:0.95rem;">
                                Fecha de creación
                                <span class="badge p-1 px-2 text-dark fw-normal">
                                    <?= date('d/m/Y', strtotime($aero['fechaCreacion'])) ?>
                                </span>
                            </p>
                            <p class="d-flex flex-row align-items-center justify-content-between fw-semibold m-0 mb-2"
                               style="font-size:0.95rem;">
                                Vuelos activos
                                <span class="badge p-1 px-2 text-dark fw-normal">
                                    <?= adminAeroNumero($metricas['vuelosActivos']) ?>
                                </span>
                            </p>
                            <p class="d-flex flex-row align-items-center justify-content-between fw-semibold m-0 mb-2"
                               style="font-size:0.95rem;">
                                Promociones vigentes
                                <span class="badge p-1 px-2 text-dark fw-normal">
                                    <?= adminAeroNumero($metricas['promocionesVigentes']) ?>
                                </span>
                            </p>
                        </div>
                    </div>

                </div>

                <!-- Resumen -->
                <div class="bg-white shadow rounded-3 p-4">

                    <div class="d-flex flex-column flex-md-row justify-content-md-between row p-0 m-0 gap-3 gap-md-0 mb-3">
                        <?php foreach ($cards as $card): ?>
                            <div class="col-12 col-sm-6 col-xxl-3">
                                <div class="bg-white shadow rounded-3 py-2 px-3 overflow-hidden h-100">
                                    <div class="d-flex flex-row align-items-center justify-content-start gap-3">
                                        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                                            style="width:55px;height:55px;background-color:#E3F0FE;">
                                            <img src="<?= url('public/img/icons/placeholdersCards/' . $card['icono']) ?>"
                                                class="img-fluid" style="width:32px;height:32px;object-fit:contain;" alt="Icono de <?= htmlspecialchars($card['titulo']) ?>">
                                        </div>
                                        <div class="d-flex flex-column justify-content-center p-0 m-0">
                                            <p class="fw-semibold m-0 p-0 text-muted" style="font-size:14px;"><?= htmlspecialchars($card['titulo']) ?></p>
                                            <p class="fs-4 fw-bold text-dark m-0 p-0 text-break"><?= $card['valor'] ?></p>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-row justify-content-start p-0 m-0 gap-2 mt-1">
                                        <img src="<?= url('public/img/icons/placeholdersCards/' . $card['tendencia'][0]) ?>"
                                            class="img-fluid" style="width:20px;height:20px;object-fit:contain;" alt="Icono de tendencia">
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
                                        <img src="<?= url('public/img/icons/placeholdersCards/avion.png') ?>" style="width:18px;height:18px;object-fit:contain;" alt="Icono de avión">
                                        <span class="fw-semibold" style="font-size:0.9rem;">Vuelos más vendidos</span>
                                    </div>
                                </div>
                                <div class="px-3 py-2">
                                    <table class="table table-sm table-hover align-middle mb-0 tabla-resumen-vuelos" style="font-size:0.82rem;">
                                        <caption class="visually-hidden">Vuelos más vendidos</caption>
                                        <thead>
                                            <tr class="text-muted">
                                                <th scope="col" class="fw-medium border-0 ps-0">RUTA</th>
                                                <th scope="col" class="fw-medium border-0 text-end">VUELOS</th>
                                                <th scope="col" class="fw-medium border-0 text-end">PASAJEROS</th>
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
                                                                <img src="<?= url('public/img/icons/placeholdersCards/avion.png') ?>" style="width:14px;height:14px;object-fit:contain;opacity:0.4;" alt="Icono de avión">
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
                                        <img src="<?= url('public/img/icons/placeholdersCards/etiqueta.png') ?>" style="width:18px;height:18px;object-fit:contain;" alt="Icono de etiqueta">
                                        <span class="fw-semibold" style="font-size:0.9rem;">Promociones activas</span>
                                    </div>
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
                                        <img src="<?= url('public/img/icons/placeholdersCards/ceo.png') ?>" style="width:18px;height:18px;object-fit:contain;" alt="Icono de CEO">
                                        <span class="fw-semibold" style="font-size:0.9rem;">Tramos más recaudados</span>
                                    </div>
                                </div>
                                <div class="px-3 py-2">
                                    <table class="table table-sm table-hover align-middle mb-0 tabla-resumen-recaudacion" style="font-size:0.82rem;">
                                        <caption class="visually-hidden">Tramos más recaudados</caption>
                                        <thead>
                                            <tr class="text-muted">
                                                <th scope="col" class="fw-medium border-0 ps-0">RUTA</th>
                                                <th scope="col" class="fw-medium border-0 text-end">RECAUDADO</th>
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
                                                                <img src="<?= url('public/img/icons/placeholdersCards/avion.png') ?>" style="width:14px;height:14px;object-fit:contain;opacity:0.4;" alt="Icono de avión">
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

            </div>

        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

<?php require __DIR__ . '/../components/menuMobileAdmin.php' ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
<script src="<?= url('public/js/admin/sidebar.js') ?>"></script>

</body>
</html>
