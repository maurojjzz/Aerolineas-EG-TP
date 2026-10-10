<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../controllers/UsuarioController.php';

$ctrl = new UsuarioController($link);

$id = (int)($_GET['id'] ?? 0);
if (!$id) redirect('index.php?pagina=usuario&seccion=listado');

$u = $ctrl->obtenerUsuarioPorId($id);
if (!$u) {
    flash_set('error', 'Usuario no encontrado.');
    redirect('index.php?pagina=usuario&seccion=listado');
    return;
}

// Tab activa
$tabActiva = $_GET['tab'] ?? 'resumen';

// Indicador ficticio de actividad/tendencia
$flechaImg = 'flecha-up.png';
$flechaTexto = '+1 vs mes anterior';
$flechaColor = 'text-success';
?>

<div>
    <nav class="breadcrumbCont" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Inicio</a></li>
            <li class="breadcrumb-item"><a href="<?= url('index.php?pagina=usuario&seccion=listado') ?>">Listado de usuarios</a></li>
            <li class="breadcrumb-item active">Usuario: <?= htmlspecialchars($u['nombre'] . ' ' . $u['apellido']) ?></li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center gap-2 my-2">
        <a href="<?= url('index.php?pagina=usuario&seccion=listado') ?>" class="btn btn-sm d-flex flex-row gap-1 btn-atras ms-3">
            <img src="<?= url('public/img/icons/flecha-izquierda.png') ?>" class="img-fluid" style="width: 18px;height: 18px;" alt="icono flecha atras"> 
            <span class="text-dark fw-semibold text-decoration-none text-uppercase">Volver a listado</span>
        </a>
        <a href="<?= url('index.php?pagina=usuario&seccion=editar&id=' . $u['idUsuario']) ?>" class="btn btn-primary btn-sm me-4 fw-semibold fs-6 text-decoration-none text-capitalize">Editar Usuario</a>
    </div>

    <div class="contForm col-12 d-flex flex-column p-2 gap-3">
        <!-- Tarjeta Principal de Información -->
        <div class="p-0 m-0 d-flex flex-column flex-md-row justify-content-between gap-4">

            <div class="bg-white shadow rounded-3 p-3 mainInfoAerolinea flex-grow-1">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                    <div class="d-flex flex-column flex-sm-row gap-3 align-items-center">
                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width:120px;height:120px;">
                            <span class="text-white fw-bold text-uppercase fs-1">
                                <?= htmlspecialchars(substr($u['nombre'], 0, 1) . substr($u['apellido'], 0, 1)) ?>
                            </span>
                        </div>

                        <div class="d-flex flex-column flex-grow-1 justify-content-between">
                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                <h2 class="fw-bold m-0"><?= htmlspecialchars($u['nombre'] . ' ' . $u['apellido']) ?></h2>
                                <span class="badge text-bg-info text-capitalize fs-6"><?= htmlspecialchars($u['rol']) ?></span>
                            </div>

                            <p class="text-muted m-0 mt-1" style="font-size:0.9rem;">
                                <?= htmlspecialchars($u['email']) ?>
                            </p>

                            <div class="d-flex flex-wrap gap-4 mt-3" style="font-size:0.85rem;">
                                <span class="d-flex flex-column gap-1">
                                    <span class="text-muted">Documento</span> 
                                    <strong><?= htmlspecialchars($u['tipoDocumento'] . ' ' . $u['nroDocumento']) ?></strong>
                                </span>
                                <span class="d-flex flex-column gap-1">
                                    <span class="text-muted">Teléfono</span> 
                                    <strong><?= htmlspecialchars($u['telefono'] ?? '—') ?></strong>
                                </span>
                                <?php if (!empty($u['nombreAerolinea'])): ?>
                                    <span class="d-flex flex-column gap-1">
                                        <span class="text-muted">Aerolínea Asociada</span> 
                                        <strong><?= htmlspecialchars($u['nombreAerolinea']) ?></strong>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel Lateral de Estado -->
            <div class="d-flex flex-column p-3 rounded-3 bg-white shadow subInfoAero" style="min-width: 280px;">
                <p class="d-flex flex-row align-items-center justify-content-between fw-semibold m-0 mb-2">
                    Estado de la cuenta
                    <span class="badge p-1 px-2 text-bg-<?= $u['activo'] ? 'success' : 'secondary' ?>">
                        <?= $u['activo'] ? '● Activo' : '● Inactivo' ?>
                    </span>
                </p>
                <div class="d-flex flex-column justify-content-evenly gap-2 h-100">
                    <p class="d-flex flex-row align-items-center justify-content-between fw-semibold m-0 mb-2" style="font-size:0.95rem;">
                        Email Verificado
                        <span class="badge p-1 px-2 text-bg-<?= $u['emailVerificado'] ? 'success' : 'warning' ?>">
                            <?= $u['emailVerificado'] ? 'Verificado' : 'Pendiente' ?>
                        </span>
                    </p>

                    <p class="d-flex flex-row align-items-center justify-content-between fw-semibold m-0 mb-2" style="font-size:0.95rem;">
                        Fecha de registro
                        <span class="badge p-1 px-2 text-dark fw-normal">
                            <?= date('d/m/Y', strtotime($u['fechaCreacion'])) ?>
                        </span>
                    </p>

                    <p class="d-flex flex-row align-items-center justify-content-between fw-semibold m-0 mb-2" style="font-size:0.95rem;">
                        Reservas realizadas
                        <span class="badge p-1 px-2 text-dark fw-normal">
                            4
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Pestañas (Tabs) -->
        <div class="bg-white shadow rounded-3">
            <ul class="nav nav-tabs px-3 d-flex align-items-center justify-content-center justify-content-md-start" id="detalleTab">
                <li class="nav-item">
                    <button class="nav-link <?= $tabActiva === 'resumen' ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#tab-resumen">
                        Resumen
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link <?= $tabActiva === 'reservas' ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#tab-reservas">
                        Historial de Reservas
                    </button>
                </li>
            </ul>

            <div class="tab-content p-4">
                <!-- Tab Resumen -->
                <div class="tab-pane fade <?= $tabActiva === 'resumen' ? 'show active' : '' ?>" id="tab-resumen">
                    <div class="d-flex flex-column flex-md-row justify-content-md-between row p-0 m-0 gap-3 gap-md-0 mb-3">
                        <div class="col-md-4">
                            <div class="bg-white shadow rounded-3 py-2 px-4 overflow-hidden">
                                <div class="d-flex flex-row align-items-center justify-content-start gap-3">
                                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:55px;height:55px;background-color:#E3F0FE;">
                                        <i class="bi bi-ticket-perforated-fill text-primary fs-3"></i>
                                    </div>
                                    <div class="d-flex flex-column justify-content-center p-0 m-0">
                                        <p class="fw-semibold m-0 p-0 text-muted" style="font-size:14px;">Pasajes Comprados</p>
                                        <p class="fs-3 fw-bold text-dark m-0 p-0">4</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="bg-white shadow rounded-3 py-2 px-4 overflow-hidden">
                                <div class="d-flex flex-row align-items-center justify-content-start gap-3">
                                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:55px;height:55px;background-color:#E3F0FE;">
                                        <i class="bi bi-cash-stack text-primary fs-3"></i>
                                    </div>
                                    <div class="d-flex flex-column justify-content-center p-0 m-0">
                                        <p class="fw-semibold m-0 p-0 text-muted" style="font-size:14px;">Total Invertido</p>
                                        <p class="fs-3 fw-bold text-dark m-0 p-0">$ 450.000</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="bg-white shadow rounded-3 py-2 px-4 overflow-hidden">
                                <div class="d-flex flex-row align-items-center justify-content-start gap-3">
                                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:55px;height:55px;background-color:#E3F0FE;">
                                        <i class="bi bi-airplane-engines-fill text-primary fs-3"></i>
                                    </div>
                                    <div class="d-flex flex-column justify-content-center p-0 m-0">
                                        <p class="fw-semibold m-0 p-0 text-muted" style="font-size:14px;">Próximo Vuelo</p>
                                        <p class="fs-5 fw-bold text-dark m-0 p-0">12/11/2026</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Reservas -->
                <div class="tab-pane fade <?= $tabActiva === 'reservas' ? 'show active' : '' ?>" id="tab-reservas">
                    <p class="text-muted text-center py-4">
                        Historial de compras y reservas del usuario — próximamente
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>