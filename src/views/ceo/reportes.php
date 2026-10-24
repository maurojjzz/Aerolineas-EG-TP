<?php
date_default_timezone_set('America/Argentina/Buenos_Aires');

require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../controllers/AerolineaController.php';

requireRol('ceo');

$idAerolinea = aerolineaCEO();
if (!$idAerolinea) {
    redirect('index.php?pagina=login');
}

$ctrlAero = new AerolineaController($link);
$aero = $ctrlAero->obtenerAerolineaPorId($idAerolinea);
if (!$aero) {
    redirect('index.php?pagina=login');
}

// ── Helpers de la vista ──────────────────────────────────────────────────────
function repCeoFecha(?string $valor): ?string {
    if (!$valor) return null;
    $fecha = DateTime::createFromFormat('Y-m-d', $valor);
    return ($fecha && $fecha->format('Y-m-d') === $valor) ? $valor : null;
}

function repCeoBind(mysqli_stmt $stmt, string $tipos, array $params): void {
    if ($tipos === '' || empty($params)) return;
    $refs = [];
    foreach ($params as $i => $valor) {
        $refs[$i] = &$params[$i];
    }
    array_unshift($refs, $tipos);
    call_user_func_array([$stmt, 'bind_param'], $refs);
}

function repCeoConsulta(mysqli $link, string $sql, string $tipos = '', array $params = []): array {
    $stmt = mysqli_prepare($link, $sql);
    repCeoBind($stmt, $tipos, $params);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $filas = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $filas[] = $fila;
    }
    mysqli_stmt_close($stmt);
    return $filas;
}

function repCeoUrl(int $pag, ?int $porPagina = null): string {
    $params = $_GET;
    $params['pagina']  = 'ceo';
    $params['seccion'] = 'reportes';
    $params['pag']     = $pag;
    if ($porPagina !== null) {
        $params['porPagina'] = $porPagina;
    }
    return 'index.php?' . http_build_query($params);
}

// ── Parámetros ───────────────────────────────────────────────────────────────
$tab = in_array($_GET['tab'] ?? '', ['ventas', 'ocupacion'], true) ? $_GET['tab'] : 'ventas';

$porPaginaPermitidos = [10, 25, 50];
$pag = max(1, (int)($_GET['pag'] ?? 1));
$porPaginaRaw = (int)($_GET['porPagina'] ?? 10);
$porPagina = in_array($porPaginaRaw, $porPaginaPermitidos, true) ? $porPaginaRaw : 10;
$modoImpresion = ($_GET['imprimir'] ?? '') === '1';
if ($modoImpresion) {
    $porPagina = 5000;
}

$desde = repCeoFecha(trim($_GET['desde'] ?? ''));
$hasta = repCeoFecha(trim($_GET['hasta'] ?? ''));
$busqueda = trim($_GET['busqueda'] ?? '');

// ── Consultas según pestaña (siempre limitadas a la aerolínea del CEO) ───────
$where = ['v.idAerolinea = ?'];
$tipos = 'i';
$params = [$idAerolinea];

