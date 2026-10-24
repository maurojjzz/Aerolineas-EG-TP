<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../controllers/PromocionController.php';

$ctrl = new PromocionController($link);

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    redirect('index.php?pagina=promociones&seccion=listado');
}

$promo = $ctrl->obtenerPorId($id);
if (!$promo) {
    flash_set('error', 'Promoción no encontrada.');
    redirect('index.php?pagina=promociones&seccion=listado');
}

$fInicio = !empty($promo['fechaInicio']) ? date('d/m/Y', strtotime($promo['fechaInicio'])) : '—';
$fFin = !empty($promo['fechaFin']) ? date('d/m/Y', strtotime($promo['fechaFin'])) : '—';
$aerolinea = $promo['nombreAerolinea'] ?? '—';
$ceo = trim(($promo['ceoNombre'] ?? '') . ' ' . ($promo['ceoApellido'] ?? ''));
$estado = $promo['estado'] ?? 'Pendiente';

function badgeEstadoPromoDetalle(string $estado): string {
    return match ($estado) {
        'Aprobada' => 'text-bg-success',
        'Denegada' => 'text-bg-danger',
        default    => 'text-bg-warning',
    };
}
?>

<?php require __DIR__ . '/../components/alertToast.php'; ?>

<div>
    <nav class="breadcrumbCont" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <!-- <li class="breadcrumb-item"><a href="<?= url('index.php?pagina=inicio') ?>">Inicio</a></li> -->
            <li class="breadcrumb-item"><a href="<?= url('index.php?pagina=promociones&seccion=listado') ?>">Promociones</a></li>
            <li class="breadcrumb-item active" aria-current="page">Detalle: <?= htmlspecialchars($promo['codigo'] ?? '') ?></li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="m-0 fs-2 fw-bold">Detalle de la Promoción</h1>
    </div>

    <div class="row g-4">
        
        <!-- Columna Izquierda: Información Principal -->
        <div class="col-12 col-lg-8">
            <div class="bg-white shadow rounded-3 p-4 h-100">
                
                <div class="d-flex align-items-center gap-3 mb-4 border-bottom pb-3">
                    <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width:70px; height:70px;">
                        <span class="text-white fw-bold text-uppercase fs-3"><?= htmlspecialchars(substr($promo['codigo'] ?? 'PR', 0, 2)) ?></span>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h2 class="fw-bold m-0 text-uppercase"><?= htmlspecialchars($promo['codigo'] ?? '') ?></h2>
                            <span class="badge <?= badgeEstadoPromoDetalle($estado) ?> fs-6">
                                <?= htmlspecialchars($estado) ?>
                            </span>
                        </div>
                        <p class="text-muted m-0 fs-5"><?= htmlspecialchars($promo['nombre'] ?? '') ?></p>
                    </div>
                </div>

                <div class="mb-4">
                    <h2 class="h5 fw-semibold text-muted mb-2" style="font-size:0.9rem; text-transform:uppercase; letter-spacing:1px;">Descripción</h2>
                    <p class="text-dark m-0" style="line-height:1.6;">
                        <?= !empty($promo['descripcion']) ? nl2br(htmlspecialchars($promo['descripcion'])) : 'Sin descripción detallada.' ?>
                    </p>
                </div>

                <div>
                    <h2 class="h5 fw-semibold text-muted mb-2" style="font-size:0.9rem; text-transform:uppercase; letter-spacing:1px;">Condiciones</h2>
                    <div class="bg-light rounded-3 p-3">
                        <p class="text-muted m-0" style="font-size:0.9rem; line-height:1.5;">
                            <?= !empty($promo['condiciones']) ? nl2br(htmlspecialchars($promo['condiciones'])) : 'Sin condiciones especificadas.' ?>
                        </p>
                    </div>
                </div>

            </div>
        </div>

        <!-- Columna Derecha: Resumen y Datos -->
        <div class="col-12 col-lg-4">
            
            <!-- Tarjeta de Descuento -->
            <div class="bg-white shadow rounded-3 p-4 mb-4 text-center">
                <p class="text-muted fw-semibold m-0 mb-2">Descuento Aplicado</p>
                <p class="display-4 fw-bold text-success m-0"><?= (float)($promo['descuentoPorcentaje'] ?? 0) ?>%</p>
            </div>

            <!-- Tarjeta de Fechas -->
            <div class="bg-white shadow rounded-3 p-4 mb-4">
                <h2 class="h5 fw-semibold text-muted mb-3" style="font-size:0.9rem; text-transform:uppercase; letter-spacing:1px;">Vigencia</h2>
                
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted"><i class="bi bi-calendar-event me-1"></i> Inicio</span>
                    <span class="fw-semibold"><?= $fInicio ?></span>
                </div>
                
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted"><i class="bi bi-calendar-x me-1"></i> Fin</span>
                    <span class="fw-semibold"><?= $fFin ?></span>
                </div>
            </div>

            <!-- Tarjeta de Aerolínea / CEO -->
            <div class="bg-white shadow rounded-3 p-4">
                <h2 class="h5 fw-semibold text-muted mb-3" style="font-size:0.9rem; text-transform:uppercase; letter-spacing:1px;">Información del Creador</h2>
                
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Aerolínea</span>
                    <span class="fw-semibold text-primary"><?= htmlspecialchars($aerolinea) ?></span>
                </div>
                
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted">CEO</span>
                    <span class="fw-semibold"><?= $ceo !== '' ? htmlspecialchars($ceo) : '—' ?></span>
                </div>
            </div>

        </div>
    </div>
</div>