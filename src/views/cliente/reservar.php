<?php
date_default_timezone_set('America/Argentina/Buenos_Aires');
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../controllers/ReservaController.php';
require_once __DIR__ . '/../../controllers/UsuarioController.php';

$idVuelo   = (int)($_GET['idVuelo']   ?? 0);
$pasajeros = max(1, min(9, (int)($_GET['pasajeros'] ?? 1)));

if (!$idVuelo) redirect('index.php?pagina=buscar-vuelos');

$ctrl  = new ReservaController($link);
$ctrlUsu = new UsuarioController($link);
$vuelo = $ctrl->obtenerVueloParaReservar($idVuelo);

if (!$vuelo) {
    flash_set('error', 'El vuelo no está disponible.');
    redirect('index.php?pagina=buscar-vuelos');
}

if ($pasajeros > $vuelo['asientosDisponibles']) {
    flash_set('error', 'No hay suficientes asientos disponibles.');
    redirect('index.php?pagina=buscar-vuelos');
}

$user = usuarioActual();

$u = $ctrlUsu->obtenerUsuarioPorId($user['idUsuario']);

$precioTotal = (float)$vuelo['precioVuelo'] * $pasajeros;

$fechaSalida  = date('d/m/Y', strtotime($vuelo['fechaHoraSalidaVuelo']));
$horaSalida   = date('H:i',   strtotime($vuelo['fechaHoraSalidaVuelo']));
$fechaLlegada = date('d/m/Y', strtotime($vuelo['fechaHoraLlegadaVuelo']));
$horaLlegada  = date('H:i',   strtotime($vuelo['fechaHoraLlegadaVuelo']));

// Calcular duración aproximada
$diffMin = (strtotime($vuelo['fechaHoraLlegadaVuelo']) - strtotime($vuelo['fechaHoraSalidaVuelo'])) / 60;
$durH    = floor($diffMin / 60);
$durM    = $diffMin % 60;
$duracion = $durH . 'h ' . str_pad($durM, 2, '0', STR_PAD_LEFT) . 'm';


?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservar vuelo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= url('public/css/layout/header.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/components/menuMobile.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/layout/reservas.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/layout/reservarVuelo.css') ?>">
</head>
<body class="container-fluid overflow-x-hidden p-0 m-0">

<?php require './src/views/layouts/header.php'; ?>