if ($tab === 'ventas') {
    $where[] = "r.estadoReserva = 'confirmada'";

    if ($desde) { $where[] = 'DATE(r.fechaReserva) >= ?'; $tipos .= 's'; $params[] = $desde; }
    if ($hasta) { $where[] = 'DATE(r.fechaReserva) <= ?'; $tipos .= 's'; $params[] = $hasta; }
    if ($busqueda !== '') {
        $where[] = '(CONCAT(u.nombre, " ", u.apellido) LIKE ? OR u.email LIKE ? OR v.origenVuelo LIKE ? OR v.destinoVuelo LIKE ? OR CAST(r.idReserva AS CHAR) LIKE ?)';
        $like = '%' . $busqueda . '%';
        $tipos .= 'sssss';
        array_push($params, $like, $like, $like, $like, $like);
    }

    $whereSql = implode(' AND ', $where);
    $baseFrom = "FROM reserva r
        INNER JOIN usuario u ON u.idUsuario = r.idUsuario
        INNER JOIN vuelo v ON v.idVuelo = r.idVuelo
        WHERE $whereSql";

    $resumen = repCeoConsulta($link,
        "SELECT COUNT(*) AS ventas,
                COALESCE(SUM(r.precioTotal), 0) AS ingresos,
                COALESCE(SUM(r.cantPasajeros), 0) AS pasajeros,
                COUNT(DISTINCT r.idUsuario) AS clientes
         $baseFrom", $tipos, $params)[0] ?? ['ventas' => 0, 'ingresos' => 0, 'pasajeros' => 0, 'clientes' => 0];

    $total = (int)$resumen['ventas'];
    $totalPaginas = max(1, (int)ceil($total / $porPagina));
    $pag = min($pag, $totalPaginas);
    $offset = ($pag - 1) * $porPagina;

    $filas = repCeoConsulta($link,
        "SELECT r.idReserva, r.fechaReserva, r.cantPasajeros, r.precioTotal,
                u.nombre, u.apellido, u.email,
                v.origenVuelo, v.codigoOrigen, v.destinoVuelo, v.codigoDestino,
                v.fechaHoraSalidaVuelo, v.fechaHoraLlegadaVuelo
         $baseFrom
         ORDER BY r.fechaReserva DESC
         LIMIT ? OFFSET ?", $tipos . 'ii', array_merge($params, [$porPagina, $offset]));

} else {
    if ($desde) { $where[] = 'DATE(v.fechaHoraSalidaVuelo) >= ?'; $tipos .= 's'; $params[] = $desde; }
    if ($hasta) { $where[] = 'DATE(v.fechaHoraSalidaVuelo) <= ?'; $tipos .= 's'; $params[] = $hasta; }
    if ($busqueda !== '') {
        $where[] = '(v.origenVuelo LIKE ? OR v.destinoVuelo LIKE ? OR v.codigoOrigen LIKE ? OR v.codigoDestino LIKE ?)';
        $like = '%' . $busqueda . '%';
        $tipos .= 'ssss';
        array_push($params, $like, $like, $like, $like);
    }

    $whereSql = implode(' AND ', $where);
    $baseFrom = "FROM vuelo v
        LEFT JOIN reserva r ON r.idVuelo = v.idVuelo
        WHERE $whereSql";

    $resumen = repCeoConsulta($link,
        "SELECT COUNT(DISTINCT v.idVuelo) AS vuelos,
                COALESCE(SUM(CASE WHEN r.estadoReserva = 'confirmada' THEN r.cantPasajeros ELSE 0 END), 0) AS pasajeros,
                COALESCE(SUM(CASE WHEN r.estadoReserva = 'confirmada' THEN r.precioTotal ELSE 0 END), 0) AS ingresos
         $baseFrom", $tipos, $params)[0] ?? ['vuelos' => 0, 'pasajeros' => 0, 'ingresos' => 0];

    $disponibles = repCeoConsulta($link,
        "SELECT COALESCE(SUM(v.asientosDisponibles), 0) AS disponibles
         FROM vuelo v WHERE $whereSql", $tipos, $params)[0] ?? ['disponibles' => 0];

    $total = (int)$resumen['vuelos'];
    $totalPaginas = max(1, (int)ceil($total / $porPagina));
    $pag = min($pag, $totalPaginas);
    $offset = ($pag - 1) * $porPagina;

    $filas = repCeoConsulta($link,
        "SELECT v.idVuelo, v.origenVuelo, v.codigoOrigen, v.destinoVuelo, v.codigoDestino,
                v.fechaHoraSalidaVuelo, v.fechaHoraLlegadaVuelo,
                v.asientosTotales, v.asientosDisponibles, v.precioVuelo,
                COALESCE(SUM(CASE WHEN r.estadoReserva = 'confirmada' THEN r.cantPasajeros ELSE 0 END), 0) AS vendidos,
                COALESCE(SUM(CASE WHEN r.estadoReserva = 'confirmada' THEN r.precioTotal ELSE 0 END), 0) AS ingresos
         $baseFrom
         GROUP BY v.idVuelo
         ORDER BY v.fechaHoraSalidaVuelo DESC
         LIMIT ? OFFSET ?", $tipos . 'ii', array_merge($params, [$porPagina, $offset]));
}

$filtrosActivos = 0;
if ($desde || $hasta) $filtrosActivos++;
if ($busqueda !== '') $filtrosActivos++;

$desdeFila = $total === 0 ? 0 : ($pag - 1) * $porPagina + 1;
$hastaFila = min($pag * $porPagina, $total);

$graficoReporte = [];
foreach ($filas as $fila) {
    if ($tab === 'ventas') {
        $ruta = ($fila['codigoOrigen'] ?? '') . ' → ' . ($fila['codigoDestino'] ?? '');
        $graficoReporte[$ruta] = ($graficoReporte[$ruta] ?? 0) + (int)$fila['cantPasajeros'];
    } else {
        $ruta = ($fila['codigoOrigen'] ?? '') . ' → ' . ($fila['codigoDestino'] ?? '');
        $ocupacion = (int)($fila['asientosTotales'] ?? 0) > 0
            ? round(((int)$fila['vendidos'] / (int)$fila['asientosTotales']) * 100)
            : 0;
        $graficoReporte[$ruta] = max($graficoReporte[$ruta] ?? 0, $ocupacion);
    }
}
arsort($graficoReporte);
$graficoReporte = array_slice($graficoReporte, 0, 5, true);
$maxGrafico = max(1, ...array_values($graficoReporte));

$tabs = [
    'ventas'    => 'Ventas',
    'ocupacion' => 'Ocupación',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes de <?= htmlspecialchars($aero['nombreAerolinea']) ?> — Panel de CEO — Volaré</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= url('public/css/admin.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/layout/headerAdmin.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/components/tablaLista.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= url('public/css/bootstrap-icons.css') ?>">
    <link rel="stylesheet" href="<?= url('public/css/accesibilidad.css') ?>">
    <style>
        .reporte-portada, .reporte-grafico { display: none; }
        .barra-fila { display: grid; grid-template-columns: 105px 1fr 80px; gap: 8px; align-items: center; margin: 7px 0; font-size: 10px; }
        .barra-base { height: 10px; background: #e8edf3; border-radius: 6px; overflow: hidden; }
        .barra-valor { height: 100%; background: #2878d7; border-radius: 6px; }
        @media print {
            @page { size: A4 landscape; margin: 14mm; }
            body { background: #fff !important; color: #172033 !important; }
            .admin-sidebar, .admin-header, nav[aria-label="breadcrumb"], #panelFiltrosReportes,
            #btnImprimirReporte, [data-bs-toggle="collapse"], .pagination,
            .reportes-navegacion, .reportes-titulo,
            .tablaContenedor > div:first-child { display: none !important; }
            .admin-main { width: 100% !important; margin: 0 !important; }
            main.admin-content { padding: 0 !important; }
            .reporte-portada { display: flex; justify-content: space-between; align-items: flex-start;
                border-bottom: 2px solid #123d72; margin-bottom: 12px; padding-bottom: 10px; }
            .reporte-portada h1 { color: #123d72; font-size: 22px; margin: 0; }
            .reporte-portada p { margin: 2px 0 0; font-size: 11px; color: #596579; }
            .reporte-meta { text-align: right; font-size: 10px; color: #596579; }
            .card, .tablaContenedor { box-shadow: none !important; border: 1px solid #dbe2ea !important; }
            .table-responsive { overflow: visible !important; }
            table { font-size: 10px; }
            th { background: #eaf1f8 !important; color: #123d72 !important; }
            tr { break-inside: avoid; }
            .reporte-grafico { display: block; border: 1px solid #dbe2ea; padding: 10px 14px; margin: 0 0 12px; break-inside: avoid; }
            .reporte-grafico h2 { color: #123d72; font-size: 13px; margin: 0 0 8px; }
        }
    </style>
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

        <main id="contenido" tabindex="-1" class="container-fluid d-flex flex-column gap-3 p-2 admin-content">

            <?php require __DIR__ . '/../components/alertToast.php'; ?>
            <div class="reporte-portada">
                <div>
                    <h1>Reporte de <?= $tab === 'ventas' ? 'ventas' : 'ocupación' ?></h1>
                    <p><?= htmlspecialchars($aero['nombreAerolinea']) ?> · Información de mi aerolínea</p>
                </div>
                <div class="reporte-meta">
                    <strong>Volaré</strong><br>
                    Generado: <?= date('d/m/Y H:i') ?><br>
                    <?= $desde || $hasta ? 'Período: ' . ($desde ? date('d/m/Y', strtotime($desde)) : 'sin inicio') . ' a ' . ($hasta ? date('d/m/Y', strtotime($hasta)) : 'sin fin') : 'Período: todos los registros' ?>
                </div>
            </div>

            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('index.php?pagina=ceo') ?>">Mi Aerolínea</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Reportes</li>
                </ol>
            </nav>

            <div class="contForm col-12 d-flex flex-column p-2">
                <h1 class="m-0 p-0 fs-2 fw-bold reportes-titulo">Reportes de <?= htmlspecialchars($aero['nombreAerolinea']) ?></h1>
                <p class="m-0 p-0 mb-1 subt">Ventas confirmadas y ocupación de los vuelos de tu aerolínea.</p>

                <!-- Pestañas -->
                <ul class="nav nav-tabs mt-3 reportes-navegacion" style="border-bottom-color: #dee2e6;">
                    <?php foreach ($tabs as $clave => $label): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $tab === $clave ? 'active' : '' ?>"
                               style="
                                   color: <?= $tab === $clave ? '#fff' : '#212529' ?>;
                                   background-color: <?= $tab === $clave ? '#0d6efd' : 'transparent' ?>;
                                   border-color: <?= $tab === $clave ? '#0d6efd #0d6efd transparent' : '#dee2e6 #dee2e6 transparent' ?>;
                                   border-width: 1px;
                                   border-style: solid;
                                   border-top-left-radius: 0.375rem;
                                   border-top-right-radius: 0.375rem;
                                   margin-bottom: -1px;
                               "
                               href="<?= url('index.php?pagina=ceo&seccion=reportes&tab=' . $clave) ?>">
                                <?= $label ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <!-- KPIs -->
                <div class="row g-3 g-md-2 mt-2 mb-3 justify-content-center reportes-resumen">
                    <?php if ($tab === 'ventas'): ?>
                        <?php
                        $ticketPromedio = (int)$resumen['ventas'] > 0
                            ? (float)$resumen['ingresos'] / (int)$resumen['ventas']
                            : 0;
                        $cards = [
                            ['icon' => 'etiqueta.png', 'label' => 'Ventas confirmadas', 'valor' => number_format((int)$resumen['ventas'], 0, ',', '.')],
                            ['icon' => 'ceo.png', 'label' => 'Ingresos', 'valor' => '$ ' . number_format((float)$resumen['ingresos'], 2, ',', '.')],
                            ['icon' => 'avion.png', 'label' => 'Pasajeros', 'valor' => number_format((int)$resumen['pasajeros'], 0, ',', '.')],
                            ['icon' => 'ceo.png', 'label' => 'Ticket promedio', 'valor' => '$ ' . number_format($ticketPromedio, 2, ',', '.')],
                        ];
                        ?>
                    <?php else: ?>
                        <?php
                        $cards = [
                            ['icon' => 'avion.png', 'label' => 'Vuelos', 'valor' => number_format((int)$resumen['vuelos'], 0, ',', '.')],
                            ['icon' => 'ceo.png', 'label' => 'Pasajeros confirmados', 'valor' => number_format((int)$resumen['pasajeros'], 0, ',', '.')],
                            ['icon' => 'etiqueta.png', 'label' => 'Ingresos', 'valor' => '$ ' . number_format((float)$resumen['ingresos'], 2, ',', '.')],
                            ['icon' => 'avion.png', 'label' => 'Asientos disponibles', 'valor' => number_format((int)$disponibles['disponibles'], 0, ',', '.')],
                        ];
                        ?>
                    <?php endif; ?>

                    <?php foreach ($cards as $card): ?>
                        <div class="col-6 col-md-3">
                            <div class="bg-white shadow rounded-3 py-2 px-3 h-100">
                                <div class="d-flex flex-row align-items-center gap-3">
                                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                                         style="width:55px;height:55px;background-color:#E3F0FE;">
                                        <img src="<?= url('public/img/icons/placeholdersCards/' . $card['icon']) ?>"
                                             class="img-fluid" alt="" style="width:32px;height:32px;object-fit:contain;">
                                    </div>

                                    <div class="d-flex flex-column justify-content-center">
                                        <p class="fw-semibold m-0 p-0 text-muted" style="font-size:14px;"><?= $card['label'] ?></p>
                                        <p class="fs-4 fw-bold text-dark m-0 p-0 text-nowrap"><?= $card['valor'] ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <section class="reporte-grafico" aria-label="Resumen gráfico del reporte">
                    <h2>Indicador destacado por ruta</h2>
                    <?php if ($graficoReporte): foreach ($graficoReporte as $ruta => $valor): ?>
                        <div class="barra-fila">
                            <span><?= htmlspecialchars($ruta) ?></span>
                            <div class="barra-base"><div class="barra-valor" style="width: <?= round(($valor / $maxGrafico) * 100) ?>%;"></div></div>
                            <strong><?= $valor ?><?= $tab === 'ocupacion' ? '%' : ' pasajeros' ?></strong>
                        </div>
                    <?php endforeach; else: ?>
                        <p>Sin datos suficientes para generar el indicador.</p>
                    <?php endif; ?>
                </section>

                <div class="table-responsive bg-white shadow rounded-3 pt-3 tablaContenedor">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center px-2 mb-3 gap-3 gap-sm-0">
                        <h2 class="m-0 p-0 fs-4 fw-bold">
                            <?= $tab === 'ventas' ? 'Ventas de mi aerolínea' : 'Ocupación de vuelos' ?>
                        </h2>

                        <div class="d-flex flex-column flex-sm-row gap-2 align-items-stretch align-items-sm-center">
                            <button type="button" class="btn btn-outline-primary text-nowrap"
                                    data-bs-toggle="collapse" data-bs-target="#panelFiltrosReportes"
                                    aria-expanded="<?= $filtrosActivos > 0 ? 'true' : 'false' ?>"
                                    aria-controls="panelFiltrosReportes">
                                <i class="bi bi-funnel me-1"></i> Filtros
                                <?php if ($filtrosActivos > 0): ?>
                                    <span class="badge text-bg-primary ms-1"><?= $filtrosActivos ?></span>
                                <?php endif; ?>
                            </button>
                            <button type="button" class="btn btn-outline-secondary text-nowrap" id="btnImprimirReporte">
                                <i class="bi bi-printer me-1"></i> Imprimir
                            </button>
                        </div>
                    </div>

                    <!-- Panel de filtros -->
                    <div class="collapse <?= $filtrosActivos > 0 ? 'show' : '' ?>" id="panelFiltrosReportes">
                        <form method="GET" action="index.php" class="px-3 pb-3">
                            <input type="hidden" name="pagina" value="ceo">
                            <input type="hidden" name="seccion" value="reportes">
                            <input type="hidden" name="tab" value="<?= $tab ?>">
                            <input type="hidden" name="porPagina" value="<?= $porPagina ?>">

                            <div class="row g-3">
                                <div class="col-12 col-md-3">
                                    <label class="form-label fw-semibold mb-1" style="font-size:0.85rem;" for="filtroDesde">
                                        <?= $tab === 'ventas' ? 'Fecha de reserva desde' : 'Salida desde' ?>
                                    </label>
                                    <input type="date" class="form-control form-control-sm" id="filtroDesde"
                                           name="desde" value="<?= htmlspecialchars($desde ?? '') ?>">
                                </div>
                                <div class="col-12 col-md-3">
                                    <label class="form-label fw-semibold mb-1" style="font-size:0.85rem;" for="filtroHasta">
                                        <?= $tab === 'ventas' ? 'Fecha de reserva hasta' : 'Salida hasta' ?>
                                    </label>
                                    <input type="date" class="form-control form-control-sm" id="filtroHasta"
                                           name="hasta" value="<?= htmlspecialchars($hasta ?? '') ?>">
                                </div>
                                <div class="col-12 col-md-3">
                                    <label class="form-label fw-semibold mb-1" style="font-size:0.85rem;" for="filtroBusquedaReporte">Buscar</label>
                                    <input type="text" class="form-control form-control-sm js-buscador-reporte"
                                           id="filtroBusquedaReporte" name="busqueda" autocomplete="off"
                                           placeholder="<?= $tab === 'ventas' ? 'Ruta, cliente o reserva…' : 'Ruta o código…' ?>"
                                           value="<?= htmlspecialchars($busqueda) ?>">
                                </div>
                                <div class="col-12 d-flex gap-2 align-items-end">
                                    <button type="submit" class="btn btn-primary btn-sm">Aplicar filtros</button>
                                    <a href="<?= url('index.php?pagina=ceo&seccion=reportes&tab=' . $tab) ?>"
                                       class="btn btn-outline-secondary btn-sm">Limpiar</a>
                                </div>
                            </div>
                        </form>
                    </div>

                    <table class="table table-hover align-middle border-top mt-2">
                        <caption class="visually-hidden"><?= $tab === 'ocupacion' ? 'Ocupación por ruta' : 'Ventas del período' ?></caption>
                        <?php if ($tab === 'ventas'): ?>
                            <thead>
                                <tr>
                                    <th scope="col" class="fw-bold text-center">#</th>
                                    <th scope="col" class="fw-bold">FECHA</th>
                                    <th scope="col" class="fw-bold">CLIENTE</th>
                                    <th scope="col" class="fw-bold d-none d-sm-table-cell">RUTA</th>
                                    <th scope="col" class="fw-bold d-none d-md-table-cell">SALIDA</th>
                                    <th scope="col" class="fw-bold text-center">PASAJEROS</th>
                                    <th scope="col" class="fw-bold text-end">TOTAL</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($filas)): ?>
                                    <tr><td colspan="7" class="text-center py-4 text-muted">No hay ventas confirmadas para los filtros seleccionados.</td></tr>
                                <?php else: foreach ($filas as $f): ?>
                                    <tr>
                                        <td class="text-center fw-medium"><?= (int)$f['idReserva'] ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($f['fechaReserva'])) ?></td>
                                        <td>
                                            <div class="fw-medium"><?= htmlspecialchars($f['nombre'] . ' ' . $f['apellido']) ?></div>
                                            <div class="text-muted" style="font-size:0.78rem;"><?= htmlspecialchars($f['email']) ?></div>
                                        </td>
                                        <td class="d-none d-sm-table-cell">
                                            <?= htmlspecialchars($f['origenVuelo']) ?> →
                                            <?= htmlspecialchars($f['destinoVuelo']) ?>
                                        </td>
                                        <td class="d-none d-md-table-cell"><?= date('d/m/Y H:i', strtotime($f['fechaHoraSalidaVuelo'])) ?></td>
                                        <td class="text-center"><?= (int)$f['cantPasajeros'] ?></td>
                                        <td class="text-end fw-semibold">$ <?= number_format((float)$f['precioTotal'], 2, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>

                        <?php else: ?>
                            <thead>
                                <tr>
                                    <th scope="col" class="fw-bold text-center">#</th>
                                    <th scope="col" class="fw-bold">RUTA</th>
                                    <th scope="col" class="fw-bold d-none d-md-table-cell">SALIDA</th>
                                    <th scope="col" class="fw-bold d-none d-lg-table-cell">LLEGADA</th>
                                    <th scope="col" class="fw-bold text-center">TOTAL</th>
                                    <th scope="col" class="fw-bold text-center">VENDIDOS</th>
                                    <th scope="col" class="fw-bold text-center d-none d-sm-table-cell">DISPONIBLES</th>
                                    <th scope="col" class="fw-bold text-center">OCUPACIÓN</th>
                                    <th scope="col" class="fw-bold text-end d-none d-sm-table-cell">INGRESOS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($filas)): ?>
                                    <tr><td colspan="9" class="text-center py-4 text-muted">No hay vuelos para los filtros seleccionados.</td></tr>
                                <?php else: foreach ($filas as $f):
                                    $totales = (int)$f['asientosTotales'];
                                    $vendidos = (int)$f['vendidos'];
                                    $ocupacion = $totales > 0 ? round($vendidos * 100 / $totales, 1) : 0;
                                    ?>
                                    <tr>
                                        <td class="text-center fw-medium"><?= (int)$f['idVuelo'] ?></td>
                                        <td>
                                            <div class="fw-medium"><?= htmlspecialchars($f['origenVuelo']) ?> → <?= htmlspecialchars($f['destinoVuelo']) ?></div>
                                            <div class="text-muted" style="font-size:0.78rem;">
                                                <?= htmlspecialchars($f['codigoOrigen'] ?? '') ?> - <?= htmlspecialchars($f['codigoDestino'] ?? '') ?>
                                            </div>
                                        </td>
                                        <td class="d-none d-md-table-cell"><?= date('d/m/Y H:i', strtotime($f['fechaHoraSalidaVuelo'])) ?></td>
                                        <td class="d-none d-lg-table-cell"><?= date('d/m/Y H:i', strtotime($f['fechaHoraLlegadaVuelo'])) ?></td>
                                        <td class="text-center"><?= $totales ?></td>
                                        <td class="text-center"><?= $vendidos ?></td>
                                        <td class="text-center d-none d-sm-table-cell"><?= (int)$f['asientosDisponibles'] ?></td>
                                        <td class="text-center">
                                            <div class="d-flex align-items-center justify-content-center gap-2">
                                                <div class="progress" style="width:60px;height:6px;">
                                                    <div class="progress-bar <?= $ocupacion >= 80 ? 'bg-danger' : ($ocupacion >= 50 ? 'bg-warning' : 'bg-success') ?>"
                                                         role="progressbar" style="width: <?= min(100, $ocupacion) ?>%;"
                                                         aria-valuenow="<?= $ocupacion ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                                <span style="font-size:0.8rem;"><?= $ocupacion ?>%</span>
                                            </div>
                                        </td>
                                        <td class="text-end fw-semibold d-none d-sm-table-cell">$ <?= number_format((float)$f['ingresos'], 2, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach; endif; ?>
                            </tbody>
                        <?php endif; ?>
                    </table>

                    <!-- Paginación -->
                    <div class="border-top px-3 py-3">
                        <div class="d-flex flex-column flex-sm-row justify-content-center justify-content-sm-between align-items-center flex-wrap gap-3">
                            <span class="text-muted" style="font-size:0.8rem;">
                                Mostrando <strong><?= $desdeFila ?></strong> a <strong><?= $hastaFila ?></strong> de <strong><?= $total ?></strong> resultados
                            </span>

                            <nav aria-label="Paginación">
                                <ul class="pagination pagination-sm m-0 gap-1">
                                    <li class="page-item <?= $pag <= 1 ? 'disabled' : '' ?>">
                                        <a class="page-link rounded-2 border-0 <?= $pag <= 1 ? 'text-muted' : '' ?>"
                                           href="<?= $pag > 1 ? repCeoUrl(1) : '#' ?>">«</a>
                                    </li>
                                    <li class="page-item <?= $pag <= 1 ? 'disabled' : '' ?>">
                                        <a class="page-link rounded-2 border-0 <?= $pag <= 1 ? 'text-muted' : '' ?>"
                                           href="<?= $pag > 1 ? repCeoUrl($pag - 1) : '#' ?>">‹</a>
                                    </li>

                                    <?php
                                    $rango = 2;
                                    $inicio = max(1, $pag - $rango);
                                    $fin = min($totalPaginas, $pag + $rango);
                                    ?>

                                    <?php if ($inicio > 1): ?>
                                        <li class="page-item disabled"><span class="page-link border-0 text-muted rounded-2">…</span></li>
                                    <?php endif; ?>

                                    <?php for ($i = $inicio; $i <= $fin; $i++): ?>
                                        <li class="page-item">
                                            <a class="page-link rounded-2 border-0 <?= $i === $pag ? 'active-pag' : 'text-secondary' ?>"
                                               href="<?= repCeoUrl($i) ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($fin < $totalPaginas): ?>
                                        <li class="page-item disabled"><span class="page-link border-0 text-muted rounded-2">…</span></li>
                                    <?php endif; ?>

                                    <li class="page-item <?= $pag >= $totalPaginas ? 'disabled' : '' ?>">
                                        <a class="page-link rounded-2 border-0 <?= $pag >= $totalPaginas ? 'text-muted' : '' ?>"
                                           href="<?= $pag < $totalPaginas ? repCeoUrl($pag + 1) : '#' ?>">›</a>
                                    </li>
                                    <li class="page-item <?= $pag >= $totalPaginas ? 'disabled' : '' ?>">
                                        <a class="page-link rounded-2 border-0 <?= $pag >= $totalPaginas ? 'text-muted' : '' ?>"
                                           href="<?= $pag < $totalPaginas ? repCeoUrl($totalPaginas) : '#' ?>">»</a>
                                    </li>
                                </ul>
                            </nav>

                            <div class="d-flex align-items-center gap-2">
                                <span class="text-muted" style="font-size:0.8rem;">Por página:</span>
                                <select class="form-select form-select-sm border-0 bg-light rounded-2"
                                        style="width:auto; font-size:0.8rem; cursor:pointer;"
                                        onchange="window.location.href=this.value" aria-label="Resultados por página">
                                    <?php foreach ($porPaginaPermitidos as $op): ?>
                                        <option value="<?= repCeoUrl(1, $op) ?>" <?= $op === $porPagina ? 'selected' : '' ?>>
                                            <?= $op ?> por página
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

<?php require __DIR__ . '/../components/menuMobileAdmin.php' ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
<script src="<?= url('public/js/admin/sidebar.js') ?>"></script>
<script src="<?= url('public/js/admin/reportesListado.js') ?>"></script>

</body>
</html>
