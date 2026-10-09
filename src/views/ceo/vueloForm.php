<?php
date_default_timezone_set('America/Argentina/Buenos_Aires');

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../controllers/VueloController.php';

$idAerolinea = aerolineaCEO();
if (!$idAerolinea) redirect('index.php?pagina=login');

$ctrl = new VueloController($link);

$id          = (int)($_GET['id'] ?? 0);
$modoEditar  = $id > 0;
$vuelo       = null;

if ($modoEditar) {
    $vuelo = $ctrl->obtenerVueloPorId($id);
    if (!$vuelo || (int)$vuelo['idAerolinea'] !== $idAerolinea) {
        flash_set('error', 'Vuelo no encontrado.');
        redirect('index.php?pagina=vuelos');
    }
}

// Extraer fecha y hora del DATETIME
$fechaVal = '';
$horaVal  = '';
if ($modoEditar) {
    $fechaVal = date('Y-m-d', strtotime($vuelo['fechaHoraSalidaVuelo']));
    $horaVal  = date('H:i',   strtotime($vuelo['fechaHoraSalidaVuelo']));
}
?>
<!DOCTYPE html>
<html lang="es-419">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $modoEditar ? 'Editar Vuelo' : 'Crear Vuelo' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= url('public/css/admin.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/layout/headerAdmin.css') ?>">
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
            <?php require __DIR__ . '/../components/alertToast.php'; ?>

            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('index.php?pagina=ceo') ?>">Mi Aerolínea</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('index.php?pagina=vuelos') ?>">Vuelos</a></li>
                    <li class="breadcrumb-item active"><?= $modoEditar ? 'Editar Vuelo' : 'Crear Vuelo' ?></li>
                </ol>
            </nav>

            <div class="contForm col-12 d-flex flex-column p-2">
                <h2 class="fw-bold"><?= $modoEditar ? 'Editar Vuelo' : 'Crear Vuelo' ?></h2>
                <p class="text-muted"><?= $modoEditar ? 'Modificá los datos del vuelo.' : 'Registrá un nuevo vuelo para tu aerolínea.' ?></p>

                <form id="formVuelo"
                      action="<?= $modoEditar
                          ? url('src/routes/vuelo.php?accion=editar&id=' . $id)
                          : url('src/routes/vuelo.php?accion=crear') ?>"
                      method="POST"
                      class="row mt-2 m-0 g-0 p-0 gap-2">

                    <div class="col-12 col-lg-9 border shadow rounded-2 py-4">

                        <div class="row d-flex justify-content-evenly m-0 g-0 p-0">
                            <div class="form-group d-flex flex-column gap-1 col-sm-5 col-11">
                                <label class="form-label-t" for="origen">Origen:</label>
                                <input type="text" name="origen" id="origen"
                                       class="form-control ctm-inp" required
                                       placeholder="Buenos Aires"
                                       value="<?= $modoEditar ? htmlspecialchars($vuelo['origenVuelo']) : '' ?>">
                                <p id="infoOrigen" class="form-text-info">Ciudad o aeropuerto de salida.</p>
                            </div>
                            <div class="form-group d-flex flex-column gap-1 col-sm-5 col-11">
                                <label class="form-label-t" for="destino">Destino:</label>
                                <input type="text" name="destino" id="destino"
                                       class="form-control ctm-inp" required
                                       placeholder="Madrid"
                                       value="<?= $modoEditar ? htmlspecialchars($vuelo['destinoVuelo']) : '' ?>">
                                <p id="infoDestino" class="form-text-info">Ciudad o aeropuerto de llegada.</p>
                            </div>
                        </div>

                        <div class="row d-flex justify-content-evenly m-0 g-0 p-0">
                            <div class="form-group d-flex flex-column gap-1 col-sm-5 col-11">
                                <label class="form-label-t" for="fecha">Fecha de salida:</label>
                                <input type="date" name="fecha" id="fecha"
                                       class="form-control ctm-inp" required
                                       min="<?= date('Y-m-d') ?>"
                                       value="<?= $fechaVal ?>">
                                <p id="infoFecha" class="form-text-info"></p>
                            </div>
                            <div class="form-group d-flex flex-column gap-1 col-sm-5 col-11">
                                <label class="form-label-t" for="hora">Hora de salida:</label>
                                <input type="time" name="hora" id="hora"
                                       class="form-control ctm-inp" required
                                       value="<?= $horaVal ?>">
                                <p id="infoHora" class="form-text-info"></p>
                            </div>
                        </div>

                        <div class="row d-flex justify-content-evenly m-0 g-0 p-0">
                            <div class="form-group d-flex flex-column gap-1 col-sm-5 col-11">
                                <label class="form-label-t" for="asientos">Asientos disponibles:</label>
                                <input type="number" name="asientos" id="asientos"
                                       class="form-control ctm-inp" required min="1"
                                       placeholder="150"
                                       value="<?= $modoEditar ? $vuelo['asientosDisponibles'] : '' ?>">
                                <p id="infoAsientos" class="form-text-info">Cantidad de asientos a la venta.</p>
                            </div>
                            <div class="form-group d-flex flex-column gap-1 col-sm-5 col-11">
                                <label class="form-label-t" for="precio">Precio (ARS):</label>
                                <input type="number" name="precio" id="precio"
                                       class="form-control ctm-inp" required min="0.01" step="0.01"
                                       placeholder="85000"
                                       value="<?= $modoEditar ? $vuelo['precioVuelo'] : '' ?>">
                                <p id="infoPrecio" class="form-text-info">Precio base por pasajero.</p>
                            </div>
                        </div>

                        <div class="row d-flex justify-content-evenly m-0 g-0 p-0">
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end col-11">
                                <a href="<?= url('index.php?pagina=vuelos') ?>"
                                   class="btn btn-outline-danger me-md-2">Cancelar</a>
                                <button type="submit" class="btn btn-primary">
                                    <?= $modoEditar ? 'Guardar cambios' : 'Crear Vuelo' ?>
                                </button>
                            </div>
                        </div>

                    </div>

                    <!-- Sección de validaciones -->
                    <div class="d-none d-lg-flex col-lg flex-column align-items-center gap-4">

                        <div class="d-flex flex-column border border-2 border-danger-subtle w-100 rounded-3 shadow d-none" id="erroresBox">
                            <div class="tituloBoxErrores bg-danger-subtle border-bottom d-flex flex-column align-items-center justify-content-center">
                                <img src="<?= url('public/img/icons/alerta.png') ?>" alt="icono alerta validaciones formulario" class="img-fluid my-2" style="width:28px;height:28px;">
                                <h5 class="text-danger">Validación del Formulario</h5>
                                <p id="cantidadErrores">Errores pendientes: 0</p>
                            </div>
                            <div class="errorBoxContent py-3 pe-3">
                                <ul id="errorList"></ul>
                            </div>
                        </div>

                        <div class="d-flex flex-column border border-2 border-primary-subtle w-100 rounded-3 shadow" id="recomendacionesBox">
                            <div class="tituloBoxErrores bg-primary-subtle border-bottom d-flex flex-column align-items-center justify-content-center">
                                <img src="<?= url('public/img/icons/luz.png') ?>" alt="icono alerta validaciones formulario" class="img-fluid my-2" style="width:28px;height:28px;">
                                <h5 class="text-primary">Recomendaciones</h5>
                                <p class="text-break text-center px-2">Tené en cuenta estos puntos antes de crear el vuelo</p>
                            </div>

                            <div class="recomendaBoxContent d-flex flex-column align-items-center py-3 ps-2">
                                <div class="d-flex gap-2 mb-3 w-100 justify-content-center">
                                    <div>
                                        <p class="m-0 fw-semibold text-center">Ruta válida</p>
                                        <p class="m-0 form-text-info text-center">Origen y destino deben ser diferentes.</p>
                                    </div>
                                </div>
                                <div class="d-flex gap-2 mb-3 w-100 justify-content-center">
                                    <div>
                                        <p class="m-0 fw-semibold text-center">Fecha futura</p>
                                        <p class="m-0 form-text-info text-center">La salida no puede ser en el pasado.</p>
                                    </div>
                                </div>
                                <div class="d-flex gap-2 w-100 justify-content-center">
                                    <div>
                                        <p class="m-0 fw-semibold text-center">Precio y asientos</p>
                                        <p class="m-0 form-text-info text-center">Verificá que sean valores positivos.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                </form>
            </div>
        </main>
    </div>
</div>

<?php require __DIR__ . '/../components/menuMobileAdmin.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= url('public/js/admin/sidebar.js') ?>"></script>
<script src="<?= url('public/js/ceo/validacionesVuelo.js') ?>"></script>
</body>
</html>