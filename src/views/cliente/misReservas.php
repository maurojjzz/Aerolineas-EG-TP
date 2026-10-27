<?php
date_default_timezone_set('America/Argentina/Buenos_Aires');
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../controllers/ReservaController.php';

$idUsuario = (int)($_SESSION['usuario']['idUsuario'] ?? 0);
$ctrl      = new ReservaController($link);

// Limpiar vencidas antes de leer
$ctrl->limpiarReservasVencidas();

$todasReservas = $ctrl->listarMisReservas($idUsuario);
$ahora = time();

$tabActiva = $_GET['tab'] ?? 'todas';
if (!in_array($tabActiva, ['todas','pendientes','confirmadas','canceladas'])) $tabActiva = 'todas';

$reservasMostradas = match($tabActiva) {
    'pendientes'=> array_filter($todasReservas, fn($r) => $r['estadoReserva'] === 'pendiente de pago'),
    'confirmadas'=> array_filter($todasReservas, fn($r) => $r['estadoReserva'] === 'confirmada'),
    'canceladas'=> array_filter($todasReservas, fn($r) => $r['estadoReserva'] === 'cancelada'),
    default => $todasReservas,
};

$conteos = [
    'todas' => count($todasReservas),
    'pendientes' => count(array_filter($todasReservas, fn($r) => $r['estadoReserva'] === 'pendiente de pago')),
    'confirmadas' => count(array_filter($todasReservas, fn($r) => $r['estadoReserva'] === 'confirmada')),
    'canceladas' => count(array_filter($todasReservas, fn($r) => $r['estadoReserva'] === 'cancelada')),
];

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Reservas — Volaré</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= url('public/css/layout/header.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/components/menuMobile.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/layout/reservas.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/layout/misReservas.css') ?>?v=<?= time() ?>">
    
    <link rel="stylesheet" href="<?= url('public/css/accesibilidad.css') ?>">
</head>
<body class="container-fluid overflow-x-hidden p-0 m-0">

<?php require './src/views/layouts/header.php'; ?>

