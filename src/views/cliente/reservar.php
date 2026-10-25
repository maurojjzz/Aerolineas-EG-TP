<?php
date_default_timezone_set('America/Argentina/Buenos_Aires');
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../controllers/ReservaController.php';
require_once __DIR__ . '/../../controllers/UsuarioController.php';
require_once __DIR__ . '/../../controllers/PromocionController.php';

$idVuelo       = (int)($_GET['idVuelo'] ?? 0);
$idVueloVuelta = (int)($_GET['idVueloVuelta'] ?? 0);
$pasajeros     = max(1, min(9, (int)($_GET['pasajeros'] ?? 1)));

if (!$idVuelo) redirect('index.php?pagina=buscar-vuelos');

if ($idVueloVuelta > 0 && $idVueloVuelta === $idVuelo) {
    flash_set('error', 'Los vuelos de ida y vuelta deben ser distintos.');
    redirect('index.php?pagina=buscar-vuelos');
}

$ctrl  = new ReservaController($link);
$ctrlUsu = new UsuarioController($link);
$promoCtrl = new PromocionController($link);
$vuelo = $ctrl->obtenerVueloParaReservar($idVuelo);

if (!$vuelo) {
    flash_set('error', 'El vuelo no está disponible.');
    redirect('index.php?pagina=buscar-vuelos');
}

if ($pasajeros > $vuelo['asientosDisponibles']) {
    flash_set('error', 'No hay suficientes asientos disponibles para el vuelo de ida.');
    redirect('index.php?pagina=buscar-vuelos');
}

$vueloVuelta = null;
if ($idVueloVuelta > 0) {
    $vueloVuelta = $ctrl->obtenerVueloParaReservar($idVueloVuelta);

    if (!$vueloVuelta) {
        flash_set('error', 'El vuelo de vuelta no está disponible.');
        redirect('index.php?pagina=buscar-vuelos');
    }

    if ($pasajeros > $vueloVuelta['asientosDisponibles']) {
        flash_set('error', 'No hay suficientes asientos disponibles para el vuelo de vuelta.');
        redirect('index.php?pagina=buscar-vuelos');
    }

    $errorVuelta = $ctrl->validarVueloVuelta($vuelo, $vueloVuelta);
    if ($errorVuelta !== null) {
        flash_set('error', $errorVuelta);
        redirect('index.php?pagina=buscar-vuelos');
    }
}

$user = usuarioActual();

$u = $ctrlUsu->obtenerUsuarioPorId($user['idUsuario']);

$esIdaVuelta = $vueloVuelta !== null;
$precioTotal = 0.0;

$tramos = [
    ['vuelo' => $vuelo, 'titulo' => 'Vuelo de ida'],
];

if ($vueloVuelta) {
    $tramos[] = ['vuelo' => $vueloVuelta, 'titulo' => 'Vuelo de vuelta'];
}

$tramosData = [];
foreach ($tramos as $tramo) {
    $vueloResumen = $tramo['vuelo'];

    $salidaTs   = strtotime($vueloResumen['fechaHoraSalidaVuelo']);
    $llegadaTs  = strtotime($vueloResumen['fechaHoraLlegadaVuelo']);
    $diffMin    = max(0, (int)round(($llegadaTs - $salidaTs) / 60));
    $subtotal   = (float)$vueloResumen['precioVuelo'] * $pasajeros;

    // Obtener TODAS las promos aplicables (stacking)
    $promos = $promoCtrl->obtenerPromosAplicables(
        (int)$vueloResumen['idVuelo'],
        (int)$vueloResumen['idAerolinea'],
        $vueloResumen['codigoOrigen'] ?? '',
        $vueloResumen['codigoDestino'] ?? ''
    );

    $descuentoTotal = 0;
    $promosNombres = [];
    foreach ($promos as $p) {
        if ($p['acumulable']) {
            $descuentoTotal += (float)$p['descuentoPorcentaje'];
            $promosNombres[] = $p['nombre'] . ' (-' . $p['descuentoPorcentaje'] . '%)';
        } else {
            $descuentoTotal = (float)$p['descuentoPorcentaje'];
            $promosNombres = [$p['nombre'] . ' (-' . $p['descuentoPorcentaje'] . '%)'];
            break;
        }
    }
    $descuentoTotal = min($descuentoTotal, 100);
    $subtotalConDescuento = $subtotal * (1 - $descuentoTotal / 100);
    $precioTotal += $subtotalConDescuento;

    $tramosData[] = [
        'vuelo'            => $vueloResumen,
        'titulo'           => $tramo['titulo'],
        'fechaSalida'      => date('d/m/Y', $salidaTs),
        'horaSalida'       => date('H:i', $salidaTs),
        'fechaLlegada'     => date('d/m/Y', $llegadaTs),
        'horaLlegada'      => date('H:i', $llegadaTs),
        'duracion'         => intdiv($diffMin, 60) . 'h ' . str_pad((string)($diffMin % 60), 2, '0', STR_PAD_LEFT) . 'm',
        'subtotal'         => $subtotalConDescuento,
        'precioBase'       => $subtotal,
        'promocion'        => !empty($promos) ? $promos[0] : null,
        'descuentoPct'     => $descuentoTotal,
        'promos_aplicadas' => $promos,
        'promos_nombres'   => $promosNombres,
    ];
}


