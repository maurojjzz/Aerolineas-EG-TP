<?php
date_default_timezone_set('America/Argentina/Buenos_Aires');
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../controllers/ReservaController.php';

$idVuelo    = (int)($_GET['idVuelo']    ?? 0);
$pasajeros  = max(1, min(9, (int)($_GET['pasajeros'] ?? 1)));

if (!$idVuelo) redirect('index.php?pagina=buscar-vuelos');

$ctrl  = new ReservaController($link);
$vuelo = $ctrl->obtenerVueloParaReservar($idVuelo);

if (!$vuelo) {
    flash_set('error', 'El vuelo no está disponible.');
    redirect('index.php?pagina=buscar-vuelos');
}

if ($pasajeros > $vuelo['asientosDisponibles']) {
    flash_set('error', 'No hay suficientes asientos disponibles.');
    redirect('index.php?pagina=buscar-vuelos');
}

$precioTotal = (float)$vuelo['precioVuelo'] * $pasajeros;
$fechaSalida = date('d/m/Y H:i', strtotime($vuelo['fechaHoraSalidaVuelo']));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Reserva</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= url('public/css/layout/header.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/components/menuMobile.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/layout/reservas.css') ?>">
</head>
<body class="container-fluid overflow-x-hidden p-0 m-0">

<?php require './src/views/layouts/header.php'; ?>

<div class="container-xxl py-5 px-3">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">

            <h2 class="fw-bold mb-4" style="color:#082149;">Confirmar Reserva</h2>

            <!-- Card resumen del vuelo -->
            <div class="bg-white rounded-4 shadow p-4 mb-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <?php if ($vuelo['logoUrl'] && $vuelo['logoUrl'] !== '0'): ?>
                        <img src="<?= htmlspecialchars($vuelo['logoUrl']) ?>"
                             style="width:48px;height:48px;object-fit:contain;" alt="logo">
                    <?php endif; ?>
                    <div>
                        <p class="fw-bold m-0"><?= htmlspecialchars($vuelo['nombreAerolinea']) ?></p>
                        <p class="text-muted m-0" style="font-size:0.85rem;"><?= htmlspecialchars($vuelo['codigoIATA']) ?></p>
                    </div>
                </div>

                <hr>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <p class="fw-bold fs-4 m-0"><?= htmlspecialchars($vuelo['origenVuelo']) ?></p>
                        <p class="text-muted m-0"><?= $fechaSalida ?></p>
                    </div>
                    <span class="fs-3">✈</span>
                    <div class="text-end">
                        <p class="fw-bold fs-4 m-0"><?= htmlspecialchars($vuelo['destinoVuelo']) ?></p>
                    </div>
                </div>

                <hr>

                <div class="d-flex justify-content-between">
                    <span class="text-muted">Pasajeros</span>
                    <span class="fw-semibold"><?= $pasajeros ?></span>
                </div>
                <div class="d-flex justify-content-between mt-1">
                    <span class="text-muted">Precio por pasajero</span>
                    <span>$ <?= number_format($vuelo['precioVuelo'], 2, ',', '.') ?></span>
                </div>
                <hr>
                <div class="d-flex justify-content-between">
                    <span class="fw-bold fs-5">Total</span>
                    <span class="fw-bold fs-5" style="color:#0d3262;">
                        $ <?= number_format($precioTotal, 2, ',', '.') ?>
                    </span>
                </div>
            </div>

            <!-- Datos del pasajero -->
            <div class="bg-white rounded-4 shadow p-4 mb-4">
                <h5 class="fw-bold mb-3">Tus datos</h5>
                <?php $u = usuarioActual(); ?>
                <p class="m-0"><strong>Nombre:</strong> <?= htmlspecialchars($u['nombre'] . ' ' . $u['apellido']) ?></p>
                <p class="m-0 mt-1"><strong>Email:</strong> <?= htmlspecialchars($u['email']) ?></p>
            </div>

            <!-- Form para confirmar -->
            <form method="POST" action="<?= url('src/routes/reserva.php?accion=crear') ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="idVuelo"      value="<?= $idVuelo ?>">
                <input type="hidden" name="cantPasajeros" value="<?= $pasajeros ?>">

                <div class="d-flex gap-3">
                    <a href="javascript:history.back()" class="btn btn-outline-secondary flex-grow-1">
                        Volver
                    </a>
                    <button type="submit" class="btn btn-primary flex-grow-1 fw-semibold">
                        Confirmar Reserva
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<?php require_once './src/views/layouts/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>