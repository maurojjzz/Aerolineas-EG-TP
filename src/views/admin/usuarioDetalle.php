<?php
date_default_timezone_set('America/Argentina/Buenos_Aires');

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../controllers/UsuarioController.php';
require_once __DIR__ . '/../../controllers/ReservaController.php';

$ctrl        = new UsuarioController($link);
$reservaCtrl = new ReservaController($link);

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
if (!in_array($tabActiva, ['resumen', 'reservas'], true)) {
    $tabActiva = 'resumen';
}

// ── Reservas del usuario ──────────────────────────────────────────────────
$todasReservas = $reservaCtrl->listarMisReservas($id);

$reservasConfirmadas = array_values(array_filter(
    $todasReservas,
    static fn(array $r): bool => ($r['estadoReserva'] ?? '') === 'confirmada'
));

$pasajesComprados = 0;
$totalInvertido   = 0.0;

foreach ($reservasConfirmadas as $r) {
    $pasajesComprados += (int)($r['cantPasajeros'] ?? 0);
    $totalInvertido   += (float)($r['precioTotal'] ?? 0);
}

$ahora = time();

$proximosVuelos = array_values(array_filter(
    $todasReservas,
    static function (array $r) use ($ahora): bool {
        if (($r['estadoReserva'] ?? '') === 'cancelada') {
            return false;
        }
        $salida = strtotime((string)($r['fechaHoraSalidaVuelo'] ?? ''));
        return $salida !== false && $salida >= $ahora;
    }
));

usort($proximosVuelos, static function (array $a, array $b): int {
    return strtotime((string)$a['fechaHoraSalidaVuelo']) <=> strtotime((string)$b['fechaHoraSalidaVuelo']);
});

$proximoVuelo = $proximosVuelos[0] ?? null;

$totalReservas = count($todasReservas);

// ── Paginación del historial ──────────────────────────────────────────────
$porPaginaReservas     = 10;
$pagReservas           = max(1, (int)($_GET['pagReservas'] ?? 1));
$totalPaginasReservas  = max(1, (int)ceil($totalReservas / $porPaginaReservas));
$pagReservas           = min($pagReservas, $totalPaginasReservas);
$offsetReservas        = ($pagReservas - 1) * $porPaginaReservas;
$reservasPagina        = array_slice($todasReservas, $offsetReservas, $porPaginaReservas);
$desdeReservas         = $totalReservas === 0 ? 0 : $offsetReservas + 1;
$hastaReservas         = min($offsetReservas + $porPaginaReservas, $totalReservas);

// ── Helpers locales ───────────────────────────────────────────────────────
$fmtMoneda = static function ($valor): string {
    return '$ ' . number_format((float)$valor, 0, ',', '.');
};

$fmtFecha = static function (?string $fecha, string $formato = 'd/m/Y H:i'): string {
    if ($fecha === null || $fecha === '') {
        return '—';
    }
    $timestamp = strtotime($fecha);
    return $timestamp !== false ? date($formato, $timestamp) : '—';
};

$nombreCiudad = static function (?string $texto): string {
    $texto = trim((string)$texto);
    if ($texto === '') {
        return '—';
    }
    return trim((string)preg_replace('/\s*\([A-Z]{3}\)\s*$/', '', $texto));
};

$badgeEstadoReserva = static function (string $estado): array {
    return match ($estado) {
        'confirmada'        => ['text-bg-success', 'Confirmada'],
        'pendiente de pago' => ['text-bg-warning', 'Pendiente de pago'],
        'cancelada'         => ['text-bg-secondary', 'Cancelada'],
        default             => ['text-bg-light', $estado],
    };
};

$urlReservas = static function (int $pagina) use ($id): string {
    return url('index.php?' . http_build_query([
        'pagina'       => 'usuario',
        'seccion'      => 'detalle',
        'id'           => $id,
        'tab'          => 'reservas',
        'pagReservas'  => $pagina,
    ]));
};
?>