?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservar vuelo — Volaré</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= url('public/css/layout/header.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/components/menuMobile.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/layout/reservas.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/layout/reservarVuelo.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/accesibilidad.css') ?>">
</head>
<body class="container-fluid overflow-x-hidden p-0 m-0">

<?php require './src/views/layouts/header.php'; ?>

<div style="min-height:100vh;">
<main id="contenido" tabindex="-1" class="container-xxl py-4 px-3 px-lg-5">

    <div class="titulo-cuadro text-center mb-4">
        <p class="h5 fw-bold mb-1">Paso 2 de 3</p>
        <h1 class="fs-2 fw-bold">Confirmá tu reserva</h1>
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
                    <h2 class="h4 fw-bold m-0 fs-5">Datos del pasajero</h2>
                </div>

                <p class="text-muted mb-3" style="font-size:0.85rem;">
                    Estos son tus datos registrados. Si necesitás modificarlos, podés hacerlo desde
                    <a href="<?= url('index.php?pagina=perfil') ?>" class="text-primary">Mi perfil</a>.
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
                        <h2 class="h4 fw-bold m-0 fs-5">Resumen de la reserva</h2>
                    </div>
                    <span class="badge-estado-pend">Pendiente de pago</span>
                </div>

                <?php foreach ($tramosData as $i => $tramo): ?>
                    <div class="<?= $i > 0 ? 'border-top pt-4 mt-4' : '' ?>">
                        <?php if ($esIdaVuelta): ?>
                            <p class="text-uppercase text-muted fw-bold mb-3" style="font-size:0.75rem;letter-spacing:1px;">
                                <?= htmlspecialchars($tramo['titulo']) ?>
                            </p>
                        <?php endif; ?>

                        <!-- Aerolínea -->
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <?php if ($tramo['vuelo']['logoUrl'] && $tramo['vuelo']['logoUrl'] !== '0'): ?>
                                <img src="<?= htmlspecialchars($tramo['vuelo']['logoUrl']) ?>"
                                    style="width:40px;height:40px;object-fit:contain;" alt="logo">
                            <?php else: ?>
                                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center"
                                    style="width:40px;height:40px;">
                                    <span class="text-white fw-bold" style="font-size:0.7rem;">
                                        <?= htmlspecialchars(substr($tramo['vuelo']['codigoIATA'], 0, 2)) ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            <div>
                                <p class="fw-semibold m-0" style="font-size:0.9rem;"><?= htmlspecialchars($tramo['vuelo']['nombreAerolinea']) ?></p>
                                <p class="text-muted m-0" style="font-size:0.75rem;"><?= htmlspecialchars($tramo['vuelo']['codigoIATA']) ?></p>
                            </div>
                        </div>

                        <!-- Ruta -->
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <div class="text-center" style="min-width:80px;">
                                <p class="fw-bold fs-5 m-0"><?= $tramo['horaSalida'] ?></p>
                                <p class="fw-semibold m-0" style="font-size:0.85rem;">
                                    <?= htmlspecialchars($tramo['vuelo']['origenVuelo']) ?>
                                </p>
                                <p class="text-muted m-0" style="font-size:0.75rem;"><?= $tramo['fechaSalida'] ?></p>
                            </div>
                            <div class="linea-vuelo-sm mx-2"></div>
                            <div class="text-center" style="min-width:80px;">
                                <p class="fw-bold fs-5 m-0"><?= $tramo['horaLlegada'] ?></p>
                                <p class="fw-semibold m-0" style="font-size:0.85rem;">
                                    <?= htmlspecialchars($tramo['vuelo']['destinoVuelo']) ?>
                                </p>
                                <p class="text-muted m-0" style="font-size:0.75rem;"><?= $tramo['fechaLlegada'] ?></p>
                            </div>
                        </div>

                        <hr class="my-2">

                        <!-- Detalles -->
                        <div class="d-flex flex-column gap-2 mb-3" style="font-size:0.85rem;">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">📅 Fecha de salida</span>
                                <span><?= $tramo['fechaSalida'] ?> · <?= $tramo['horaSalida'] ?></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">📅 Fecha de llegada</span>
                                <span><?= $tramo['fechaLlegada'] ?> · <?= $tramo['horaLlegada'] ?></span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">⏱ Duración</span>
                                <span><?= $tramo['duracion'] ?></span>
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
                                <span>$ <?= number_format((float)$tramo['vuelo']['precioVuelo'], 2, ',', '.') ?> c/u</span>
                            </div>
                            <?php if (!empty($tramo['promos_aplicadas'])): ?>
                                <div class="mb-2">
                                    <span class="badge bg-success me-1">🎉 -<?= (float)$tramo['descuentoPct'] ?>%</span>
                                    <?php if (count($tramo['promos_nombres']) > 1): ?>
                                        <span class="text-muted small" title="<?= htmlspecialchars(implode(' + ', $tramo['promos_nombres'])) ?>">
                                            <?= count($tramo['promos_nombres']) ?> promos
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Descuento</span>
                                    <span class="text-success fw-semibold">- $ <?= number_format($tramo['precioBase'] - $tramo['subtotal'], 2, ',', '.') ?></span>
                                </div>
                                <?php foreach ($tramo['promos_aplicadas'] as $idx => $p): ?>
                                    <div class="d-flex justify-content-between small text-muted">
                                        <span><?= $idx + 1 ?>. <?= htmlspecialchars($p['nombre']) ?></span>
                                        <span>-<?= (float)$p['descuentoPorcentaje'] ?>%</span>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <?php if ($esIdaVuelta): ?>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Subtotal <?= htmlspecialchars($tramo['titulo']) ?></span>
                                    <span class="fw-semibold">$ <?= number_format($tramo['subtotal'], 2, ',', '.') ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="d-flex justify-content-between align-items-center mb-3 mt-4">
                    <span class="fw-bold fs-5">TOTAL FINAL</span>
                    <span class="fw-bold fs-5" style="color:#0d3262;">
                        $ <?= number_format($precioTotal, 2, ',', '.') ?>
                    </span>
                </div>

                <div class="alert alert-info p-2 mb-3" style="font-size:0.8rem;">
                    <?php if ($esIdaVuelta): ?>
                        Se crearán <strong>dos reservas independientes</strong>, una por la ida y otra por la vuelta.
                        Cada tramo se paga por separado desde "Mis Reservas". Si pagás solo uno, el otro queda
                        pendiente y se cancela a los 3 días.
                    <?php else: ?>
                        Una vez confirmes la reserva, pasará a estado <strong>pendiente de pago</strong>.
                        Podrás confirmar el pago desde "Mis Reservas".
                    <?php endif; ?>
                </div>

                <!-- Form oculto -->
                <form method="POST" action="<?= url('src/routes/reserva.php?accion=crear') ?>" id="formReserva">
                    <input type="hidden" name="csrf_token"    value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="idVuelo"       value="<?= $idVuelo ?>">
                    <?php if ($idVueloVuelta > 0): ?>
                        <input type="hidden" name="idVueloVuelta" value="<?= $idVueloVuelta ?>">
                    <?php endif; ?>
                    <input type="hidden" name="cantPasajeros" value="<?= $pasajeros ?>">
                </form>

                <button type="submit" form="formReserva"
                        class="btn btn-primary w-100 fw-semibold py-2 mb-2"
                        data-loading-text="Confirmando reserva..."
                        style="font-size:1rem;">
                    <?= $esIdaVuelta ? 'Confirmar y reservar ambos vuelos' : 'Confirmar y reservar' ?>
                </button>
                <a href="<?= url('index.php?pagina=cliente') ?>" class="btn btn-outline-secondary w-100">
                    Cancelar
                </a>

            </div>
        </div>

    </div>
</main>
</div>

<?php require_once './src/views/layouts/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>