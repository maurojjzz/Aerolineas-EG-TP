<?php
date_default_timezone_set('America/Argentina/Buenos_Aires');

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../controllers/PromocionController.php';

requireRol('ceo');

$ctrl = new PromocionController($link);

$idPromo = (int)($_GET['id'] ?? 0);
// obtenerPorIdCEO devuelve la promoción SOLO si es de la aerolínea del CEO logueado
$promo = $idPromo > 0 ? $ctrl->obtenerPorIdCEO($idPromo) : null;

if (!$promo) {
    flash_set('error', 'La promoción no existe o no pertenece a tu aerolínea.');
    redirect('index.php?pagina=ceo&seccion=promociones');
    exit;
}

$volver    = url('index.php?pagina=ceo&seccion=promociones');
$estado    = $promo['estado'] ?? 'Pendiente';
$fInicio   = !empty($promo['fechaInicio']) ? date('d/m/Y', strtotime($promo['fechaInicio'])) : '—';
$fFin      = !empty($promo['fechaFin'])    ? date('d/m/Y', strtotime($promo['fechaFin']))    : '—';
$fCreacion = !empty($promo['fechaCreacion']) ? date('d/m/Y H:i', strtotime($promo['fechaCreacion'])) : '—';
$aerolinea = $promo['nombreAerolinea'] ?? ($promo['codigoIATA'] ?? '—');
$creador   = trim(($promo['ceoNombre'] ?? '') . ' ' . ($promo['ceoApellido'] ?? ''));

function badgeEstadoPromoCeoDetalle(string $estado): string {
    return match ($estado) {
        'Aprobada' => 'text-bg-success',
        'Denegada' => 'text-bg-danger',
        default    => 'text-bg-warning',
    };
}

// Mensaje según el estado, para que el CEO sepa en qué etapa está
$avisoEstado = match ($estado) {
    'Aprobada' => ['alert-success', 'Esta promoción fue aprobada por el administrador.'],
    'Denegada' => ['alert-danger',  'Esta promoción fue denegada por el administrador.'],
    default    => ['alert-warning', 'Esta promoción está pendiente de revisión por el administrador.'],
};
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CEO - Detalle de Promoción</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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

        <main class="container-fluid d-flex flex-column gap-3 p-3 admin-content">

<?php require __DIR__ . '/../components/alertToast.php'; ?>
<div>
    <nav class="breadcrumbCont" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= url('index.php?pagina=ceo') ?>">Inicio</a></li>
            <li class="breadcrumb-item"><a href="<?= $volver ?>">Promociones</a></li>
            <li class="breadcrumb-item active" aria-current="page">Promoción: <?= htmlspecialchars($promo['codigo'] ?? 'Detalle') ?></li>
        </ol>
    </nav>

    <div class="contForm col-12 d-flex flex-column p-2 gap-3">

        <div class="alert <?= $avisoEstado[0] ?> m-0 py-2" role="alert" style="font-size:0.9rem;">
            <?= $avisoEstado[1] ?>
        </div>

        <div class="p-0 m-0 d-flex flex-column flex-md-row justify-content-between gap-4">

            <!-- Información principal -->
            <div class="bg-white shadow rounded-3 p-3 flex-grow-1">
                <div class="d-flex flex-column flex-sm-row gap-3 align-items-center w-100">
                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width:120px; height:120px;">
                        <span class="text-white fw-bold text-uppercase fs-2"><?= htmlspecialchars(substr($promo['codigo'] ?? 'PR', 0, 2)) ?></span>
                    </div>

                    <div class="d-flex flex-column flex-grow-1 justify-content-between" style="min-height:120px;">
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <h2 class="fw-bold m-0 text-uppercase"><?= htmlspecialchars($promo['codigo'] ?? '') ?></h2>
                            <span class="badge <?= badgeEstadoPromoCeoDetalle($estado) ?> fs-6"><?= htmlspecialchars($estado) ?></span>
                        </div>

                        <p class="text-dark fw-semibold m-0 mt-1" style="font-size:1.1rem;">
                            <?= htmlspecialchars($promo['nombre'] ?? '') ?>
                        </p>

                        <p class="text-muted m-0" style="font-size:0.9rem;">
                            <?= !empty($promo['descripcion']) ? htmlspecialchars($promo['descripcion']) : 'Sin descripción detallada.' ?>
                        </p>

                        <div class="d-flex flex-wrap gap-5 mt-3" style="font-size:0.85rem;">
                            <span class="d-flex flex-column gap-1">
                                <span class="text-muted">Descuento</span>
                                <strong class="text-success fs-6"><?= (float)($promo['descuentoPorcentaje'] ?? 0) ?>%</strong>
                            </span>
                            <span class="d-flex flex-column gap-1">
                                <span class="text-muted">Aerolínea</span>
                                <strong class="text-primary"><?= htmlspecialchars($aerolinea) ?></strong>
                            </span>
                            <span class="d-flex flex-column gap-1">
                                <span class="text-muted">Creada por</span>
                                <strong><?= $creador !== '' ? htmlspecialchars($creador) : '—' ?></strong>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vigencia y condiciones -->
            <div class="d-flex flex-column p-3 rounded-3 bg-white shadow" style="min-width: 280px;">
                <p class="fw-semibold m-0 mb-3">Vigencia de la promo</p>
                <div class="d-flex flex-column justify-content-evenly gap-2 h-100" style="font-size:0.9rem;">
                    <div class="d-flex flex-row align-items-center justify-content-between">
                        <span class="text-muted">Fecha de inicio</span>
                        <span class="fw-semibold"><?= $fInicio ?></span>
                    </div>
                    <div class="d-flex flex-row align-items-center justify-content-between">
                        <span class="text-muted">Fecha de fin</span>
                        <span class="fw-semibold"><?= $fFin ?></span>
                    </div>
                    <div class="d-flex flex-row align-items-center justify-content-between">
                        <span class="text-muted">Cargada el</span>
                        <span class="fw-semibold"><?= $fCreacion ?></span>
                    </div>
                    <hr class="text-muted my-2">
                    <div>
                        <span class="text-muted d-block mb-1">Condiciones:</span>
                        <p class="text-dark m-0" style="font-size:0.8rem; line-height: 1.4;">
                            <?= !empty($promo['condiciones']) ? htmlspecialchars($promo['condiciones']) : 'Sin condiciones especificadas.' ?>
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= url('public/js/admin/sidebar.js') ?>"></script>
</body>
</html>