<div>
    <nav class="breadcrumbCont" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <!-- <li class="breadcrumb-item"><a href="#">Inicio</a></li> -->
            <li class="breadcrumb-item"><a href="<?= url('index.php?pagina=usuario&seccion=listado') ?>">Usuarios</a></li>
            <li class="breadcrumb-item active">Detalle: <?= htmlspecialchars($u['nombre'] . ' ' . $u['apellido']) ?></li>
        </ol>
    </nav>

    <div class="d-flex justify-content-end align-items-center gap-2 my-2">
        <!-- <a href="<?= url('index.php?pagina=usuario&seccion=listado') ?>" class="btn btn-sm d-flex flex-row gap-1 btn-atras ms-3">
            <img src="<?= url('public/img/icons/flecha-izquierda.png') ?>" class="img-fluid" style="width: 18px;height: 18px;" alt="icono flecha atras"> 
            <span class="text-dark fw-semibold text-decoration-none text-uppercase">Volver a listado</span>
        </a> -->
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
                                <h1 class="fw-bold m-0"><?= htmlspecialchars($u['nombre'] . ' ' . $u['apellido']) ?></h1>
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
                            <?= number_format($totalReservas, 0, ',', '.') ?>
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Pestañas (Tabs) -->
        <div class="bg-white shadow rounded-3">
            <ul class="nav nav-tabs px-3 d-flex align-items-center justify-content-center justify-content-md-start" id="detalleTab">
                <li class="nav-item">
                    <button class="nav-link <?= $tabActiva === 'resumen' ? 'active' : '' ?>"
                            data-bs-toggle="tab" data-bs-target="#tab-resumen" data-tab="resumen">
                        Resumen
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link <?= $tabActiva === 'reservas' ? 'active' : '' ?>"
                            data-bs-toggle="tab" data-bs-target="#tab-reservas" data-tab="reservas">
                        Historial de Reservas
                    </button>
                </li>
            </ul>

            <div class="tab-content p-4">
                <!-- Tab Resumen -->
                <div class="tab-pane fade <?= $tabActiva === 'resumen' ? 'show active' : '' ?>" id="tab-resumen">
                    <div class="d-flex flex-column flex-md-row justify-content-md-between row p-0 m-0 gap-3 gap-md-0 mb-3">
                        <div class="col-md-4">
                            <div class="bg-white shadow rounded-3 py-2 px-4 overflow-hidden h-100">
                                <div class="d-flex flex-row align-items-center justify-content-start gap-3">
                                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:55px;height:55px;background-color:#E3F0FE;">
                                        <i class="bi bi-ticket-perforated-fill text-primary fs-3"></i>
                                    </div>
                                    <div class="d-flex flex-column justify-content-center p-0 m-0">
                                        <p class="fw-semibold m-0 p-0 text-muted" style="font-size:14px;">Pasajes Comprados</p>
                                        <p class="fs-3 fw-bold text-dark m-0 p-0"><?= number_format($pasajesComprados, 0, ',', '.') ?></p>
                                    </div>
                                </div>
                                <p class="text-muted m-0 mt-1" style="font-size:0.8rem;">
                                    <?= count($reservasConfirmadas) ?> reserva<?= count($reservasConfirmadas) === 1 ? '' : 's' ?> confirmada<?= count($reservasConfirmadas) === 1 ? '' : 's' ?>
                                </p>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="bg-white shadow rounded-3 py-2 px-4 overflow-hidden h-100">
                                <div class="d-flex flex-row align-items-center justify-content-start gap-3">
                                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:55px;height:55px;background-color:#E3F0FE;">
                                        <i class="bi bi-cash-stack text-primary fs-3"></i>
                                    </div>
                                    <div class="d-flex flex-column justify-content-center p-0 m-0">
                                        <p class="fw-semibold m-0 p-0 text-muted" style="font-size:14px;">Total Invertido</p>
                                        <p class="fs-3 fw-bold text-dark m-0 p-0"><?= $fmtMoneda($totalInvertido) ?></p>
                                    </div>
                                </div>
                                <p class="text-muted m-0 mt-1" style="font-size:0.8rem;">En compras confirmadas</p>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="bg-white shadow rounded-3 py-2 px-4 overflow-hidden h-100">
                                <div class="d-flex flex-row align-items-center justify-content-start gap-3">
                                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:55px;height:55px;background-color:#E3F0FE;">
                                        <i class="bi bi-airplane-engines-fill text-primary fs-3"></i>
                                    </div>
                                    <div class="d-flex flex-column justify-content-center p-0 m-0">
                                        <p class="fw-semibold m-0 p-0 text-muted" style="font-size:14px;">Próximo Vuelo</p>
                                        <p class="fs-5 fw-bold text-dark m-0 p-0">
                                            <?= $proximoVuelo
                                                ? $fmtFecha($proximoVuelo['fechaHoraSalidaVuelo'], 'd/m/Y') . ' · ' . $fmtFecha($proximoVuelo['fechaHoraSalidaVuelo'], 'H:i')
                                                : '—' ?>
                                        </p>
                                    </div>
                                </div>
                                <p class="text-muted m-0 mt-1" style="font-size:0.8rem;">
                                    <?= $proximoVuelo
                                        ? htmlspecialchars($nombreCiudad($proximoVuelo['origenVuelo']) . ' → ' . $nombreCiudad($proximoVuelo['destinoVuelo']))
                                        : 'Sin vuelos próximos' ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Reservas -->
                <div class="tab-pane fade <?= $tabActiva === 'reservas' ? 'show active' : '' ?>" id="tab-reservas">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
                        <div>
                            <h2 class="m-0 fs-5 fw-bold">Historial de reservas</h2>
                            <p class="text-muted m-0" style="font-size:0.82rem;">
                                Todas las reservas asociadas a <?= htmlspecialchars($u['nombre'] . ' ' . $u['apellido']) ?>.
                            </p>
                        </div>
                        <span class="badge text-bg-light border align-self-start align-self-sm-auto">
                            <?= number_format($totalReservas, 0, ',', '.') ?> reserva<?= $totalReservas === 1 ? '' : 's' ?>
                        </span>
                    </div>

                    <?php if ($totalReservas === 0): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-calendar2-x text-muted" style="font-size:2.5rem;"></i>
                            <p class="fs-6 fw-semibold mt-3 mb-1">Este usuario todavía no tiene reservas registradas.</p>
                            <p class="text-muted m-0">Cuando haga una compra, va a aparecer acá.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive tablaContenedor">
                            <table class="table table-hover align-middle border-top mt-2">
                                <caption class="visually-hidden">Historial de reservas</caption>
                                <thead>
                                    <tr>
                                        <th scope="col" class="fw-bold">RESERVA</th>
                                        <th scope="col" class="fw-bold">RUTA</th>
                                        <th scope="col" class="fw-bold d-none d-md-table-cell">AEROLÍNEA</th>
                                        <th scope="col" class="fw-bold d-none d-md-table-cell">VUELO</th>
                                        <th scope="col" class="fw-bold d-none d-sm-table-cell">PASAJEROS</th>
                                        <th scope="col" class="fw-bold text-end">TOTAL</th>
                                        <th scope="col" class="fw-bold text-center">ESTADO</th>
                                        <th scope="col" class="fw-bold text-center">ACCIONES</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reservasPagina as $r):
                                        [$badgeClass, $badgeTexto] = $badgeEstadoReserva((string)$r['estadoReserva']);
                                        $cantPasajeros = (int)($r['cantPasajeros'] ?? 0);
                                    ?>
                                        <tr>
                                            <td>
                                                <p class="fw-semibold m-0">#<?= str_pad((string)(int)$r['idReserva'], 6, '0', STR_PAD_LEFT) ?></p>
                                                <p class="text-muted m-0" style="font-size:0.75rem;">
                                                    <?= $fmtFecha($r['fechaReserva'] ?? null, 'd/m/Y') ?>
                                                </p>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-1">
                                                    <span class="badge text-bg-light border"><?= htmlspecialchars($r['codigoOrigen'] ?? '—') ?></span>
                                                    <span class="text-muted">→</span>
                                                    <span class="badge text-bg-light border"><?= htmlspecialchars($r['codigoDestino'] ?? '—') ?></span>
                                                </div>
                                                <p class="text-muted m-0" style="font-size:0.75rem;">
                                                    <?= htmlspecialchars($nombreCiudad($r['origenVuelo'] ?? '')) ?>
                                                    →
                                                    <?= htmlspecialchars($nombreCiudad($r['destinoVuelo'] ?? '')) ?>
                                                </p>
                                            </td>
                                            <td class="d-none d-md-table-cell">
                                                <div class="d-flex align-items-center gap-2">
                                                    <?php if (!empty($r['logoUrl']) && $r['logoUrl'] !== '0'): ?>
                                                        <img src="<?= htmlspecialchars($r['logoUrl']) ?>" alt="logo" class="rounded-circle border" style="width:28px;height:28px;object-fit:contain;">
                                                    <?php else: ?>
                                                        <span class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center text-white fw-bold" style="width:28px;height:28px;font-size:0.6rem;">
                                                            <?= htmlspecialchars(substr((string)($r['codigoIATA'] ?? '--'), 0, 2)) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                    <span class="fw-medium" style="font-size:0.85rem;"><?= htmlspecialchars($r['nombreAerolinea'] ?? '—') ?></span>
                                                </div>
                                            </td>
                                            <td class="d-none d-md-table-cell">
                                                <p class="m-0 fw-medium" style="font-size:0.85rem;">
                                                    <?= $fmtFecha($r['fechaHoraSalidaVuelo'] ?? null, 'd/m/Y') ?>
                                                    <span class="text-muted"><?= $fmtFecha($r['fechaHoraSalidaVuelo'] ?? null, 'H:i') ?></span>
                                                </p>
                                                <p class="text-muted m-0" style="font-size:0.75rem;">
                                                    Llega <?= $fmtFecha($r['fechaHoraLlegadaVuelo'] ?? null, 'd/m/Y H:i') ?>
                                                </p>
                                            </td>
                                            <td class="d-none d-sm-table-cell">
                                                <?= $cantPasajeros ?> pasajero<?= $cantPasajeros === 1 ? '' : 's' ?>
                                            </td>
                                            <td class="text-end">
                                                <span class="fw-semibold"><?= $fmtMoneda($r['precioTotal'] ?? 0) ?></span>
                                                <?php
                                                    $descuentoMostrado = (float)($r['descuento_efectivo'] ?? 0);
                                                    if ($descuentoMostrado <= 0 && !empty($r['promos_aplicadas'])) {
                                                        $descuentoMostrado = array_sum(array_column($r['promos_aplicadas'], 'descuentoPorcentaje'));
                                                    }
                                                ?>
                                                <?php if ($descuentoMostrado > 0): ?>
                                                    <div class="mt-1">
                                                        <span class="badge bg-success" style="font-size:0.65rem;">
                                                            🎉 -<?= number_format($descuentoMostrado, 2, ',', '.') ?>%
                                                        </span>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($badgeTexto) ?></span>
                                            </td>
                                            <td class="text-center">
                                                <?php if (($r['estadoReserva'] ?? '') === 'confirmada'): ?>
                                                    <form method="POST"
                                                          action="<?= url('src/routes/usuarios.php?accion=reenviar-comprobante') ?>"
                                                          data-loading-form>
                                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                                        <input type="hidden" name="idReserva" value="<?= (int)$r['idReserva'] ?>">
                                                        <input type="hidden" name="idUsuario" value="<?= (int)$id ?>">
                                                        <button type="submit" class="btn btn-outline-primary btn-sm"
                                                                data-loading-text="Enviando…"
                                                                title="Reenviar el comprobante de pago por correo">
                                                            <i class="bi bi-envelope" aria-hidden="true"></i>
                                                            <span class="d-none d-lg-inline">Reenviar</span>
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <span class="text-muted" style="font-size:0.75rem;">—</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="border-top px-3 py-3">
                            <div class="d-flex flex-column flex-sm-row justify-content-center justify-content-sm-between align-items-center flex-wrap gap-3">
                                <span class="text-muted" style="font-size:0.8rem;">
                                    Mostrando <strong><?= $desdeReservas ?></strong> a <strong><?= $hastaReservas ?></strong> de <strong><?= $totalReservas ?></strong> reservas
                                </span>

                                <nav aria-label="Paginación del historial">
                                    <ul class="pagination pagination-sm m-0 gap-1">
                                        <li class="page-item <?= $pagReservas <= 1 ? 'disabled' : '' ?>">
                                            <a class="page-link rounded-2 border-0 <?= $pagReservas <= 1 ? 'text-muted' : '' ?>"
                                               href="<?= $pagReservas > 1 ? $urlReservas(1) : '#' ?>">«</a>
                                        </li>
                                        <li class="page-item <?= $pagReservas <= 1 ? 'disabled' : '' ?>">
                                            <a class="page-link rounded-2 border-0 <?= $pagReservas <= 1 ? 'text-muted' : '' ?>"
                                               href="<?= $pagReservas > 1 ? $urlReservas($pagReservas - 1) : '#' ?>">‹</a>
                                        </li>

                                        <?php
                                        $rangoReservas  = 2;
                                        $inicioReservas = max(1, $pagReservas - $rangoReservas);
                                        $finReservas    = min($totalPaginasReservas, $pagReservas + $rangoReservas);
                                        ?>

                                        <?php if ($inicioReservas > 1): ?>
                                            <li class="page-item disabled"><span class="page-link border-0 text-muted rounded-2">…</span></li>
                                        <?php endif; ?>

                                        <?php for ($i = $inicioReservas; $i <= $finReservas; $i++): ?>
                                            <li class="page-item">
                                                <a class="page-link rounded-2 border-0 <?= $i === $pagReservas ? 'active-pag' : 'text-secondary' ?>"
                                                   href="<?= $urlReservas($i) ?>"><?= $i ?></a>
                                            </li>
                                        <?php endfor; ?>

                                        <?php if ($finReservas < $totalPaginasReservas): ?>
                                            <li class="page-item disabled"><span class="page-link border-0 text-muted rounded-2">…</span></li>
                                        <?php endif; ?>

                                        <li class="page-item <?= $pagReservas >= $totalPaginasReservas ? 'disabled' : '' ?>">
                                            <a class="page-link rounded-2 border-0 <?= $pagReservas >= $totalPaginasReservas ? 'text-muted' : '' ?>"
                                               href="<?= $pagReservas < $totalPaginasReservas ? $urlReservas($pagReservas + 1) : '#' ?>">›</a>
                                        </li>
                                        <li class="page-item <?= $pagReservas >= $totalPaginasReservas ? 'disabled' : '' ?>">
                                            <a class="page-link rounded-2 border-0 <?= $pagReservas >= $totalPaginasReservas ? 'text-muted' : '' ?>"
                                               href="<?= $pagReservas < $totalPaginasReservas ? $urlReservas($totalPaginasReservas) : '#' ?>">»</a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll('#detalleTab [data-tab]').forEach(function (btn) {
        btn.addEventListener('shown.bs.tab', function () {
            const url = new URL(window.location.href);
            url.searchParams.set('tab', this.dataset.tab);
            if (this.dataset.tab === 'resumen') {
                url.searchParams.delete('pagReservas');
            }
            history.replaceState({}, '', url);
        });
    });
});
</script>
