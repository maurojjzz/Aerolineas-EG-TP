<?php 
date_default_timezone_set('America/Argentina/Buenos_Aires');
require_once __DIR__ . '/../../controllers/NovedadController.php';
require_once __DIR__ . '/../../controllers/ReservaController.php';
require_once __DIR__ . '/../../config/conexion.php';

$novedadCtrl = new NovedadController($link);
$novedades = $novedadCtrl->obtenerUltimasPublicadas(3); // Obtiene las últimas 3 publicadas

// ── Próximas reservas del cliente logueado ────────────────────────────────
$idUsuario = (int)($_SESSION['usuario']['idUsuario'] ?? 0);
$proximasReservas = [];

if ($idUsuario > 0) {
    $reservaCtrl = new ReservaController($link);
    $reservaCtrl->limpiarReservasVencidas();

    $todasReservas = $reservaCtrl->listarMisReservas($idUsuario);
    $ahora = time();

    $proximasReservas = array_values(array_filter(
        $todasReservas,
        static function (array $r) use ($ahora): bool {
            if (($r['estadoReserva'] ?? '') === 'cancelada') {
                return false;
            }
            $salida = strtotime((string)($r['fechaHoraSalidaVuelo'] ?? ''));
            return $salida !== false && $salida >= $ahora;
        }
    ));

    usort($proximasReservas, static function (array $a, array $b): int {
        return strtotime((string)$a['fechaHoraSalidaVuelo']) <=> strtotime((string)$b['fechaHoraSalidaVuelo']);
    });

    $proximasReservas = array_slice($proximasReservas, 0, 2);
}

$fmtFechaReserva = static function (?string $fecha, string $formato = 'd/m/Y • H:i'): string {
    if ($fecha === null || $fecha === '') {
        return '—';
    }
    $timestamp = strtotime($fecha);
    return $timestamp !== false ? date($formato, $timestamp) : '—';
};

$nombreCiudadReserva = static function (?string $texto): string {
    $texto = trim((string)$texto);
    if ($texto === '') {
        return '—';
    }
    return trim((string)preg_replace('/\s*\([A-Z]{3}\)\s*$/', '', $texto));
};

