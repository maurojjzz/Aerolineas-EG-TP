<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../controllers/PromocionController.php';

$ctrl = new PromocionController($link);

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    redirect('index.php?pagina=promociones&seccion=listado');
}

$promo = $ctrl->obtenerPorId($id); // Asegurate de que tu controlador tenga este método (o el equivalente que use tu sistema)
if (!$promo) {
    http_response_code(404);
    echo '<p class="text-center py-5 text-muted">Promoción no encontrada.</p>';
    return;
}

// Formateo de fechas y datos
$fInicio = !empty($promo['fechaInicio']) ? date('d/m/Y', strtotime($promo['fechaInicio'])) : '—';
$fFin = !empty($promo['fechaFin']) ? date('d/m/Y', strtotime($promo['fechaFin'])) : '—';
$aerolinea = $promo['nombreAerolinea'] ?? ($promo['codigoIATA'] ?? '—');
$ceo = trim(($promo['ceoNombre'] ?? '') . ' ' . ($promo['ceoApellido'] ?? ''));

function badgeEstadoPromoDetalle(string $estado): string {
    return match ($estado) {
        'Aprobada' => 'text-bg-success',
        'Denegada' => 'text-bg-danger',
        default    => 'text-bg-warning',
    };
}
?>

<div>
    <nav class="breadcrumbCont" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Inicio</a></li>
            <li class="breadcrumb-item"><a href="<?= url('index.php?pagina=promociones&seccion=listado') ?>">Gestión de Promociones</a></li>
            <li class="breadcrumb-item active">Promoción: <?= htmlspecialchars($promo['codigo'] ?? 'Detalle') ?></li>
        </ol>
    </nav>

    <div class="contForm col-12 d-flex flex-column p-2 gap-3">
        <div class="p-0 m-0 d-flex flex-column flex-md-row justify-content-between gap-4">

            <!-- Información Principal de la Promoción -->
            <div class="bg-white shadow rounded-3 p-3 flex-grow-1">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                    <div class="d-flex flex-column flex-sm-row gap-3 align-items-center w-100">
                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width:120px; height:120px;">
                            <span class="text-white fw-bold text-uppercase fs-2"><?= htmlspecialchars(substr($promo['codigo'] ?? 'PR', 0, 2)) ?></span>
                        </div>

                        <div class="d-flex flex-column flex-grow-1 justify-content-between" style="min-height:120px;">
                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                <h2 class="fw-bold m-0 text-uppercase"><?= htmlspecialchars($promo['codigo'] ?? '') ?></h2>
                                <span class="badge <?= badgeEstadoPromoDetalle($promo['estado'] ?? 'Pendiente') ?> fs-6">
                                    <?= htmlspecialchars($promo['estado'] ?? 'Pendiente') ?>
                                </span>
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
                                    <span class="text-muted">CEO responsable</span> 
                                    <strong><?= $ceo !== '' ? htmlspecialchars($ceo) : '—' ?></strong>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subinformación / Vigencia -->
            <div class="d-flex flex-column p-3 rounded-3 bg-white shadow" style="min-width: 280px;">
                <p class="d-flex flex-row align-items-center justify-content-between fw-semibold m-0 mb-3">
                    Vigencia de la promo
                </p>
                <div class="d-flex flex-column justify-content-evenly gap-2 h-100" style="font-size:0.9rem;">
                    <div class="d-flex flex-row align-items-center justify-content-between">
                        <span class="text-muted">Fecha de inicio</span>
                        <span class="fw-semibold"><?= $fInicio ?></span>
                    </div>
                    <div class="d-flex flex-row align-items-center justify-content-between">
                        <span class="text-muted">Fecha de fin</span>
                        <span class="fw-semibold"><?= $fFin ?></span>
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