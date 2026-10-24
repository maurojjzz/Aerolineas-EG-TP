<?php
date_default_timezone_set('America/Argentina/Buenos_Aires');
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../controllers/ReservaController.php';

$idUsuario = (int)($_SESSION['usuario']['idUsuario'] ?? 0);
$ctrl      = new ReservaController($link);

$todasReservas = $ctrl->listarMisReservas($idUsuario);
$compras = array_values(array_filter(
    $todasReservas,
    static fn(array $reserva): bool => ($reserva['estadoReserva'] ?? '') === 'confirmada'
));

$fmtPesos = static function ($valor): string {
    return '$ ' . number_format((float)$valor, 2, ',', '.');
};

$fmtFecha = static function (?string $fecha, string $formato = 'd/m/Y H:i'): string {
    if ($fecha === null || $fecha === '') {
        return '—';
    }
    $timestamp = strtotime($fecha);
    return $timestamp ? date($formato, $timestamp) : '—';
};

$fechaValida = static function (string $fecha): bool {
    if ($fecha === '') {
        return false;
    }
    $objeto = DateTimeImmutable::createFromFormat('!Y-m-d', $fecha);
    return $objeto !== false && $objeto->format('Y-m-d') === $fecha;
};

$nombreCiudad = static function (?string $texto): string {
    $texto = trim((string)$texto);
    if ($texto === '') {
        return '—';
    }
    return trim((string)preg_replace('/\s*\([A-Z]{3}\)\s*$/', '', $texto));
};

$normalizarTexto = static function (string $texto): string {
    $texto = function_exists('mb_strtolower') ? mb_strtolower($texto, 'UTF-8') : strtolower($texto);
    return strtr($texto, [
        'á' => 'a',
        'é' => 'e',
        'í' => 'i',
        'ó' => 'o',
        'ú' => 'u',
        'ü' => 'u',
        'ñ' => 'n',
    ]);
};

// Resumen de compras confirmadas
$totalCompras    = count($compras);
$totalInvertido  = 0.0;
$totalPasajeros  = 0;
$destinosFrecuencia = [];

foreach ($compras as $compra) {
    $totalInvertido += (float)($compra['precioTotal'] ?? 0);
    $totalPasajeros += (int)($compra['cantPasajeros'] ?? 0);

    $destino = trim((string)($compra['destinoVuelo'] ?? ''));
    if ($destino !== '') {
        $destinosFrecuencia[$destino] = ($destinosFrecuencia[$destino] ?? 0) + 1;
    }
}

arsort($destinosFrecuencia);
$destinoFavorito = array_key_first($destinosFrecuencia);
$destinoFavoritoCantidad = $destinoFavorito !== null ? (int)$destinosFrecuencia[$destinoFavorito] : 0;
$destinoFavorito = $destinoFavorito ?? 'Sin compras';

// Filtros
$busqueda   = trim((string)($_GET['busqueda'] ?? ''));
$aerolineaId = (int)($_GET['aerolinea'] ?? 0);
$desde      = trim((string)($_GET['desde'] ?? ''));
$hasta      = trim((string)($_GET['hasta'] ?? ''));
$desde      = $fechaValida($desde) ? $desde : '';
$hasta      = $fechaValida($hasta) ? $hasta : '';

$orden = (string)($_GET['orden'] ?? 'reciente');
if (!in_array($orden, ['reciente', 'antigua', 'mayor', 'menor', 'salida'], true)) {
    $orden = 'reciente';
}

$paginaHistorial = max(1, (int)($_GET['pag'] ?? 1));

$comprasFiltradas = array_values(array_filter(
    $compras,
    static function (array $reserva) use ($aerolineaId, $desde, $hasta, $busqueda, $normalizarTexto): bool {
        if ($aerolineaId > 0 && (int)($reserva['idAerolinea'] ?? 0) !== $aerolineaId) {
            return false;
        }

        $fechaCompra = substr((string)($reserva['fechaReserva'] ?? ''), 0, 10);
        if ($desde !== '' && $fechaCompra < $desde) {
            return false;
        }
        if ($hasta !== '' && $fechaCompra > $hasta) {
            return false;
        }

        if ($busqueda !== '') {
            $texto = implode(' ', array_filter([
                $reserva['idReserva'] ?? '',
                $reserva['origenVuelo'] ?? '',
                $reserva['codigoOrigen'] ?? '',
                $reserva['destinoVuelo'] ?? '',
                $reserva['codigoDestino'] ?? '',
                $reserva['nombreAerolinea'] ?? '',
                $reserva['codigoIATA'] ?? '',
            ], static fn($valor): bool => $valor !== null && $valor !== ''));

            $texto   = $normalizarTexto($texto);
            $termino = $normalizarTexto($busqueda);

            if (strpos($texto, $termino) === false) {
                return false;
            }
        }

        return true;
    }
));

