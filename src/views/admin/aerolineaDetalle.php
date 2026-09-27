<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../controllers/AerolineaController.php';

$ctrl = new AerolineaController($link);

$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    redirect('index.php?pagina=aerolinea&seccion=listado');
}

$aero = $ctrl->obtenerAerolineaPorId($id);

if (!$aero) {
    http_response_code(404);
    echo '<p class="text-center py-5 text-muted">Aerolínea no encontrada.</p>';
    return;
}
?>

<div>
    <nav class="breadcrumbCont" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Inicio</a></li>
            <li class="breadcrumb-item"><a href="<?= url('index.php?pagina=aerolinea&seccion=listado') ?>">Aerolínea</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($aero['nombreAerolinea']) ?></li>
        </ol>
    </nav>

    <div class="contForm col-12 d-flex flex-column p-2 gap-3">

        <!-- Header -->
        <div class="bg-white shadow rounded-3 p-4">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">

                <div class="d-flex gap-3 align-items-center">
                    <?php if ($aero['logoUrl']): ?>
                        <img src="<?= $aero['logoUrl'] ?>" class="rounded-circle border" style="width:70px;height:70px;object-fit:contain;" alt="logo">
                    <?php else: ?>
                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width:70px;height:70px;">
                            <span class="text-white fw-bold text-uppercase fs-4"><?= htmlspecialchars(substr($aero['codigoIATA'], 0, 2)) ?></span>
                        </div>
                    <?php endif; ?>

                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h2 class="fw-bold m-0"><?= htmlspecialchars($aero['nombreAerolinea']) ?></h2>
                            <span class="badge bg-light text-secondary border"><?= htmlspecialchars($aero['codigoIATA']) ?></span>
                        </div>
                        <?php if (!empty($aero['descripcion'])): ?>
                            <p class="text-muted m-0 mt-1" style="font-size:0.9rem;"><?= htmlspecialchars($aero['descripcion']) ?></p>
                        <?php endif; ?>

                        <div class="d-flex flex-wrap gap-3 mt-2" style="font-size:0.85rem;">
                            <span><span class="text-muted">País:</span> <strong><?= htmlspecialchars($aero['codPais']) ?></strong></span>
                            <span><span class="text-muted">Código IATA:</span> <strong><?= htmlspecialchars($aero['codigoIATA']) ?></strong></span>
                            <span><span class="text-muted">CEO:</span> <strong><?= $aero['ceoNombre'] ? htmlspecialchars($aero['ceoNombre'] . ' ' . $aero['ceoApellido']) : '—' ?></strong></span>
                            <span><span class="text-muted">Email:</span> <strong><?= htmlspecialchars($aero['email'] ?? '—') ?></strong></span>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-column align-items-end gap-2">
                    <span class="badge text-bg-<?= $aero['activo'] ? 'success' : 'secondary' ?> px-3 py-2">
                        <?= $aero['activo'] ? '● Activa' : '● Inactiva' ?>
                    </span>
                    <a href="<?= url('index.php?pagina=aerolinea&seccion=editar&id=' . $aero['idAerolinea']) ?>"
                       class="btn btn-primary btn-sm">
                        Editar aerolínea
                    </a>
                    <a href="<?= url('index.php?pagina=aerolinea&seccion=listado') ?>"
                       class="btn btn-outline-secondary btn-sm">
                        ← Volver
                    </a>
                </div>

            </div>
        </div>

        <!-- Placeholders para cuando tengas los datos -->
        <div class="row g-3">
            <div class="col-md-4">
                <div class="bg-white shadow rounded-3 p-4 text-center text-muted">
                    <p class="fw-semibold m-0">Vuelos activos</p>
                    <p class="fs-3 fw-bold text-primary m-0">—</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="bg-white shadow rounded-3 p-4 text-center text-muted">
                    <p class="fw-semibold m-0">Promociones vigentes</p>
                    <p class="fs-3 fw-bold text-primary m-0">—</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="bg-white shadow rounded-3 p-4 text-center text-muted">
                    <p class="fw-semibold m-0">Pasajeros transportados</p>
                    <p class="fs-3 fw-bold text-primary m-0">—</p>
                </div>
            </div>
        </div>

    </div>
</div>