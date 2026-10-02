<?php
date_default_timezone_set('America/Argentina/Buenos_Aires');

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../controllers/AerolineaController.php';

// El CEO tiene su aerolínea en la sesión
$idAerolinea = $_SESSION['usuario']['idAerolinea'] ?? 0;
if (!$idAerolinea) {
    redirect('index.php?pagina=login');
}

$ctrl = new AerolineaController($link);
$aero = $ctrl->obtenerAerolineaPorId($idAerolinea);

if (!$aero) {
    redirect('index.php?pagina=login');
}

$tabActiva = $_GET['tab'] ?? 'resumen';
$flechaImg   = 'flecha-up.png';
$flechaTexto = '+2 vs mes anterior';
$flechaColor = 'text-success';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CEO - <?= htmlspecialchars($aero['nombreAerolinea']) ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= url('public/css/admin.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/layout/headerAdmin.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/components/tablaLista.css') ?>?v=<?= time() ?>">
</head>
<body>

<div class="d-flex min-vh-100 row g-0 m-0 p-2 pe-2">

    <aside class="admin-sidebar text-white d-none d-md-block col-md-3 col-xxl-2 pe-1">
        <?php require __DIR__ . '/../layouts/sidebarAdmin.php'; ?>
    </aside>

    <div class="admin-main col-12 col-md-9 col-xxl-10 bg-light rounded-3 overflow-hidden">

        <header class="admin-header">
            <?php require __DIR__ . '/../layouts/headerAdmin.php'; ?>
        </header>

        <main class="container-fluid d-flex flex-column gap-3 p-2 admin-content">

            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                    <li class="breadcrumb-item active">Mi Aerolínea</li>
                </ol>
            </nav>

            <div class="contForm col-12 d-flex flex-column p-2 gap-3">

                <!-- Header aerolínea -->
                <div class="p-0 m-0 d-flex flex-column flex-md-row justify-content-between gap-4">

                    <div class="bg-white shadow rounded-3 p-3 mainInfoAerolinea">
                        <div class="d-flex flex-column flex-sm-row gap-3 align-items-center">
                            <?php if ($aero['logoUrl']): ?>
                                <img src="<?= $aero['logoUrl'] ?>" class="rounded-circle border"
                                    style="width:150px;height:150px;object-fit:contain;" alt="logo">
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
                                    <h2 class="fw-bold m-0"><?= htmlspecialchars($aero['nombreAerolinea']) ?></h2>
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
                                <span class="badge p-1 px-2 text-dark fw-normal">25</span>
                            </p>
                            <p class="d-flex flex-row align-items-center justify-content-between fw-semibold m-0 mb-2"
                               style="font-size:0.95rem;">
                                Promociones vigentes
                                <span class="badge p-1 px-2 text-dark fw-normal">5</span>
                            </p>
                        </div>
                    </div>

                </div>

                <!-- Tabs -->
                <div class="bg-white shadow rounded-3">

                    <ul class="nav nav-tabs px-3 d-flex align-items-center justify-content-center justify-content-md-start"
                        id="detalleTab">
                        <li class="nav-item">
                            <button class="nav-link <?= $tabActiva === 'resumen' ? 'active' : '' ?>"
                                    data-bs-toggle="tab" data-bs-target="#tab-resumen">Resumen</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link <?= $tabActiva === 'vuelos' ? 'active' : '' ?>"
                                    data-bs-toggle="tab" data-bs-target="#tab-vuelos">Vuelos</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link <?= $tabActiva === 'promociones' ? 'active' : '' ?>"
                                    data-bs-toggle="tab" data-bs-target="#tab-promociones">Promociones</button>
                        </li>
                    </ul>

                    <div class="tab-content p-4">

                        <!-- Tab Resumen -->
                        <div class="tab-pane fade <?= $tabActiva === 'resumen' ? 'show active' : '' ?>" id="tab-resumen">

                            <div class="d-flex flex-column flex-md-row justify-content-md-between row p-0 m-0 gap-3 gap-md-0 mb-3">
                                <?php
                                $cards = [
                                    ['icon' => 'avion.png',    'label' => 'Vuelos totales',         'valor' => '25',         'extra' => $flechaTexto],
                                    ['icon' => 'etiqueta.png', 'label' => 'Promociones activas',     'valor' => '3',          'extra' => $flechaTexto],
                                    ['icon' => 'ceo.png',      'label' => 'Pasajeros transportados', 'valor' => '58.420',     'extra' => $flechaTexto],
                                    ['icon' => 'ceo.png',      'label' => 'Ventas totales',          'valor' => '$2.840.500', 'extra' => '+18% vs mes anterior'],
                                ];
                                foreach ($cards as $c): ?>
                                <div class="col-md-3">
                                    <div class="bg-white shadow rounded-3 py-2 px-4 overflow-hidden">
                                        <div class="d-flex flex-row align-items-center justify-content-start gap-3">
                                            <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                                                style="width:55px;height:55px;background-color:#E3F0FE;">
                                                <img src="<?= url('public/img/icons/placeholdersCards/' . $c['icon']) ?>"
                                                    class="img-fluid" style="width:32px;height:32px;object-fit:contain;" alt="">
                                            </div>
                                            <div class="d-flex flex-column justify-content-center p-0 m-0">
                                                <p class="fw-semibold m-0 p-0 text-muted" style="font-size:14px;"><?= $c['label'] ?></p>
                                                <p class="fs-3 fw-bold text-dark m-0 p-0"><?= $c['valor'] ?></p>
                                            </div>
                                        </div>
                                        <div class="d-flex flex-row justify-content-start p-0 m-0 gap-2 mt-1">
                                            <img src="<?= url('public/img/icons/placeholdersCards/' . $flechaImg) ?>"
                                                class="img-fluid" style="width:20px;height:20px;object-fit:contain;" alt="">
                                            <p class="fw-normal m-0 p-0 <?= $flechaColor ?>" style="font-size:14px;"><?= $c['extra'] ?></p>
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
                                            <span class="fw-semibold" style="font-size:0.9rem;">Vuelos más vendidos</span>
                                            <button class="btn btn-outline-secondary btn-sm" style="font-size:0.75rem;">Ver todos</button>
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
                                                    <?php foreach ([
                                                        ['ruta' => 'Buenos Aires → Madrid',   'vuelos' => 8, 'pasajeros' => '1.240'],
                                                        ['ruta' => 'Buenos Aires → Santiago', 'vuelos' => 6, 'pasajeros' => '980'],
                                                        ['ruta' => 'Córdoba → Lima',          'vuelos' => 5, 'pasajeros' => '760'],
                                                        ['ruta' => 'Rosario → México DF',     'vuelos' => 4, 'pasajeros' => '620'],
                                                        ['ruta' => 'Buenos Aires → Bogotá',   'vuelos' => 4, 'pasajeros' => '580'],
                                                    ] as $v): ?>
                                                    <tr>
                                                        <td class="ps-0 border-0"><?= $v['ruta'] ?></td>
                                                        <td class="text-end border-0"><?= $v['vuelos'] ?></td>
                                                        <td class="text-end border-0"><?= $v['pasajeros'] ?></td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Promociones activas -->
                                <div class="col-12 col-lg-4">
                                    <div class="bg-white border rounded-3 h-100">
                                        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                                            <span class="fw-semibold" style="font-size:0.9rem;">Promociones activas</span>
                                            <button class="btn btn-outline-secondary btn-sm" style="font-size:0.75rem;">Ver todas</button>
                                        </div>
                                        <div class="px-3 py-2 d-flex flex-column gap-2">
                                            <?php foreach ([
                                                ['nombre' => '30% de descuento en vuelos internacionales', 'fechas' => '01/11/2024 - 30/11/2024', 'estado' => 'Aprobada',  'color' => 'success'],
                                                ['nombre' => '15% de descuento en primera compra',         'fechas' => '01/10/2024 - 31/12/2024', 'estado' => 'Aprobada',  'color' => 'success'],
                                                ['nombre' => 'Ofertas especiales a México',                'fechas' => '15/11/2024 - 30/11/2024', 'estado' => 'Pendiente', 'color' => 'warning'],
                                            ] as $p): ?>
                                            <div class="d-flex justify-content-between align-items-start gap-2 border-bottom pb-2">
                                                <div>
                                                    <p class="fw-semibold m-0" style="font-size:0.82rem;"><?= $p['nombre'] ?></p>
                                                    <p class="text-muted m-0" style="font-size:0.75rem;"><?= $p['fechas'] ?></p>
                                                </div>
                                                <span class="badge text-bg-<?= $p['color'] ?> flex-shrink-0" style="font-size:0.7rem;">
                                                    <?= $p['estado'] ?>
                                                </span>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tramos más recaudados -->
                                <div class="col-12 col-lg-4">
                                    <div class="bg-white border rounded-3 h-100">
                                        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                                            <span class="fw-semibold" style="font-size:0.9rem;">Tramos más recaudados</span>
                                            <button class="btn btn-outline-secondary btn-sm" style="font-size:0.75rem;">Ver todos</button>
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
                                                    <?php foreach ([
                                                        ['ruta' => 'Buenos Aires → Madrid',   'monto' => '$ 2.400.000'],
                                                        ['ruta' => 'Buenos Aires → Santiago', 'monto' => '$ 1.800.000'],
                                                        ['ruta' => 'Córdoba → Lima',          'monto' => '$ 980.000'],
                                                        ['ruta' => 'Rosario → México DF',     'monto' => '$ 750.000'],
                                                        ['ruta' => 'Buenos Aires → Bogotá',   'monto' => '$ 620.000'],
                                                    ] as $t): ?>
                                                    <tr>
                                                        <td class="ps-0 border-0"><?= $t['ruta'] ?></td>
                                                        <td class="text-end border-0 fw-semibold text-primary"><?= $t['monto'] ?></td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- Tab Vuelos -->
                        <div class="tab-pane fade <?= $tabActiva === 'vuelos' ? 'show active' : '' ?>" id="tab-vuelos">
                            <p class="text-muted text-center py-4">Tabla de vuelos — próximamente</p>
                        </div>

                        <!-- Tab Promociones -->
                        <div class="tab-pane fade <?= $tabActiva === 'promociones' ? 'show active' : '' ?>" id="tab-promociones">
                            <p class="text-muted text-center py-4">Tabla de promociones — próximamente</p>
                        </div>

                    </div>
                </div>

            </div>

        </main>
    </div>
</div>

<?php require __DIR__ . '/../components/menuMobileAdmin.php' ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
<script src="<?= url('public/js/admin/sidebar.js') ?>"></script>

</body>
</html>