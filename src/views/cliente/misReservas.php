<?php
date_default_timezone_set('America/Argentina/Buenos_Aires');
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../controllers/ReservaController.php';

$idUsuario = (int)($_SESSION['usuario']['idUsuario'] ?? 0);
$ctrl      = new ReservaController($link);
$reservas  = $ctrl->listarMisReservas($idUsuario);

$ahora = time();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Reservas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= url('public/css/layout/header.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/components/menuMobile.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/layout/reservas.css') ?>">

</head>
<body class="container-fluid overflow-x-hidden p-0 m-0">

<?php require './src/views/layouts/header.php'; ?>

<?php
// alertas flash
$error   = flash_get('error');
$success = flash_get('success');
?>

<div class="container-xxl py-5 px-3">

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold m-0" style="color:#082149;">Mis Reservas</h2>
        <a href="<?= url('index.php?pagina=buscar-vuelos') ?>" class="btn btn-primary">
            + Buscar vuelos
        </a>
    </div>

    <?php if (empty($reservas)): ?>
        <div class="bg-white rounded-4 p-5 text-center shadow-sm">
            <p class="fs-5 text-muted">No tenés reservas todavía.</p>
            <a href="<?= url('index.php?pagina=buscar-vuelos') ?>" class="btn btn-primary mt-2">
                Buscar vuelos
            </a>
        </div>
    <?php else: ?>

        <div class="d-flex flex-column gap-3">
        <?php foreach ($reservas as $r):
            $horasRestantes = (strtotime($r['fechaHoraSalidaVuelo']) - $ahora) / 3600;
            $puedeConfirmar = $r['estadoReserva'] === 'pendiente de pago';
            $puedeCancelar  = $r['estadoReserva'] !== 'cancelada' && $horasRestantes > 72;

            $badgeClass = match($r['estadoReserva']) {
                'confirmada'      => 'text-bg-success',
                'pendiente de pago' => 'bg-warning text-dark',
                'cancelada'       => 'text-bg-secondary',
                default           => 'text-bg-secondary'
            };
            $badgeLabel = match($r['estadoReserva']) {
                'confirmada'      => '✓ Confirmada',
                'pendiente de pago' => '⏳ Pendiente de pago',
                'cancelada'       => '✗ Cancelada',
                default           => $r['estadoReserva']
            };
        ?>
        <div class="bg-white rounded-4 shadow-sm p-4">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                <div>
                    <p class="text-muted m-0" style="font-size:0.75rem;">Código de reserva</p>
                    <p class="fw-bold fs-5 m-0">#<?= $r['idReserva'] ?></p>
                </div>
                <span class="badge <?= $badgeClass ?> px-3 py-2"><?= $badgeLabel ?></span>
            </div>

            <div class="d-flex align-items-center justify-content-between my-3">
                <div>
                    <p class="fw-bold fs-5 m-0"><?= htmlspecialchars($r['origenVuelo']) ?></p>
                    <p class="text-muted m-0" style="font-size:0.85rem;">
                        <?= date('d/m/Y H:i', strtotime($r['fechaHoraSalidaVuelo'])) ?>
                    </p>
                </div>
                <span class="text-primary fs-3 mx-3">✈</span>
                <div class="text-end">
                    <p class="fw-bold fs-5 m-0"><?= htmlspecialchars($r['destinoVuelo']) ?></p>
                    <p class="text-muted m-0" style="font-size:0.85rem;">
                        <?= htmlspecialchars($r['nombreAerolinea']) ?>
                    </p>
                </div>
            </div>

            <hr class="my-2">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <p class="text-muted m-0" style="font-size:0.85rem;">
                    <?= $r['cantPasajeros'] ?> pasajero<?= $r['cantPasajeros'] > 1 ? 's' : '' ?>
                    &nbsp;|&nbsp;
                    Total: <strong>$ <?= number_format($r['precioTotal'], 2, ',', '.') ?></strong>
                    &nbsp;|&nbsp;
                    Reservado: <?= date('d/m/Y', strtotime($r['fechaReserva'])) ?>
                </p>

                <div class="d-flex gap-2 flex-wrap">
                    <?php if ($puedeConfirmar): ?>
                    <form method="POST" action="<?= url('src/routes/reserva.php?accion=confirmar') ?>">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        <input type="hidden" name="idReserva"  value="<?= $r['idReserva'] ?>">
                        <button type="submit" class="btn btn-primary btn-sm">
                            Confirmar pago
                        </button>
                    </form>
                    <?php endif; ?>

                    <?php if ($puedeCancelar): ?>
                    <form method="POST" action="<?= url('src/routes/reserva.php?accion=cancelar') ?>"
                          onsubmit="return confirm('¿Seguro que querés cancelar esta reserva?')">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        <input type="hidden" name="idReserva"  value="<?= $r['idReserva'] ?>">
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            Cancelar reserva
                        </button>
                    </form>
                    <?php endif; ?>

                    <?php if ($r['estadoReserva'] !== 'cancelada' && $horasRestantes <= 72 && $horasRestantes > 0): ?>
                        <span class="text-muted" style="font-size:0.8rem;align-self:center;">
                            No se puede cancelar (menos de 72hs)
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        </div>

    <?php endif; ?>
</div>

<?php require_once './src/views/layouts/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>