<div style="min-height:100vh;">
<div class="container-xxl py-4 px-3 px-lg-5">

    <div class="titulo-cuadro text-center mb-4">
        <h5 class="fw-bold">Vuela sin limites</h5>
        <h2 >DONDE CADA <span>VIAJE</span> COMIENZA</h2>
        <p>Revisá tus datos y confirmá la reserva en pocos pasos.</p>
    </div>

    <!-- Stepper -->
    <div class="d-flex align-items-center justify-content-center mb-4 px-2">
        <div class="d-flex align-items-center gap-1" style="max-width:500px;width:100%;">
            <!-- Paso 1 -->
            <div class="d-flex flex-column align-items-center">
                <div class="step-circle bg-primary text-white">1</div>
                <small class="text-dark fw-semibold mt-1" style="font-size:0.7rem;white-space:nowrap;">Selección</small>
            </div>
            <div class="step-line done"></div>
            <!-- Paso 2 -->
            <div class="d-flex flex-column align-items-center">
                <div class="step-circle bg-primary text-white">2</div>
                <small class="text-dark mt-1 fw-semibold" style="font-size:0.7rem;white-space:nowrap;">Tus datos</small>
            </div>
            <div class="step-line"></div>
            <!-- Paso 3 -->
            <div class="d-flex flex-column align-items-center">
                <div class="step-circle bg-white text-secondary border">3</div>
                <small class="text-dark fw-semibold mt-1" style="font-size:0.7rem;white-space:nowrap;">Confirmación</small>
            </div>
        </div>
    </div>

    <!-- Layout 2 columnas -->
    <div class="row g-4 justify-content-center">

        <!-- Columna izquierda: datos del pasajero -->
        <div class="col-12 col-lg-5">
            <div class="bg-white rounded-4 shadow p-4">

                <div class="d-flex align-items-center gap-2 mb-3">
                    <span style="font-size:1.3rem;">👤</span>
                    <h5 class="fw-bold m-0">Datos del pasajero</h5>
                </div>

                <p class="text-muted mb-3" style="font-size:0.85rem;">
                    Estos son tus datos registrados. Si necesitás modificarlos, podés hacerlo desde
                    <a href="<?= url('index.php?pagina=mi-perfil') ?>" class="text-primary">Mi perfil</a>.
                </p>

                <div class="row g-3">
                    <div class="col-6">
                        <div class="campo-readonly">
                            <label>Nombre</label>
                            <p><?= htmlspecialchars($u['nombre']) ?></p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="campo-readonly">
                            <label>Apellido</label>
                            <p><?= htmlspecialchars($u['apellido']) ?></p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="campo-readonly">
                            <label>Tipo de documento</label>
                            <p><?= htmlspecialchars(strtoupper($u['tipoDocumento'])) ?></p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="campo-readonly">
                            <label>Número de documento</label>
                            <p><?= htmlspecialchars($u['nroDocumento']) ?></p>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="campo-readonly">
                            <label>Correo electrónico</label>
                            <p><?= htmlspecialchars($u['email']) ?></p>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="campo-readonly">
                            <label>Teléfono</label>
                            <p><?= htmlspecialchars($u['telefono']) ?></p>
                        </div>
                    </div>
                </div>

                <div class="mt-3 p-3 rounded-3" style="background:#f0f4ff;font-size:0.82rem;color:#444;">
                    🔒 Tus datos están protegidos y serán utilizados únicamente para esta reserva.
                </div>

            </div>
        </div>

        <!-- Columna derecha: resumen de la reserva -->
        <div class="col-12 col-lg-7">
            <div class="bg-white rounded-4 shadow p-4 resumen-card">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <h5 class="fw-bold m-0">Resumen de la reserva</h5>
                    </div>
                    <span class="badge-estado-pend">Pendiente de pago</span>
                </div>

                <!-- Aerolínea -->
                <div class="d-flex align-items-center gap-2 mb-3">
                    <?php if ($vuelo['logoUrl'] && $vuelo['logoUrl'] !== '0'): ?>
                        <img src="<?= htmlspecialchars($vuelo['logoUrl']) ?>"
                            style="width:40px;height:40px;object-fit:contain;" alt="logo">
                    <?php else: ?>
                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center"
                            style="width:40px;height:40px;">
                            <span class="text-white fw-bold" style="font-size:0.7rem;">
                                <?= htmlspecialchars(substr($vuelo['codigoIATA'],0,2)) ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    <div>
                        <p class="fw-semibold m-0" style="font-size:0.9rem;"><?= htmlspecialchars($vuelo['nombreAerolinea']) ?></p>
                        <p class="text-muted m-0" style="font-size:0.75rem;"><?= htmlspecialchars($vuelo['codigoIATA']) ?></p>
                    </div>
                </div>

                <!-- Ruta -->
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="text-center" style="min-width:80px;">
                        <p class="fw-bold fs-5 m-0"><?= $horaSalida ?></p>
                        <p class="fw-semibold m-0" style="font-size:0.85rem;">
                            <?= htmlspecialchars($vuelo['origenVuelo']) ?>
                        </p>
                        <p class="text-muted m-0" style="font-size:0.75rem;"><?= $fechaSalida ?></p>
                    </div>
                    <div class="linea-vuelo-sm mx-2"></div>
                    <div class="text-center" style="min-width:80px;">
                        <p class="fw-bold fs-5 m-0"><?= $horaLlegada ?></p>
                        <p class="fw-semibold m-0" style="font-size:0.85rem;">
                            <?= htmlspecialchars($vuelo['destinoVuelo']) ?>
                        </p>
                        <p class="text-muted m-0" style="font-size:0.75rem;"><?= $fechaLlegada ?></p>
                    </div>
                </div>

                <hr class="my-2">

                <!-- Detalles -->
                <div class="d-flex flex-column gap-2 mb-3" style="font-size:0.85rem;">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">📅 Fecha de salida</span>
                        <span><?= $fechaSalida ?> · <?= $horaSalida ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">📅 Fecha de llegada</span>
                        <span><?= $fechaLlegada ?> · <?= $horaLlegada ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">⏱ Duración</span>
                        <span><?= $duracion ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">👥 Pasajeros</span>
                        <span><?= $pasajeros ?> pasajero<?= $pasajeros > 1 ? 's' : '' ?></span>
                    </div>
                    
                </div>

                <hr class="my-2">

                <!-- Precio -->
                <div class="d-flex flex-column gap-1 mb-3" style="font-size:0.85rem;">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Tarifa base (x<?= $pasajeros ?>)</span>
                        <span>$ <?= number_format($vuelo['precioVuelo'], 2, ',', '.') ?> c/u</span>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="fw-bold fs-5">TOTAL FINAL</span>
                    <span class="fw-bold fs-5" style="color:#0d3262;">
                        $ <?= number_format($precioTotal, 2, ',', '.') ?>
                    </span>
                </div>

                <div class="alert alert-info p-2 mb-3" style="font-size:0.8rem;">
                    Una vez confirmes la reserva, pasará a estado <strong>pendiente de pago</strong>.
                    Podrás confirmar el pago desde "Mis Reservas".
                </div>

                <!-- Form oculto -->
                <form method="POST" action="<?= url('src/routes/reserva.php?accion=crear') ?>" id="formReserva">
                    <input type="hidden" name="csrf_token"    value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="idVuelo"       value="<?= $idVuelo ?>">
                    <input type="hidden" name="cantPasajeros" value="<?= $pasajeros ?>">
                </form>

                <button type="submit" form="formReserva"
                        class="btn btn-primary w-100 fw-semibold py-2 mb-2" style="font-size:1rem;">
                    Confirmar y reservar
                </button>
                <a href="javascript:history.back()" class="btn btn-outline-secondary w-100">
                    Cancelar
                </a>

            </div>
        </div>

    </div>
</div>
</div>

<?php require_once './src/views/layouts/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>