usort($comprasFiltradas, static function (array $a, array $b) use ($orden): int {
    return match ($orden) {
        'antigua' => (int)strtotime((string)$a['fechaReserva']) <=> (int)strtotime((string)$b['fechaReserva']),
        'mayor'   => (float)$b['precioTotal'] <=> (float)$a['precioTotal'],
        'menor'   => (float)$a['precioTotal'] <=> (float)$b['precioTotal'],
        'salida'  => (int)strtotime((string)$a['fechaHoraSalidaVuelo']) <=> (int)strtotime((string)$b['fechaHoraSalidaVuelo']),
        default   => (int)strtotime((string)$b['fechaReserva']) <=> (int)strtotime((string)$a['fechaReserva']),
    };
});

$porPagina     = 8;
$totalFiltrado = count($comprasFiltradas);
$totalPaginas  = max(1, (int)ceil($totalFiltrado / $porPagina));
$paginaHistorial = min($paginaHistorial, $totalPaginas);
$offset = ($paginaHistorial - 1) * $porPagina;
$comprasPagina = array_slice($comprasFiltradas, $offset, $porPagina);

$aerolineas = [];
foreach ($compras as $compra) {
    $idAerolinea = (int)($compra['idAerolinea'] ?? 0);
    if ($idAerolinea > 0) {
        $aerolineas[$idAerolinea] = (string)($compra['nombreAerolinea'] ?? '');
    }
}
asort($aerolineas, SORT_NATURAL | SORT_FLAG_CASE);

$urlLimpiar = url('index.php?pagina=historial-compras');

$urlPaginaHistorial = static function (int $numeroPagina): string {
    $parametros = [];
    foreach (['busqueda', 'aerolinea', 'desde', 'hasta', 'orden'] as $clave) {
        $valor = $_GET[$clave] ?? '';
        if ($valor !== '' && $valor !== null) {
            $parametros[$clave] = $valor;
        }
    }
    $parametros['pagina'] = 'historial-compras';
    $parametros['pag'] = $numeroPagina;

    return url('index.php?' . http_build_query($parametros));
};
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de compras</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= url('public/css/layout/header.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/components/menuMobile.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/layout/reservas.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/layout/misReservas.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/layout/historialCompras.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/bootstrap-icons.css') ?>">
</head>
<body class="container-fluid overflow-x-hidden p-0 m-0">

<?php require __DIR__ . '/../layouts/header.php'; ?>