$badgeReserva = static function (string $estado): array {
    return match ($estado) {
        'confirmada'        => ['text-bg-success', '✓ Confirmada'],
        'pendiente de pago' => ['bg-warning text-dark', '⏳ Pendiente de pago'],
        default             => ['text-bg-secondary', ucfirst($estado)],
    };
};
?>
<!DOCTYPE html>
<html lang="es">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Mi Panel — Volaré</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

        <link rel="stylesheet" href="<?= url('public/css/layout/homeCliente.css') ?>">
        <link rel="stylesheet" href="<?= url('public/css/layout/header.css') ?>">
        <link rel="stylesheet" href="<?= url('public/css/components/menuMobile.css') ?>">
        <link rel="stylesheet" href="<?= url('public/css/components/searchMobile.css') ?>">
        <link rel="stylesheet" href="<?= url('public/css/components/searchDesktop.css') ?>">
        <link rel="stylesheet" href="<?= url('public/css/components/homeTabsCards.css') ?>">
        <link rel="stylesheet" href="<?= url('public/css/bootstrap-icons.css') ?>">

            <link rel="stylesheet" href="<?= url('public/css/accesibilidad.css') ?>">
    </head>

    <body class="container-fluid overflow-x-hidden p-0 m-0">
        <?php require './src/views/layouts/header.php' ?>

        <main id="contenido" tabindex="-1">
        <p class="text-center text-uppercase fs-5 mt-4 mb-1 user-select-none">Volaré</p>
        <div class="hero-content text-center mt-1 user-select-none">
            <h1>DONDE CADA <span>VIAJE</span> COMIENZA</h1>
            <p>Buscá vuelos, gestioná tus reservas y descubrí destinos increíbles.</p>
        </div>
        <!-- Mobile -->
        <div class="searchbox d-lg-none px-3 mb-5">
            <?php require './src/views/components/searchMobile.php' ?>
        </div>

        <!-- Desktop -->
        <div class="searchbox-desktop d-none d-lg-block mb-5">
            <?php require './src/views/components/searchDesktop.php' ?>
        </div>

        <!-- Reservas + Novedades -->
        <section class="container px-3 px-lg-5 mb-5">
            <div class="row g-4">

                <!-- Mis próximas reservas -->
                <div class="col-12 col-lg-8">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <img src="<?= url('public/img/icons/reserva.png') ?>" style="width:30px;height:30px;object-fit:contain;" alt="Icono de reserva">
                            <h2 class="fw-bold m-0 fs-5">Mis próximas reservas</h2>
                        </div>
                        <a href="<?= url('index.php?pagina=mis-reservas') ?>" class="badge bg-primary bg-opacity-10 text-primary fw-semibold text-decoration-none px-3 py-2 rounded-pill shadow-sm" style="font-size: 0.875rem; transition: all 0.2s;" onmouseover="this.className='badge bg-primary text-white fw-semibold text-decoration-none px-3 py-2 rounded-pill shadow-sm'" onmouseout="this.className='badge bg-primary bg-opacity-10 text-primary fw-semibold text-decoration-none px-3 py-2 rounded-pill shadow-sm'">
                            Ver todas mis reservas <span style="font-size: 1.1rem; vertical-align: middle;">→</span>
                        </a>
                    </div>

                    <?php if (empty($proximasReservas)): ?>
                        <div class="bg-white rounded-3 border p-4 text-center">
                            <i class="bi bi-calendar2-x text-muted" style="font-size:2.2rem;"></i>
                            <p class="fw-semibold mt-3 mb-1">No tenés reservas próximas por ahora.</p>
                            <p class="text-muted mb-3" style="font-size:0.9rem;">Buscá un vuelo y tu próxima reserva va a aparecer acá.</p>
                            <a href="<?= url('index.php?pagina=buscar-vuelos') ?>" class="btn btn-primary btn-sm">Buscar vuelos</a>
                        </div>
                    <?php else: ?>
                        <div class="row g-3">
                            <?php foreach ($proximasReservas as $r):
                                [$badgeClass, $badgeTexto] = $badgeReserva((string)$r['estadoReserva']);
                                $cantPasajeros = (int)($r['cantPasajeros'] ?? 0);
                            ?>
                                <div class="col-12 col-md-6">
                                    <div class="bg-white rounded-3 border p-3 h-100">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <p class="text-muted m-0" style="font-size:0.75rem;">Código de reserva</p>
                                                <p class="fw-bold fs-5 m-0">#<?= str_pad((string)(int)$r['idReserva'], 6, '0', STR_PAD_LEFT) ?></p>
                                            </div>
                                            <span class="badge <?= $badgeClass ?> px-2 py-1" style="font-size:0.75rem;"><?= htmlspecialchars($badgeTexto) ?></span>
                                        </div>

                                        <div class="d-flex align-items-center justify-content-between my-3">
                                            <div>
                                                <p class="fw-semibold m-0"><?= htmlspecialchars($nombreCiudadReserva($r['origenVuelo'] ?? '')) ?> (<?= htmlspecialchars($r['codigoOrigen'] ?? '—') ?>)</p>
                                                <p class="text-muted m-0" style="font-size:0.8rem;"><?= $fmtFechaReserva($r['fechaHoraSalidaVuelo'] ?? null) ?></p>
                                            </div>
                                            <span class="text-primary fs-5">✈</span>
                                            <div class="text-end">
                                                <p class="fw-semibold m-0"><?= htmlspecialchars($nombreCiudadReserva($r['destinoVuelo'] ?? '')) ?> (<?= htmlspecialchars($r['codigoDestino'] ?? '—') ?>)</p>
                                                <p class="text-muted m-0" style="font-size:0.8rem;"><?= $fmtFechaReserva($r['fechaHoraLlegadaVuelo'] ?? null) ?></p>
                                            </div>
                                        </div>

                                        <hr class="my-2">

                                        <div class="d-flex justify-content-between align-items-center gap-2">
                                            <p class="text-muted m-0" style="font-size:0.8rem;">
                                                <?= $cantPasajeros ?> pasajero<?= $cantPasajeros === 1 ? '' : 's' ?>
                                                &nbsp;|&nbsp; Total $ <?= number_format((float)($r['precioTotal'] ?? 0), 0, ',', '.') ?>
                                            </p>
                                            <?php if ($r['estadoReserva'] === 'pendiente de pago'): ?>
                                                <a href="<?= url('index.php?pagina=pago&idReserva=' . (int)$r['idReserva']) ?>" class="btn btn-primary btn-sm text-nowrap" style="font-size:0.8rem;">Continuar pago</a>
                                            <?php else: ?>
                                                <a href="<?= url('index.php?pagina=mis-reservas') ?>" class="btn btn-outline-primary btn-sm text-nowrap" style="font-size:0.8rem;">Ver detalles</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Novedades -->
                <div class="col-12 col-lg-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-primary fs-5">📢</span>
                            <h2 class="fw-bold m-0 fs-5">Novedades</h2>
                        </div>
                        <a href="<?= url('index.php?pagina=cliente-novedades') ?>" class="badge bg-primary bg-opacity-10 text-primary fw-semibold text-decoration-none px-3 py-2 rounded-pill shadow-sm" style="font-size: 0.875rem; transition: all 0.2s;" onmouseover="this.className='badge bg-primary text-white fw-semibold text-decoration-none px-3 py-2 rounded-pill shadow-sm'" onmouseout="this.className='badge bg-primary bg-opacity-10 text-primary fw-semibold text-decoration-none px-3 py-2 rounded-pill shadow-sm'">
                            Ver todas las novedades <span style="font-size: 1.1rem; vertical-align: middle;">→</span>
                        </a>
                    </div>

                    <div class="d-flex flex-column gap-3">
                        <?php if (empty($novedades)): ?>
                            <p class="text-muted small">No hay novedades registradas.</p>
                        <?php else: ?>
                            <?php foreach ($novedades as $nov): ?>
                            <a href="<?= url('index.php?pagina=cliente-novedad-detalle&id=' . $nov['idNovedad']) ?>"
                               class="bg-white rounded-3 border p-3 d-flex gap-3 align-items-start text-decoration-none text-reset">
                                <span class="visually-hidden">Ver novedad:</span>
                                <?php if (!empty($nov['imagen'])): ?>
                                    <img src="<?= htmlspecialchars($nov['imagen']) ?>" alt="Imagen de <?= htmlspecialchars($nov['titulo']) ?>" class="rounded-2 flex-shrink-0" style="width:70px;height:70px;object-fit:cover;">
                                <?php else: ?>
                                    <div class="rounded-2 bg-light d-flex align-items-center justify-content-center text-primary flex-shrink-0" style="width:70px;height:70px;">
                                        <i class="bi bi-megaphone fs-4"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="flex-grow-1" style="min-width: 0;">
                                    <p class="fw-semibold m-0 text-truncate" style="font-size:0.9rem;"><?= htmlspecialchars($nov['titulo']) ?></p>
                                    <p class="text-muted m-0 mt-1 text-truncate" style="font-size:0.8rem;line-height:1.3;"><?= htmlspecialchars($nov['subtitulo'] ?? $nov['contenido']) ?></p>
                                    <p class="text-muted m-0 mt-1" style="font-size:0.75rem;"><?= date('d/m/Y', strtotime($nov['fechaPublicacion'])) ?></p>
                                </div>
                                <span class="text-muted flex-shrink-0" aria-hidden="true">›</span>
                            </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </section>
        </main>

        <?php require_once './src/views/layouts/footer.php'; ?>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
        <script src="<?= url('public/js/aeropuertos.js') ?>"></script>
        <script src="<?= url('public/js/searchMobile.js') ?>?v=<?= filemtime(__DIR__ . '/../../../public/js/searchMobile.js') ?>"></script>
        <script src="<?= url('public/js/searchDesktop.js') ?>?v=<?= filemtime(__DIR__ . '/../../../public/js/searchDesktop.js') ?>"></script>

    </body>

</html>