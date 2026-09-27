<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../controllers/AerolineaController.php';

$ctrl = new AerolineaController($link);

$id = (int)($_GET['id'] ?? 0);
if (!$id) redirect('index.php?pagina=aerolinea&seccion=listado');

$aero = $ctrl->obtenerAerolineaPorId($id);
if (!$aero) {
    http_response_code(404);
    echo '<p class="text-center py-5 text-muted">Aerolínea no encontrada.</p>'; // CAMBIAR POR LA TOAST 
    return;
}

// tab activo: resumen por defecto
$tabActiva = $_GET['tab'] ?? 'resumen';

// cards listado


$flechaImg = 'flecha-up.png';
    $flechaTexto = '+2' . ' vs mes anterior';
    $flechaColor = 'text-success';


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
            <img src="<?= url('public/img/icons/flecha-izquierda.png') ?>" class= "img-fluid" style="width: 18px;height: 18px;" alt="icono flecha atras"> 
            <span class="text-dark fw-semibold text-decoration-none text-uppercase" >Volver a listado</span>
        </a>
        <a href="<?= url('index.php?pagina=aerolinea&seccion=editar&id=' . $aero['idAerolinea']) ?>" class="btn btn-primary btn-sm me-4 fw-semibold fs-6 text-decoration-none text-capitalize">Editar Aerolinea</a>
    </div>

    <div class="contForm col-12 d-flex flex-column p-2 gap-3">
        
        <div class=" p-0 m-0 d-flex flex-row  justify-content-between gap-4 ">

            <div class="bg-white shadow rounded-3 p-3 w-75">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 ">
                    <div class="d-flex gap-3 align-items-center">
                        <?php if ($aero['logoUrl']): ?>
                            <img src="<?= $aero['logoUrl'] ?>" class="rounded-circle border " style="width:150px;height:150px;object-fit:contain;" alt="logo">
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

                            <p class="text-muted m-0 mt-1 " style="font-size:0.9rem;">
                            <?php if (!empty($aero['descripcion'])): ?>
                                <?= htmlspecialchars($aero['descripcion']) ?>
                            <?php endif; ?>
                            </p>

                            <div class="d-flex flex-wrap gap-5 mt-2  " style="font-size:0.85rem;">
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
            <div class="d-flex flex-column p-3  rounded-3 bg-white shadow w-25">
                    <p class="d-flex flex-row align-items-center justify-content-between fw-semibold m-0 mb-2">
                        Estado de la aerolinea
                        <span class="badge p-1 px-2 text-bg-<?= $aero['activo'] ? 'success' : 'secondary' ?>">
                            <?= $aero['activo'] ? '● Activa' : '● Inactiva' ?>
                        </span>
                    </p>
                    <div class="d-flex flex-column justify-content-evenly gap-2 h-100">
                            <p class="d-flex flex-row align-items-center justify-content-between fw-semibold m-0 mb-2" style="font-size:0.95rem;">
                                Fecha de creacion
                                <span class="badge p-1 px-2 text-dark fw-normal ?>">
                                    <?=  date('d/m/Y', strtotime($aero['fechaCreacion']))  ?>
                                </span>
                            </p>

                            <p class="d-flex flex-row align-items-center justify-content-between fw-semibold m-0 mb-2" style="font-size:0.95rem;">
                                Vuelos activos
                                <span class="badge p-1 px-2 text-dark fw-normal ?>">
                                    25 <!-- hardcodeado por ahora -->
                            </span>
                            </p>

                            <p class="d-flex flex-row align-items-center justify-content-between fw-semibold m-0 mb-2" style="font-size:0.95rem;">
                                Promociones vigentes
                                <span class="badge p-1 px-2 text-dark fw-normal ?>">
                                    5 <!-- hardcodeado por ahora -->
                                </span>
                            </p>
                    </div>
                
            </div>
        </div>

        <!-- Tabs -->
        <div class="bg-white shadow rounded-3">

            <ul class="nav nav-tabs px-3 pt-3 border-0" id="detalleTab">
                <li class="nav-item">
                    <button class="nav-link <?= $tabActiva === 'resumen' ? 'active' : '' ?>"
                            data-bs-toggle="tab" data-bs-target="#tab-resumen">
                        Resumen
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link <?= $tabActiva === 'vuelos' ? 'active' : '' ?>"
                            data-bs-toggle="tab" data-bs-target="#tab-vuelos">
                        Vuelos
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link <?= $tabActiva === 'promociones' ? 'active' : '' ?>"
                            data-bs-toggle="tab" data-bs-target="#tab-promociones">
                        Promociones
                    </button>
                </li>
            </ul>

            <div class="tab-content p-4">

                <!-- Tab Resumen -->
                <div class="tab-pane fade <?= $tabActiva === 'resumen' ? 'show active' : '' ?>" id="tab-resumen">

                    <div class="d-flex flex-column flex-md-row justify-content-md-between row p-0 m-0 gap-3 gap-md-0 mb-3">
                        <div class="col-md-3">
                            <div class="bg-white shadow rounded-3 py-2 px-4">
                                <div class="d-flex flex-row align-items-center justify-content-start gap-3">
                                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:55px;height:55px;background-color:#E3F0FE;">
                                        <img src="<?= url('public/img/icons/placeholdersCards/avion.png') ?>" class="img-fluid" alt="avion" style="width:32px;height:32px;object-fit:contain;">
                                    </div>
                                    <div class="d-flex flex-column justify-content-center p-0 m-0">
                                        <p class="fw-semibold m-0 p-0 text-muted" style="font-size:14px;">Vuelos totales</p>
                                        <!-- <p class="fs-3 fw-bold text-dark m-0 p-0">123</p> -->
                                        <p class="fs-3 fw-bold text-dark m-0 p-0">25</p>
                                    </div>
                                </div>
                                <div class="d-flex flex-row justify-content-start p-0 m-0 gap-2 mt-1">
                                    <img src="<?= url('public/img/icons/placeholdersCards/' . $flechaImg) ?>" class="img-fluid" alt="tendencia" style="width:20px;height:20px;object-fit:contain;">
                                    <p class="fw-normal m-0 p-0 <?= $flechaColor ?>" style="font-size:14px;"><?= $flechaTexto ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="bg-white shadow rounded-3 py-2 px-4">
                                <div class="d-flex flex-row align-items-center justify-content-start gap-3">
                                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:55px;height:55px;background-color:#E3F0FE;">
                                        <img src="<?= url('public/img/icons/placeholdersCards/etiqueta.png') ?>" class="img-fluid" alt="etiqueta" style="width:32px;height:32px;object-fit:contain;">
                                    </div>
                                    <div class="d-flex flex-column justify-content-center p-0 m-0">
                                        <p class="fw-semibold m-0 p-0 text-muted" style="font-size:14px;">Promociones activas</p>
                                        <p class="fs-3 fw-bold text-dark m-0 p-0">3</p>
                                    </div>
                                </div>
                                <div class="d-flex flex-row justify-content-start p-0 m-0 gap-2 mt-1">
                                    <img src="<?= url('public/img/icons/placeholdersCards/' . $flechaImg) ?>" class="img-fluid" alt="tendencia" style="width:20px;height:20px;object-fit:contain;">
                                    <p class="fw-normal m-0 p-0 <?= $flechaColor ?>" style="font-size:14px;"><?= $flechaTexto ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="bg-white shadow rounded-3 py-2 px-4">
                                <div class="d-flex flex-row align-items-center justify-content-start gap-3">
                                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:55px;height:55px;background-color:#E3F0FE;">
                                        <img src="<?= url('public/img/icons/placeholdersCards/ceo.png') ?>" class="img-fluid" alt="ceo" style="width:32px;height:32px;object-fit:contain;">
                                    </div>
                                    <div class="d-flex flex-column justify-content-center p-0 m-0">
                                        <p class="fw-semibold m-0 p-0 text-muted" style="font-size:14px;">Pasajeros transportados</p>
                                        <p class="fs-3 fw-bold text-dark m-0 p-0">58.420</p>
                                    </div>
                                </div>
                                <div class="d-flex flex-row justify-content-start p-0 m-0 gap-2 mt-1">
                                    <img src="<?= url('public/img/icons/placeholdersCards/' . $flechaImg) ?>" class="img-fluid" alt="tendencia" style="width:20px;height:20px;object-fit:contain;">
                                    <p class="fw-normal m-0 p-0 <?= $flechaColor ?>" style="font-size:14px;"><?= $flechaTexto ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="bg-white shadow rounded-3 py-2 px-4">
                                <div class="d-flex flex-row align-items-center justify-content-start gap-3">
                                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:55px;height:55px;background-color:#E3F0FE;">
                                        <img src="<?= url('public/img/icons/placeholdersCards/ceo.png') ?>" class="img-fluid" alt="ceo" style="width:32px;height:32px;object-fit:contain;">
                                    </div>
                                    <div class="d-flex flex-column justify-content-center p-0 m-0">
                                        <p class="fw-semibold m-0 p-0 text-muted" style="font-size:14px;">Ventas totales</p>
                                        <p class="fs-3 fw-bold text-dark m-0 p-0">$2.840.500</p>
                                    </div>
                                </div>
                                <div class="d-flex flex-row justify-content-start p-0 m-0 gap-2 mt-1">
                                    <img src="<?= url('public/img/icons/placeholdersCards/' . $flechaImg) ?>" class="img-fluid" alt="tendencia" style="width:20px;height:20px;object-fit:contain;">
                                    <p class="fw-normal m-0 p-0 <?= $flechaColor ?>" style="font-size:14px;"> +18% vs mes anterior</p>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

                <!-- Tab Vuelos -->
                <div class="tab-pane fade <?= $tabActiva === 'vuelos' ? 'show active' : '' ?>" id="tab-vuelos">
                    <p class="text-muted text-center py-4">
                        Tabla de vuelos — próximamente
                    </p>
                </div>

                <!-- Tab Promociones -->
                <div class="tab-pane fade <?= $tabActiva === 'promociones' ? 'show active' : '' ?>" id="tab-promociones">
                    <p class="text-muted text-center py-4">
                        Tabla de promociones — próximamente
                    </p>
                </div>

            </div>
        </div>

    </div>
</div>