<div style="min-height:100vh;">
    <div class="container-xxl pb-4 px-3 px-lg-5">

        <?php require __DIR__ . '/../components/alertToast.php'; ?>

        <div class="titulo-cuadro text-center mb-4">
            <h2>HISTORIAL DE <span>COMPRAS</span></h2>
            <p>Revisá tus compras confirmadas y el detalle de cada viaje.</p>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="historial-kpi h-100">
                    <div class="historial-kpi-icon"><i class="bi bi-bag-check"></i></div>
                    <div class="min-width-0">
                        <p class="historial-kpi-label">Compras realizadas</p>
                        <p class="historial-kpi-value"><?= $totalCompras ?></p>
                        <p class="historial-kpi-hint">Reservas confirmadas</p>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="historial-kpi h-100">
                    <div class="historial-kpi-icon"><i class="bi bi-cash-stack"></i></div>
                    <div class="min-width-0">
                        <p class="historial-kpi-label">Total invertido</p>
                        <p class="historial-kpi-value historial-kpi-value-money"><?= $fmtPesos($totalInvertido) ?></p>
                        <p class="historial-kpi-hint">En compras confirmadas</p>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="historial-kpi h-100">
                    <div class="historial-kpi-icon"><i class="bi bi-people"></i></div>
                    <div class="min-width-0">
                        <p class="historial-kpi-label">Pasajeros transportados</p>
                        <p class="historial-kpi-value"><?= $totalPasajeros ?></p>
                        <p class="historial-kpi-hint">En todos tus viajes</p>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="historial-kpi h-100">
                    <div class="historial-kpi-icon"><i class="bi bi-geo-alt"></i></div>
                    <div class="min-width-0">
                        <p class="historial-kpi-label">Destino favorito</p>
                        <p class="historial-kpi-value historial-kpi-value-destination text-truncate"
                           title="<?= htmlspecialchars($destinoFavorito) ?>">
                            <?= htmlspecialchars($nombreCiudad($destinoFavorito)) ?>
                        </p>
                        <p class="historial-kpi-hint">
                            <?= $destinoFavoritoCantidad > 0
                                ? $destinoFavoritoCantidad . ' compra' . ($destinoFavoritoCantidad > 1 ? 's' : '')
                                : 'Todavía sin compras' ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <form action="<?= url('index.php') ?>" method="GET" class="historial-filtros mb-4">
            <input type="hidden" name="pagina" value="historial-compras">

            <div class="row g-3 align-items-end">
                <div class="col-12 col-lg-4">
                    <label class="form-label" for="busquedaCompra">Buscar</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="search"
                               class="form-control"
                               id="busquedaCompra"
                               name="busqueda"
                               value="<?= htmlspecialchars($busqueda) ?>"
                               placeholder="Código, destino o aerolínea">
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-2">
                    <label class="form-label" for="aerolineaCompra">Aerolínea</label>
                    <select class="form-select" id="aerolineaCompra" name="aerolinea">
                        <option value="">Todas</option>
                        <?php foreach ($aerolineas as $idAerolinea => $nombreAerolinea): ?>
                            <option value="<?= (int)$idAerolinea ?>" <?= $aerolineaId === (int)$idAerolinea ? 'selected' : '' ?>>
                                <?= htmlspecialchars($nombreAerolinea) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-6 col-lg-2">
                    <label class="form-label" for="desdeCompra">Desde</label>
                    <input type="date" class="form-control" id="desdeCompra" name="desde" value="<?= htmlspecialchars($desde) ?>">
                </div>

                <div class="col-6 col-lg-2">
                    <label class="form-label" for="hastaCompra">Hasta</label>
                    <input type="date" class="form-control" id="hastaCompra" name="hasta" value="<?= htmlspecialchars($hasta) ?>">
                </div>

                <div class="col-12 col-lg-2">
                    <label class="form-label" for="ordenCompra">Ordenar por</label>
                    <select class="form-select" id="ordenCompra" name="orden">
                        <option value="reciente" <?= $orden === 'reciente' ? 'selected' : '' ?>>Más recientes</option>
                        <option value="antigua" <?= $orden === 'antigua' ? 'selected' : '' ?>>Más antiguas</option>
                        <option value="mayor" <?= $orden === 'mayor' ? 'selected' : '' ?>>Mayor total</option>
                        <option value="menor" <?= $orden === 'menor' ? 'selected' : '' ?>>Menor total</option>
                        <option value="salida" <?= $orden === 'salida' ? 'selected' : '' ?>>Fecha de salida</option>
                    </select>
                </div>
            </div>

            <div class="d-flex flex-column flex-sm-row justify-content-end gap-2 mt-3">
                <a href="<?= htmlspecialchars($urlLimpiar) ?>" class="btn btn-light border">Limpiar filtros</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-funnel"></i> Filtrar
                </button>
            </div>
        </form>

        <div class="bg-white rounded-4 shadow p-3 p-lg-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
                <div>
                    <h3 class="h4 fw-bold m-0 fs-5">Compras confirmadas</h3>
                    <p class="text-muted m-0 historial-resultado">
                        <?= $totalFiltrado ?> compra<?= $totalFiltrado !== 1 ? 's' : '' ?> encontrada<?= $totalFiltrado !== 1 ? 's' : '' ?>
                        <?= $totalFiltrado !== $totalCompras ? 'de ' . $totalCompras . ' en total' : '' ?>
                    </p>
                </div>
                <a href="<?= url('index.php?pagina=buscar-vuelos') ?>" class="btn btn-outline-primary btn-sm align-self-start align-self-md-auto">
                    + Buscar vuelos
                </a>
            </div>

            <?php if ($totalCompras === 0): ?>
                <div class="text-center py-5">
                    <div class="historial-empty-icon mb-3"><i class="bi bi-bag-x"></i></div>
                    <p class="fs-5 fw-semibold mb-1">Todavía no tenés compras confirmadas.</p>
                    <p class="text-muted mb-3">Cuando concretes una reserva, va a aparecer en este historial.</p>
                    <a href="<?= url('index.php?pagina=buscar-vuelos') ?>" class="btn btn-primary">+ Buscar vuelos</a>
                </div>
            <?php elseif ($totalFiltrado === 0): ?>
                <div class="text-center py-5">
                    <div class="historial-empty-icon mb-3"><i class="bi bi-search"></i></div>
                    <p class="fs-5 fw-semibold mb-1">No encontramos compras con esos filtros.</p>
                    <p class="text-muted mb-3">Probá con otro destino, aerolínea o rango de fechas.</p>
                    <a href="<?= htmlspecialchars($urlLimpiar) ?>" class="btn btn-outline-primary">Limpiar filtros</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="tabla-reservas tabla-compras">
                        <thead>
                            <tr>
                                <th>Compra</th>
                                <th>Ruta</th>
                                <th>Aerolínea</th>
                                <th>Vuelo</th>
                                <th>Pasajeros</th>
                                <th>Precio unitario</th>
                                <th>Promoción</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($comprasPagina as $compra):
                            $idReservaCompra = (int)$compra['idReserva'];
                            $cantPasajeros = (int)($compra['cantPasajeros'] ?? 0);
                            $origenCiudad = $nombreCiudad($compra['origenVuelo'] ?? '');
                            $destinoCiudad = $nombreCiudad($compra['destinoVuelo'] ?? '');
                        ?>
                            <tr onclick="abrirDetalleCompra(<?= $idReservaCompra ?>)" title="Ver detalle de la compra">
                                <td>
                                    <p class="compra-codigo">#<?= str_pad((string)$idReservaCompra, 6, '0', STR_PAD_LEFT) ?></p>
                                    <p class="compra-fecha"><?= $fmtFecha($compra['fechaReserva'] ?? null, 'd/m/Y') ?></p>
                                </td>
                                <td>
                                    <div class="ruta-compra">
                                        <div class="ruta-punto">
                                            <span class="ruta-codigo"><?= htmlspecialchars($compra['codigoOrigen'] ?? '—') ?></span>
                                            <span class="ruta-ciudad"><?= htmlspecialchars($origenCiudad) ?></span>
                                        </div>
                                        <span class="ruta-flecha"><i class="bi bi-airplane-fill"></i></span>
                                        <div class="ruta-punto">
                                            <span class="ruta-codigo"><?= htmlspecialchars($compra['codigoDestino'] ?? '—') ?></span>
                                            <span class="ruta-ciudad"><?= htmlspecialchars($destinoCiudad) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <?php if (!empty($compra['logoUrl']) && $compra['logoUrl'] !== '0'): ?>
                                            <img src="<?= htmlspecialchars($compra['logoUrl']) ?>" class="airline-logo" alt="">
                                        <?php else: ?>
                                            <span class="airline-logo-fallback">
                                                <?= htmlspecialchars(substr((string)($compra['codigoIATA'] ?? '--'), 0, 2)) ?>
                                            </span>
                                        <?php endif; ?>
                                        <div class="min-width-0">
                                            <p class="airline-name text-truncate" title="<?= htmlspecialchars($compra['nombreAerolinea'] ?? '') ?>">
                                                <?= htmlspecialchars($compra['nombreAerolinea'] ?? '—') ?>
                                            </p>
                                            <p class="airline-code"><?= htmlspecialchars($compra['codigoIATA'] ?? '') ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <p class="m-0 fw-medium"><?= $fmtFecha($compra['fechaHoraSalidaVuelo'] ?? null, 'd/m/Y') ?></p>
                                    <p class="flight-time">
                                        <?= $fmtFecha($compra['fechaHoraSalidaVuelo'] ?? null, 'H:i') ?>
                                        <span class="flight-arrow">→</span>
                                        <?= $fmtFecha($compra['fechaHoraLlegadaVuelo'] ?? null, 'd/m/Y H:i') ?>
                                    </p>
                                </td>
                                <td>
                                    <span class="passengers-count">
                                        <?= $cantPasajeros ?> pasajero<?= $cantPasajeros !== 1 ? 's' : '' ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="precio-unitario"><?= $fmtPesos($compra['precioVuelo'] ?? 0) ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($compra['promos_aplicadas'])): ?>
                                        <div class="d-flex flex-wrap gap-1">
                                            <span class="badge bg-success" style="font-size:0.65rem;">🎉 -<?= array_sum(array_column($compra['promos_aplicadas'], 'descuentoPorcentaje')) ?>%</span>
                                            <?php foreach ($compra['promos_aplicadas'] as $idx => $p): ?>
                                                <span class="badge bg-light text-dark border" style="font-size:0.6rem;" title="<?= htmlspecialchars($p['nombre']) ?>">
                                                    <?= $idx + 1 ?>. -<?= (float)$p['descuentoPorcentaje'] ?>%
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted" style="font-size:0.75rem;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="compra-total"><?= $fmtPesos($compra['precioTotal'] ?? 0) ?></span>
                                </td>
                                <td>
                                    <span class="badge-estado badge-conf">✓ Confirmada</span>
                                </td>
                                <td onclick="event.stopPropagation()">
                                    <button type="button"
                                            class="btn btn-outline-secondary btn-sm"
                                            title="Ver detalle de la compra"
                                            onclick="abrirDetalleCompra(<?= $idReservaCompra ?>)">
                                        Detalle
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPaginas > 1): ?>
                    <nav class="mt-4" aria-label="Paginación del historial de compras">
                        <ul class="pagination historial-paginacion justify-content-center mb-0">
                            <li class="page-item <?= $paginaHistorial <= 1 ? 'disabled' : '' ?>">
                                <?php if ($paginaHistorial <= 1): ?>
                                    <span class="page-link" aria-label="Página anterior">&laquo;</span>
                                <?php else: ?>
                                    <a class="page-link" href="<?= htmlspecialchars($urlPaginaHistorial($paginaHistorial - 1)) ?>" aria-label="Página anterior">&laquo;</a>
                                <?php endif; ?>
                            </li>

                            <?php
                            $primeraPagina = max(1, $paginaHistorial - 2);
                            $ultimaPagina  = min($totalPaginas, $paginaHistorial + 2);
                            for ($numeroPagina = $primeraPagina; $numeroPagina <= $ultimaPagina; $numeroPagina++):
                            ?>
                                <li class="page-item <?= $numeroPagina === $paginaHistorial ? 'active' : '' ?>">
                                    <?php if ($numeroPagina === $paginaHistorial): ?>
                                        <span class="page-link" aria-current="page"><?= $numeroPagina ?></span>
                                    <?php else: ?>
                                        <a class="page-link" href="<?= htmlspecialchars($urlPaginaHistorial($numeroPagina)) ?>"><?= $numeroPagina ?></a>
                                    <?php endif; ?>
                                </li>
                            <?php endfor; ?>

                            <li class="page-item <?= $paginaHistorial >= $totalPaginas ? 'disabled' : '' ?>">
                                <?php if ($paginaHistorial >= $totalPaginas): ?>
                                    <span class="page-link" aria-label="Página siguiente">&raquo;</span>
                                <?php else: ?>
                                    <a class="page-link" href="<?= htmlspecialchars($urlPaginaHistorial($paginaHistorial + 1)) ?>" aria-label="Página siguiente">&raquo;</a>
                                <?php endif; ?>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetalleCompra" tabindex="-1" role="dialog" aria-labelledby="modalDetalleCompraLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-0 pb-0">
                <h2 class="modal-title fw-bold h5" id="modalDetalleCompraLabel">Detalle de compra</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4" id="modalDetalleCompraContenido"></div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script>