<div style="min-height:100vh;">
<main id="contenido" tabindex="-1" class="container-xxl py-4 px-3 px-lg-5">
    <?php $migas = [['label' => 'Mis reservas']]; require __DIR__ . '/../components/breadcrumbCliente.php'; ?>

    <?php require __DIR__ . '/../components/alertToast.php';  ?>

    <div class="titulo-cuadro text-center mb-4">
        <p class="h5 fw-bold">Vuela sin límites</p>
        <h1>MIS <span>RESERVAS</span></h1>
        <p>Consultá, confirmá o cancelá tus reservas fácilmente.</p>
    </div>

    <div class="bg-white rounded-4 shadow p-4">


        <!-- Tabs -->
        <div class="tab-reservas d-flex flex-wrap mb-4">
            <?php
            $tabs = [
                'todas'       => 'Todas',
                'pendientes'  => 'Pendientes de pago',
                'confirmadas' => 'Confirmadas',
                'canceladas'  => 'Canceladas',
            ];
            foreach ($tabs as $key => $label):
            ?>
            <a href="index.php?pagina=mis-reservas&tab=<?= $key ?>" class="tab-btn <?= $tabActiva === $key ? 'active' : '' ?>"
               <?= $tabActiva === $key ? 'aria-current="page"' : '' ?>>
                <?= $label ?>
                <span class="tab-badge"><?= $conteos[$key] ?></span>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Aviso -->
        <div class="d-flex align-items-center gap-2 mb-3 p-3 rounded-3" style="background:#f0f4ff;font-size:0.82rem;color:#444;">
            ℹ️ Las reservas pendientes se cancelan automáticamente a los <strong>3 días</strong> sin pago.
            Podés cancelar hasta <strong>72 horas antes</strong> del vuelo.
        </div>

        <?php if (empty($reservasMostradas)): ?>
            <div class="text-center py-5">
                <p class="fs-5 text-muted mb-3">
                    <?= match($tabActiva) {
                        'pendientes'  => 'No tenés reservas pendientes de pago.',
                        'confirmadas' => 'No tenés reservas confirmadas.',
                        'canceladas'  => 'No tenés reservas canceladas.',
                        default       => 'Todavía no tenés reservas cargadas.',
                    } ?>
                </p>
                <a href="<?= url('index.php?pagina=buscar-vuelos') ?>" class="btn btn-primary">
                    + Buscar vuelos
                </a>
            </div>
        <?php else: ?>

        <!-- Tabla -->
        <div class="table-responsive">
            <table class="tabla-reservas">
                <caption class="visually-hidden">Mis reservas</caption>
                <thead>
                    <tr>
                        <th scope="col">Código</th>
                        <th scope="col">Ruta</th>
                        <th scope="col">Aerolínea</th>
                        <th scope="col">Fecha salida</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Pasajeros</th>
                        <th scope="col">Promoción</th>
                        <th scope="col">Total</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($reservasMostradas as $r):
                    $horasRestantes = (strtotime($r['fechaHoraSalidaVuelo']) - $ahora) / 3600;
                    $puedeConfirmar = $r['estadoReserva'] === 'pendiente de pago';
                    $puedeCancelar  = $r['estadoReserva'] !== 'cancelada' && $horasRestantes > 72;
                    $vencePago      = strtotime($r['fechaReserva']) + 3 * 24 * 3600;

                    $badgeClass = match($r['estadoReserva']) {
                        'confirmada'        => 'badge-conf',
                        'pendiente de pago' => 'badge-pend',
                        default             => 'badge-canc',
                    };
                    $badgeLabel = match($r['estadoReserva']) {
                        'confirmada'        => '✓ Confirmada',
                        'pendiente de pago' => '⏳ Pendiente de pago',
                        default             => '✗ Cancelada',
                    };

                    // Serializar datos para el modal (sin JSON complejo en el atributo)
                    $dataId = $r['idReserva'];
                ?>
                <tr>
                    <td data-label="Código">
                        <button type="button" 
                                class="btn btn-link p-0 m-0 text-start w-100"
                                onclick="abrirDetalle(<?= $dataId ?>)"
                                aria-label="Ver detalle de reserva #<?= str_pad($r['idReserva'], 6, '0', STR_PAD_LEFT) ?>">
                            <p class="fw-bold m-0" style="font-size:0.9rem;">
                                #<?= str_pad($r['idReserva'], 6, '0', STR_PAD_LEFT) ?>
                            </p>
                            <p class="text-muted m-0" style="font-size:0.72rem;">
                                <?= date('d/m/Y', strtotime($r['fechaReserva'])) ?>
                            </p>
                        </button>
                    </td>
                    <td data-label="Ruta">
                        <div class="ruta-mini">
                            <div>
                                <div class="aeropuerto"><?= htmlspecialchars($r['origenVuelo']) ?></div>
                            </div>
                            <div class="flecha">→</div>
                            <div>
                                <div class="aeropuerto"><?= htmlspecialchars($r['destinoVuelo']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td data-label="Aerolínea">
                        <div class="d-flex align-items-center gap-2">
                            <?php if (!empty($r['logoUrl']) && $r['logoUrl'] !== '0'): ?>
                                <img src="<?= htmlspecialchars($r['logoUrl']) ?>"
                                     style="width:28px;height:28px;object-fit:contain;" alt="Logo de <?= htmlspecialchars($r['nombreAerolinea']) ?>">
                            <?php endif; ?>
                            <span style="font-size:0.82rem;"><?= htmlspecialchars($r['nombreAerolinea']) ?></span>
                        </div>
                    </td>
                    <td data-label="Fecha salida">
                        <p class="m-0 fw-medium"><?= date('d/m/Y', strtotime($r['fechaHoraSalidaVuelo'])) ?></p>
                        <p class="text-muted m-0" style="font-size:0.75rem;">
                            <?= date('H:i', strtotime($r['fechaHoraSalidaVuelo'])) ?>
                        </p>
                    </td>
                    <td data-label="Estado">
                        <span class="badge-estado <?= $badgeClass ?>"><?= $badgeLabel ?></span>
                        <?php if ($r['estadoReserva'] === 'pendiente de pago'): ?>
                            <p class="m-0 mt-1" style="font-size:0.7rem;color:#856404;">
                                Paga antes del <?= date('d/m/Y H:i', $vencePago) ?>
                            </p>
                        <?php elseif ($r['estadoReserva'] === 'confirmada' && $puedeCancelar): ?>
                            <p class="m-0 mt-1" style="font-size:0.7rem;color:#0a3622;">
                                Cancelable hasta <?= date('d/m/Y H:i', strtotime($r['fechaHoraSalidaVuelo']) - 72*3600) ?>
                            </p>
                        <?php endif; ?>
                    </td>
                    <td data-label="Pasajeros">
                        <span style="font-size:0.85rem;">
                            <?= $r['cantPasajeros'] ?> pasajero<?= $r['cantPasajeros'] > 1 ? 's' : '' ?>
                        </span>
                    </td>
                    <td data-label="Promoción">
                        <?php
                            $descuentoMostrado = (float)($r['descuento_efectivo'] ?? 0);
                            if ($descuentoMostrado <= 0 && !empty($r['promos_aplicadas'])) {
                                $descuentoMostrado = array_sum(array_column($r['promos_aplicadas'], 'descuentoPorcentaje'));
                            }
                        ?>
                        <?php if ($descuentoMostrado > 0): ?>
                            <div class="d-flex flex-wrap gap-1">
                                <span class="badge bg-success" style="font-size:0.65rem;">🎉 -<?= number_format($descuentoMostrado, 2, ',', '.') ?>%</span>
                                <?php foreach ($r['promos_aplicadas'] as $idx => $p): ?>
                                    <span class="badge bg-light text-dark border" style="font-size:0.6rem;" title="<?= htmlspecialchars($p['nombre']) ?>">
                                        <?= $idx + 1 ?>. -<?= (float)$p['descuentoPorcentaje'] ?>%
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <span class="text-muted" style="font-size:0.75rem;">—</span>
                        <?php endif; ?>
                    </td>
                    <td data-label="Total">
                        <span class="fw-bold" style="color:#0d3262;font-size:0.9rem;">
                            $ <?= number_format($r['precioTotal'], 2, ',', '.') ?>
                        </span>
                    </td>
                    <td data-label="Acciones">
                        <div class="d-flex gap-1 flex-wrap align-items-center">
                            <!-- Ver detalle -->
                            <button type="button" class="btn btn-outline-secondary btn-sm"
                                    onclick="abrirDetalle(<?= $dataId ?>)">
                                Ver detalle
                            </button>

                            <?php if ($puedeConfirmar): ?>
                            <form method="POST" action="<?= url('src/routes/reserva.php?accion=ir-a-pago') ?>" data-loading-form>
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                <input type="hidden" name="idReserva"  value="<?= $r['idReserva'] ?>">
                                <button type="submit" class="btn btn-primary btn-sm fw-semibold">
                                    💳 Pagar
                                </button>
                            </form>
                            <?php endif; ?>

                            <?php if ($puedeCancelar): ?>
                            <button type="button" class="btn btn-outline-danger btn-sm"
                                    onclick="abrirCancelar(<?= $dataId ?>)">
                                Cancelar
                            </button>
                            <?php endif; ?>

                            <?php if (($r['estadoReserva'] ?? '') === 'confirmada'): ?>
                            <form method="POST" action="<?= url('src/routes/reserva.php?accion=reenviar-comprobante') ?>"
                                  data-loading-form>
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                <input type="hidden" name="idReserva"  value="<?= $r['idReserva'] ?>">
                                <button type="submit" class="btn btn-outline-primary btn-sm"
                                        data-loading-text="Enviando…"
                                        title="Reenviar el comprobante de pago por correo">
                                    Comprobante
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <p class="text-center text-muted mt-4" style="font-size:0.8rem;">
            Si no encontrás una reserva, verificá que hayas iniciado sesión con el correo utilizado al reservar.
        </p>

        <?php endif; ?>

        <div class="text-center mt-3">
            <a href="<?= url('index.php?pagina=buscar-vuelos') ?>" class="btn btn-outline-primary">
                + Buscar más vuelos
            </a>
        </div>

    </div>
</main>
</div>

<!-- Modal detalle de reserva -->
<div class="modal fade" id="modalDetalle" tabindex="-1" role="dialog" aria-labelledby="modalDetalleLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 pb-0">
                <h2 class="modal-title fw-bold h5" id="modalDetalleLabel">Detalle de reserva</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4" id="modalDetalleContenido">
                <!-- Se rellena con JS -->
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación de cancelación -->
<div class="modal fade" id="modalCancelar" tabindex="-1" role="dialog" aria-labelledby="modalCancelarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 pb-0">
                <h2 class="modal-title fw-bold h5 text-danger" id="modalCancelarLabel">Cancelar reserva</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form method="POST" action="<?= url('src/routes/reserva.php?accion=cancelar') ?>" data-loading-form>
                <div class="modal-body p-4">
                    <p class="mb-2">¿Seguro que querés cancelar esta reserva?</p>
                    <div class="bg-light rounded-3 p-3 mb-3" id="modalCancelarResumen"></div>
                    <p class="text-muted small m-0">Esta acción no se puede deshacer y los asientos quedarán liberados.</p>
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="idReserva" id="modalCancelarId" value="">
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Volver</button>
                    <button type="submit" class="btn btn-danger fw-semibold" data-loading-text="Cancelando…">Sí, cancelar reserva</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once './src/views/layouts/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Datos de todas las reservas embebidos en JS
const reservasData = <?= json_encode(array_values($todasReservas), JSON_UNESCAPED_UNICODE) ?>;

function abrirCancelar(idReserva) {
    const r = reservasData.find(x => x.idReserva == idReserva);
    if (!r) return;
    const esc = (t) => String(t ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    document.getElementById('modalCancelarId').value = r.idReserva;
    document.getElementById('modalCancelarResumen').innerHTML =
        `<p class="fw-semibold m-0">Reserva #${String(r.idReserva).padStart(6, '0')}</p>
         <p class="m-0">${esc(r.origenVuelo)} → ${esc(r.destinoVuelo)}</p>`;
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalCancelar')).show();
}

function abrirDetalle(idReserva) {
    const r = reservasData.find(x => x.idReserva == idReserva);
    if (!r) return;

    const badgeMap = {
        'confirmada':       '<span style="background:#d1e7dd;color:#0a3622;border:1px solid #86efac;font-size:0.78rem;padding:3px 12px;border-radius:20px;font-weight:600;">✓ Confirmada</span>',
        'pendiente de pago':'<span style="background:#fff3cd;color:#856404;border:1px solid #ffc107;font-size:0.78rem;padding:3px 12px;border-radius:20px;font-weight:600;">⏳ Pendiente de pago</span>',
        'cancelada':        '<span style="background:#e9ecef;color:#495057;border:1px solid #ced4da;font-size:0.78rem;padding:3px 12px;border-radius:20px;font-weight:600;">✗ Cancelada</span>',
    };

    const fmtFecha = (s) => {
        if (!s) return '—';
        const d = new Date(s.replace(' ', 'T'));
        return d.toLocaleDateString('es-AR') + ' ' + d.toLocaleTimeString('es-AR', {hour:'2-digit',minute:'2-digit'});
    };
    const fmtPrecio = (n) => '$ ' + parseFloat(n).toLocaleString('es-AR', {minimumFractionDigits:2});
    const padId = (id) => '#' + String(id).padStart(6, '0');

    const logo = r.logoUrl && r.logoUrl !== '0'
        ? `<img src="${r.logoUrl}" style="width:36px;height:36px;object-fit:contain;" alt="Logo de ${r.nombreAerolinea}">`
        : `<div style="width:36px;height:36px;border-radius:50%;background:#0d6efd;display:flex;align-items:center;justify-content-center;">
               <span style="color:white;font-size:0.65rem;font-weight:700;">${r.codigoIATA.substring(0,2)}</span>
           </div>`;

    document.getElementById('modalDetalleContenido').innerHTML = `
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
            <div>
                <p class="text-muted m-0" style="font-size:0.7rem;text-transform:uppercase;letter-spacing:0.5px;">Código de reserva</p>
                <p class="fw-bold fs-4 m-0">${padId(r.idReserva)}</p>
                <p class="text-muted m-0" style="font-size:0.78rem;">Creada el ${fmtFecha(r.fechaReserva)}</p>
            </div>
            ${badgeMap[r.estadoReserva] ?? ''}
        </div>

        <hr>

        <div class="d-flex align-items-center gap-2 mb-3">
            ${logo}
            <div>
                <p class="fw-semibold m-0">${r.nombreAerolinea}</p>
                <p class="text-muted m-0" style="font-size:0.78rem;">${r.codigoIATA}</p>
            </div>
        </div>

        <div class="d-flex align-items-center mb-3">
            <div>
                <p class="fw-bold fs-5 m-0">${r.origenVuelo}</p>
                <p class="text-muted m-0" style="font-size:0.8rem;">${fmtFecha(r.fechaHoraSalidaVuelo)}</p>
            </div>
            <div style="height:1px;background:#dee2e6;flex:1;position:relative;margin:0 16px;">
                <span style="position:absolute;top:-10px;left:50%;transform:translateX(-50%);font-size:1rem;color:#0d6efd;">✈</span>
            </div>
            <div class="text-end">
                <p class="fw-bold fs-5 m-0">${r.destinoVuelo}</p>
                <p class="text-muted m-0" style="font-size:0.8rem;">${r.fechaHoraLlegadaVuelo ? fmtFecha(r.fechaHoraLlegadaVuelo) : '—'}</p>
            </div>
        </div>

        <hr>

        <div class="row g-3 mb-3">
            <div class="col-6 col-md-3 detalle-campo">
                <label>Pasajeros</label>
                <p>${r.cantPasajeros}</p>
            </div>
            <div class="col-6 col-md-3 detalle-campo">
                <label>Precio por pasajero</label>
                <p>${fmtPrecio(r.precioVuelo)}</p>
            </div>
            <div class="col-6 col-md-3 detalle-campo">
                <label>Total</label>
                <p class="fw-bold" style="color:#0d3262;">${fmtPrecio(r.precioTotal)}</p>
            </div>
            <div class="col-6 col-md-3 detalle-campo">
                <label>Estado</label>
                <p>${r.estadoReserva}</p>
            </div>
            ${(r.promos_aplicadas && r.promos_aplicadas.length > 0) ? `
            <div class="col-12 detalle-campo">
                <label>Promociones aplicadas</label>
                <div class="d-flex flex-wrap gap-1 mb-1">
                    <span class="badge bg-success">🎉 -${parseFloat(r.descuento_efectivo || r.promos_aplicadas.reduce((sum, p) => sum + parseFloat(p.descuentoPorcentaje), 0)).toFixed(2)}%</span>
                    ${r.promos_aplicadas.map((p, i) => `<span class="badge bg-light text-dark border" style="font-size:0.65rem;">${i+1}. ${escapeHtml(p.nombre)} (-${parseFloat(p.descuentoPorcentaje).toFixed(2)}%)</span>`).join('')}
                </div>
            </div>
            ` : ''}
        </div>
    `;

    new bootstrap.Modal(document.getElementById('modalDetalle')).show();
}
</script>
</body>
</html>