const comprasData = <?= json_encode(
    array_values($compras),
    JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
) ?>;

const escapeHtml = (valor) => String(valor ?? '').replace(/[&<>"']/g, (caracter) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
}[caracter]));

const fmtPrecio = (valor) => '$ ' + Number(valor || 0).toLocaleString('es-AR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
});

const fmtFecha = (valor, conHora = true) => {
    if (!valor) return '—';
    const fecha = new Date(String(valor).replace(' ', 'T'));
    if (Number.isNaN(fecha.getTime())) return '—';

    const opciones = { day: '2-digit', month: '2-digit', year: 'numeric' };
    if (conHora) {
        opciones.hour = '2-digit';
        opciones.minute = '2-digit';
        opciones.hour12 = false;
    }
    return fecha.toLocaleString('es-AR', opciones);
};

const padId = (id) => '#' + String(id).padStart(6, '0');
const ciudad = (texto) => String(texto || '').replace(/\s*\([A-Z]{3}\)\s*$/, '').trim();

function abrirDetalleCompra(idCompra) {
    const compra = comprasData.find((item) => Number(item.idReserva) === Number(idCompra));
    if (!compra) return;

    const cantidadPasajeros = Number(compra.cantPasajeros || 0);
    const logo = compra.logoUrl && compra.logoUrl !== '0'
        ? `<img src="${escapeHtml(compra.logoUrl)}" class="detalle-airline-logo" alt="">`
        : `<span class="detalle-airline-fallback">${escapeHtml(String(compra.codigoIATA || '--').substring(0, 2))}</span>`;

    document.getElementById('modalDetalleCompraContenido').innerHTML = `
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
            <div>
                <p class="detalle-etiqueta">Código de compra</p>
                <p class="detalle-codigo">${padId(compra.idReserva)}</p>
                <p class="detalle-meta">Compra realizada el ${fmtFecha(compra.fechaReserva)}</p>
            </div>
            <span class="badge-estado badge-conf">✓ Confirmada</span>
        </div>

        <hr>

        <div class="d-flex align-items-center gap-2 mb-3">
            ${logo}
            <div>
                <p class="fw-semibold m-0">${escapeHtml(compra.nombreAerolinea)}</p>
                <p class="text-muted m-0" style="font-size:0.78rem;">${escapeHtml(compra.codigoIATA)}</p>
            </div>
        </div>

        <div class="detalle-ruta mb-3">
            <div>
                <span class="detalle-iata">${escapeHtml(compra.codigoOrigen)}</span>
                <p class="detalle-ciudad">${escapeHtml(ciudad(compra.origenVuelo))}</p>
                <p class="detalle-fecha">${fmtFecha(compra.fechaHoraSalidaVuelo)}</p>
            </div>
            <span class="detalle-flecha">✈</span>
            <div class="text-end">
                <span class="detalle-iata">${escapeHtml(compra.codigoDestino)}</span>
                <p class="detalle-ciudad">${escapeHtml(ciudad(compra.destinoVuelo))}</p>
                <p class="detalle-fecha">${fmtFecha(compra.fechaHoraLlegadaVuelo)}</p>
            </div>
        </div>

        <hr>

        <div class="row g-3">
            <div class="col-6 col-md-4 detalle-campo">
                <label>Pasajeros</label>
                <p>${cantidadPasajeros}</p>
            </div>
            <div class="col-6 col-md-4 detalle-campo">
                <label>Precio por pasajero</label>
                <p>${fmtPrecio(compra.precioVuelo)}</p>
            </div>
            <div class="col-12 col-md-4 detalle-campo">
                <label>Total</label>
                <p class="fw-bold" style="color:#0d3262;">${fmtPrecio(compra.precioTotal)}</p>
            </div>
            ${compra.promos_aplicadas && compra.promos_aplicadas.length > 0 ? `
            <div class="col-12 detalle-campo">
                <label>Promociones aplicadas</label>
                <div class="d-flex flex-wrap gap-1 mb-1">
                    <span class="badge bg-success">🎉 -${compra.promos_aplicadas.reduce((sum, p) => sum + parseFloat(p.descuentoPorcentaje), 0).toFixed(2)}%</span>
                    ${compra.promos_aplicadas.map((p, i) => `<span class="badge bg-light text-dark border" style="font-size:0.65rem;">${i+1}. ${escapeHtml(p.nombre)} (-${parseFloat(p.descuentoPorcentaje).toFixed(2)}%)</span>`).join('')}
                </div>
            </div>
            ` : ''}
            <div class="col-6 col-md-4 detalle-campo">
                <label>Salida</label>
                <p>${fmtFecha(compra.fechaHoraSalidaVuelo)}</p>
            </div>
            <div class="col-6 col-md-4 detalle-campo">
                <label>Llegada</label>
                <p>${fmtFecha(compra.fechaHoraLlegadaVuelo)}</p>
            </div>
            <div class="col-12 col-md-4 detalle-campo">
                <label>Estado</label>
                <p>Confirmada</p>
            </div>
        </div>

        <p class="detalle-nota mb-0">Esta compra corresponde a una reserva confirmada.</p>
    `;

    new bootstrap.Modal(document.getElementById('modalDetalleCompra')).show();
}
</script>
</body>
</html>
