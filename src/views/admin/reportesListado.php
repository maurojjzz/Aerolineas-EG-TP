<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/app.php';

// ── Helpers de la vista ──────────────────────────────────────────────────────
function repAdminFecha(?string $valor): ?string {
    if (!$valor) return null;
    $fecha = DateTime::createFromFormat('Y-m-d', $valor);
    return ($fecha && $fecha->format('Y-m-d') === $valor) ? $valor : null;
}

function repAdminBind(mysqli_stmt $stmt, string $tipos, array $params): void {
    if ($tipos === '' || empty($params)) return;
    $refs = [];
    foreach ($params as $i => $valor) {
        $refs[$i] = &$params[$i];
    }
    array_unshift($refs, $tipos);
    call_user_func_array([$stmt, 'bind_param'], $refs);
}

function repAdminConsulta(mysqli $link, string $sql, string $tipos = '', array $params = []): array {
    $stmt = mysqli_prepare($link, $sql);
    repAdminBind($stmt, $tipos, $params);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $filas = [];
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $filas[] = $fila;
    }
    mysqli_stmt_close($stmt);
    return $filas;
}

function repAdminUrl(int $pag, ?int $porPagina = null): string {
    $params = $_GET;
    $params['pagina']  = 'reportes';
    $params['seccion'] = 'listado';
    $params['pag']     = $pag;
    if ($porPagina !== null) {
        $params['porPagina'] = $porPagina;
    }
    return 'index.php?' . http_build_query($params);
}

// ── Parámetros ───────────────────────────────────────────────────────────────
$tab = in_array($_GET['tab'] ?? '', ['ventas', 'vuelos', 'usuarios'], true)
    ? $_GET['tab']
    : 'ventas';

$porPaginaPermitidos = [10, 25, 50];
$pag = max(1, (int)($_GET['pag'] ?? 1));
$porPaginaRaw = (int)($_GET['porPagina'] ?? 10);
$porPagina = in_array($porPaginaRaw, $porPaginaPermitidos, true) ? $porPaginaRaw : 10;

$desde = repAdminFecha(trim($_GET['desde'] ?? ''));
$hasta = repAdminFecha(trim($_GET['hasta'] ?? ''));
$busqueda = trim($_GET['busqueda'] ?? '');

$aerolineas = repAdminConsulta(
    $link,
    "SELECT idAerolinea, nombreAerolinea, codigoIATA FROM aerolinea ORDER BY nombreAerolinea ASC"
);
$idAerolineaFiltro = (int)($_GET['aerolinea'] ?? 0);
$idsAerolineasValidos = array_map(fn($a) => (int)$a['idAerolinea'], $aerolineas);
if (!in_array($idAerolineaFiltro, $idsAerolineasValidos, true)) {
    $idAerolineaFiltro = 0;
}

$estadoUsuario = in_array($_GET['estadoUsuario'] ?? '', ['activo', 'inactivo'], true)
    ? $_GET['estadoUsuario']
    : '';
$verificado = in_array($_GET['verificado'] ?? '', ['si', 'no'], true)
    ? $_GET['verificado']
    : '';

// ── Filtros según pestaña ────────────────────────────────────────────────────
$where = [];
$tipos = '';
$params = [];

if ($tab === 'ventas') {
    $where[] = "r.estadoReserva = 'confirmada'";

    if ($desde) { $where[] = 'DATE(r.fechaReserva) >= ?'; $tipos .= 's'; $params[] = $desde; }
    if ($hasta) { $where[] = 'DATE(r.fechaReserva) <= ?'; $tipos .= 's'; $params[] = $hasta; }
    if ($idAerolineaFiltro) { $where[] = 'a.idAerolinea = ?'; $tipos .= 'i'; $params[] = $idAerolineaFiltro; }
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
        INNER JOIN aerolinea a ON a.idAerolinea = v.idAerolinea
        WHERE $whereSql";

    $resumen = repAdminConsulta($link,
        "SELECT COUNT(*) AS ventas,
                COALESCE(SUM(r.precioTotal), 0) AS ingresos,
                COALESCE(SUM(r.cantPasajeros), 0) AS pasajeros
         $baseFrom", $tipos, $params)[0] ?? ['ventas' => 0, 'ingresos' => 0, 'pasajeros' => 0];

    $total = (int)$resumen['ventas'];
    $totalPaginas = max(1, (int)ceil($total / $porPagina));
    $pag = min($pag, $totalPaginas);
    $offset = ($pag - 1) * $porPagina;

    $tiposDatos = $tipos . 'ii';
    $paramsDatos = array_merge($params, [$porPagina, $offset]);

    $filas = repAdminConsulta($link,
        "SELECT r.idReserva, r.fechaReserva, r.cantPasajeros, r.precioTotal,
                u.nombre, u.apellido, u.email,
                a.nombreAerolinea, a.codigoIATA,
                v.origenVuelo, v.codigoOrigen, v.destinoVuelo, v.codigoDestino,
                v.fechaHoraSalidaVuelo, v.fechaHoraLlegadaVuelo
         $baseFrom
         ORDER BY r.fechaReserva DESC
         LIMIT ? OFFSET ?", $tiposDatos, $paramsDatos);

} elseif ($tab === 'vuelos') {
    $where[] = '1 = 1';

    if ($desde) { $where[] = 'DATE(v.fechaHoraSalidaVuelo) >= ?'; $tipos .= 's'; $params[] = $desde; }
    if ($hasta) { $where[] = 'DATE(v.fechaHoraSalidaVuelo) <= ?'; $tipos .= 's'; $params[] = $hasta; }
    if ($idAerolineaFiltro) { $where[] = 'a.idAerolinea = ?'; $tipos .= 'i'; $params[] = $idAerolineaFiltro; }
    if ($busqueda !== '') {
        $where[] = '(v.origenVuelo LIKE ? OR v.destinoVuelo LIKE ? OR a.nombreAerolinea LIKE ? OR v.codigoOrigen LIKE ? OR v.codigoDestino LIKE ?)';
        $like = '%' . $busqueda . '%';
        $tipos .= 'sssss';
        array_push($params, $like, $like, $like, $like, $like);
    }

    $whereSql = implode(' AND ', $where);
    $baseFrom = "FROM vuelo v
        INNER JOIN aerolinea a ON a.idAerolinea = v.idAerolinea
        LEFT JOIN reserva r ON r.idVuelo = v.idVuelo
        WHERE $whereSql";

    $resumen = repAdminConsulta($link,
        "SELECT COUNT(DISTINCT v.idVuelo) AS vuelos,
                COALESCE(SUM(CASE WHEN r.estadoReserva = 'confirmada' THEN r.cantPasajeros ELSE 0 END), 0) AS pasajeros,
                COALESCE(SUM(CASE WHEN r.estadoReserva = 'confirmada' THEN r.precioTotal ELSE 0 END), 0) AS ingresos
         $baseFrom", $tipos, $params)[0] ?? ['vuelos' => 0, 'pasajeros' => 0, 'ingresos' => 0];

    $disponibles = repAdminConsulta($link,
        "SELECT COALESCE(SUM(v.asientosDisponibles), 0) AS disponibles
         FROM vuelo v INNER JOIN aerolinea a ON a.idAerolinea = v.idAerolinea
         WHERE $whereSql", $tipos, $params)[0] ?? ['disponibles' => 0];

    $total = (int)$resumen['vuelos'];
    $totalPaginas = max(1, (int)ceil($total / $porPagina));
    $pag = min($pag, $totalPaginas);
    $offset = ($pag - 1) * $porPagina;

    $tiposDatos = $tipos . 'ii';
    $paramsDatos = array_merge($params, [$porPagina, $offset]);

    $filas = repAdminConsulta($link,
        "SELECT v.idVuelo, v.origenVuelo, v.codigoOrigen, v.destinoVuelo, v.codigoDestino,
                v.fechaHoraSalidaVuelo, v.fechaHoraLlegadaVuelo,
                v.asientosTotales, v.asientosDisponibles, v.precioVuelo,
                a.nombreAerolinea, a.codigoIATA,
                COALESCE(SUM(CASE WHEN r.estadoReserva = 'confirmada' THEN r.cantPasajeros ELSE 0 END), 0) AS vendidos,
                COALESCE(SUM(CASE WHEN r.estadoReserva = 'confirmada' THEN r.precioTotal ELSE 0 END), 0) AS ingresos
         $baseFrom
         GROUP BY v.idVuelo, a.nombreAerolinea, a.codigoIATA
         ORDER BY v.fechaHoraSalidaVuelo DESC
         LIMIT ? OFFSET ?", $tiposDatos, $paramsDatos);

} else {
    $where[] = "u.rol = 'cliente'";

    if ($busqueda !== '') {
        $where[] = '(CONCAT(u.nombre, " ", u.apellido) LIKE ? OR u.email LIKE ? OR u.nroDocumento LIKE ?)';
        $like = '%' . $busqueda . '%';
        $tipos .= 'sss';
        array_push($params, $like, $like, $like);
    }
    if ($estadoUsuario === 'activo')   { $where[] = 'u.activo = 1'; }
    if ($estadoUsuario === 'inactivo') { $where[] = 'u.activo = 0'; }
    if ($verificado === 'si') { $where[] = 'u.emailVerificado = 1'; }
    if ($verificado === 'no') { $where[] = 'u.emailVerificado = 0'; }

    $whereSql = implode(' AND ', $where);
    $baseFrom = "FROM usuario u WHERE $whereSql";

    $resumen = repAdminConsulta($link,
        "SELECT COUNT(*) AS total,
                COALESCE(SUM(u.activo = 1), 0) AS activos,
                COALESCE(SUM(u.emailVerificado = 1), 0) AS verificados
         $baseFrom", $tipos, $params)[0] ?? ['total' => 0, 'activos' => 0, 'verificados' => 0];

    $conCompras = repAdminConsulta($link,
        "SELECT COUNT(DISTINCT u.idUsuario) AS conCompras
         FROM usuario u
         INNER JOIN reserva r ON r.idUsuario = u.idUsuario AND r.estadoReserva = 'confirmada'
         WHERE $whereSql", $tipos, $params)[0] ?? ['conCompras' => 0];

    $total = (int)$resumen['total'];
    $totalPaginas = max(1, (int)ceil($total / $porPagina));
    $pag = min($pag, $totalPaginas);
    $offset = ($pag - 1) * $porPagina;

    $tiposDatos = $tipos . 'ii';
    $paramsDatos = array_merge($params, [$porPagina, $offset]);

    $filas = repAdminConsulta($link,
        "SELECT u.idUsuario, u.nombre, u.apellido, u.email, u.telefono,
                u.fechaNacimiento, u.activo, u.emailVerificado, u.fechaCreacion,
                COUNT(r.idReserva) AS reservasTotales,
                COALESCE(SUM(CASE WHEN r.estadoReserva = 'confirmada' THEN 1 ELSE 0 END), 0) AS compras,
                COALESCE(SUM(CASE WHEN r.estadoReserva = 'confirmada' THEN r.cantPasajeros ELSE 0 END), 0) AS pasajeros,
                COALESCE(SUM(CASE WHEN r.estadoReserva = 'confirmada' THEN r.precioTotal ELSE 0 END), 0) AS gastado
         FROM usuario u
         LEFT JOIN reserva r ON r.idUsuario = u.idUsuario
         WHERE $whereSql
         GROUP BY u.idUsuario
         ORDER BY u.fechaCreacion DESC
         LIMIT ? OFFSET ?", $tiposDatos, $paramsDatos);
}

$filtrosActivos = 0;
if ($desde || $hasta) $filtrosActivos++;
if ($busqueda !== '') $filtrosActivos++;
if ($tab !== 'usuarios' && $idAerolineaFiltro) $filtrosActivos++;
if ($tab === 'usuarios' && $estadoUsuario !== '') $filtrosActivos++;
if ($tab === 'usuarios' && $verificado !== '') $filtrosActivos++;

$desdeFila = $total === 0 ? 0 : ($pag - 1) * $porPagina + 1;
$hastaFila = min($pag * $porPagina, $total);

$tabs = [
    'ventas'   => 'Ventas',
    'vuelos'   => 'Vuelos',
    'usuarios' => 'Usuarios',
];
?>

<?php require __DIR__ . '/../components/alertToast.php'; ?>

<div>
    <nav class="breadcrumbCont" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= url('index.php?pagina=inicio') ?>">Inicio</a></li>
            <li class="breadcrumb-item"><a href="<?= url('index.php?pagina=reportes&seccion=listado') ?>">Reportes</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= $tabs[$tab] ?></li>
        </ol>
    </nav>

    <div class="contForm col-12 d-flex flex-column p-2 admin-content h-100">
        <h1 class="m-0 p-0 fs-2 fw-bold">Reportes del sistema</h1>
        <p class="m-0 p-0 mb-1 subt">Consultá las ventas confirmadas, los vuelos y los usuarios registrados.</p>

        <!-- Pestañas -->
        <ul class="nav nav-tabs mt-3" style="border-bottom-color: #dee2e6;">
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
                       href="<?= url('index.php?pagina=reportes&seccion=listado&tab=' . $clave) ?>">
                        <?= $label ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <!-- KPIs -->
        <div class="row g-3 g-md-2 mt-2 mb-3 justify-content-center">
            <?php if ($tab === 'ventas'): ?>
                <?php
                $ticketPromedio = (int)$resumen['ventas'] > 0
                    ? (float)$resumen['ingresos'] / (int)$resumen['ventas']
                    : 0;
                $cardsVentas = [
                    ['icon' => 'etiqueta.png', 'label' => 'Ventas confirmadas', 'valor' => number_format((int)$resumen['ventas'], 0, ',', '.')],
                    ['icon' => 'ceo.png', 'label' => 'Ingresos totales', 'valor' => '$ ' . number_format((float)$resumen['ingresos'], 2, ',', '.')],
                    ['icon' => 'avion.png', 'label' => 'Pasajeros', 'valor' => number_format((int)$resumen['pasajeros'], 0, ',', '.')],
                    ['icon' => 'etiqueta.png', 'label' => 'Ticket promedio', 'valor' => '$ ' . number_format($ticketPromedio, 2, ',', '.')],
                ];
                ?>
            <?php elseif ($tab === 'vuelos'): ?>
                <?php
                $cardsVentas = [
                    ['icon' => 'avion.png', 'label' => 'Vuelos', 'valor' => number_format((int)$resumen['vuelos'], 0, ',', '.')],
                    ['icon' => 'ceo.png', 'label' => 'Pasajeros confirmados', 'valor' => number_format((int)$resumen['pasajeros'], 0, ',', '.')],
                    ['icon' => 'etiqueta.png', 'label' => 'Ingresos confirmados', 'valor' => '$ ' . number_format((float)$resumen['ingresos'], 2, ',', '.')],
                    ['icon' => 'avion.png', 'label' => 'Asientos disponibles', 'valor' => number_format((int)$disponibles['disponibles'], 0, ',', '.')],
                ];
                ?>
            <?php else: ?>
                <?php
                $cardsVentas = [
                    ['icon' => 'ceo.png', 'label' => 'Clientes', 'valor' => number_format((int)$resumen['total'], 0, ',', '.')],
                    ['icon' => 'ceo.png', 'label' => 'Activos', 'valor' => number_format((int)$resumen['activos'], 0, ',', '.')],
                    ['icon' => 'ceo.png', 'label' => 'Email verificado', 'valor' => number_format((int)$resumen['verificados'], 0, ',', '.')],
                    ['icon' => 'etiqueta.png', 'label' => 'Con compras', 'valor' => number_format((int)$conCompras['conCompras'], 0, ',', '.')],
                ];
                ?>
            <?php endif; ?>

            <?php foreach ($cardsVentas as $card): ?>
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

        <div class="d-flex flex-column gap-2">
            <div class="table-responsive bg-white shadow rounded-3 pt-3 tablaContenedor">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center px-2 mb-3 gap-3 gap-sm-0">
                    <h2 class="m-0 p-0 fs-4 fw-bold">
                        <?= $tab === 'ventas' ? 'Reporte de Ventas' : ($tab === 'vuelos' ? 'Reporte de Vuelos' : 'Reporte de Usuarios') ?>
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
                        <input type="hidden" name="pagina" value="reportes">
                        <input type="hidden" name="seccion" value="listado">
                        <input type="hidden" name="tab" value="<?= $tab ?>">
                        <input type="hidden" name="porPagina" value="<?= $porPagina ?>">

                        <div class="row g-3">
                            <?php if ($tab !== 'usuarios'): ?>
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
                                    <label class="form-label fw-semibold mb-1" style="font-size:0.85rem;" for="filtroAerolinea">Aerolínea</label>
                                    <select class="form-select form-select-sm" id="filtroAerolinea" name="aerolinea">
                                        <option value="">Todas</option>
                                        <?php foreach ($aerolineas as $aero): ?>
                                            <option value="<?= (int)$aero['idAerolinea'] ?>"
                                                <?= $idAerolineaFiltro === (int)$aero['idAerolinea'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($aero['nombreAerolinea'] . ' (' . $aero['codigoIATA'] . ')') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php else: ?>
                                <div class="col-12 col-md-3">
                                    <label class="form-label fw-semibold mb-1" style="font-size:0.85rem;" for="filtroEstadoUsuario">Estado</label>
                                    <select class="form-select form-select-sm" id="filtroEstadoUsuario" name="estadoUsuario">
                                        <option value="">Todos</option>
                                        <option value="activo" <?= $estadoUsuario === 'activo' ? 'selected' : '' ?>>Activos</option>
                                        <option value="inactivo" <?= $estadoUsuario === 'inactivo' ? 'selected' : '' ?>>Inactivos</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-3">
                                    <label class="form-label fw-semibold mb-1" style="font-size:0.85rem;" for="filtroVerificado">Email verificado</label>
                                    <select class="form-select form-select-sm" id="filtroVerificado" name="verificado">
                                        <option value="">Todos</option>
                                        <option value="si" <?= $verificado === 'si' ? 'selected' : '' ?>>Sí</option>
                                        <option value="no" <?= $verificado === 'no' ? 'selected' : '' ?>>No</option>
                                    </select>
                                </div>
                            <?php endif; ?>

                            <div class="col-12 col-md-3">
                                <label class="form-label fw-semibold mb-1" style="font-size:0.85rem;" for="filtroBusquedaReporte">Buscar</label>
                                <input type="text" class="form-control form-control-sm js-buscador-reporte"
                                       id="filtroBusquedaReporte" name="busqueda" autocomplete="off"
                                       placeholder="<?= $tab === 'usuarios' ? 'Nombre, email o documento…' : 'Ruta, cliente o código…' ?>"
                                       value="<?= htmlspecialchars($busqueda) ?>">
                            </div>

                            <div class="col-12 d-flex gap-2 align-items-end">
                                <button type="submit" class="btn btn-primary btn-sm">Aplicar filtros</button>
                                <a href="<?= url('index.php?pagina=reportes&seccion=listado&tab=' . $tab) ?>"
                                   class="btn btn-outline-secondary btn-sm">Limpiar</a>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Tabla según pestaña -->
                <table class="table table-hover align-middle border-top mt-2">
                    <caption class="visually-hidden"><?= $tab === 'usuarios' ? 'Actividad de usuarios' : ($tab === 'vuelos' ? 'Rendimiento por ruta' : 'Ventas del período') ?></caption>
                    <?php if ($tab === 'ventas'): ?>
                        <thead>
                            <tr>
                                <th scope="col" class="fw-bold text-center">#</th>
                                <th scope="col" class="fw-bold">FECHA</th>
                                <th scope="col" class="fw-bold">CLIENTE</th>
                                <th scope="col" class="fw-bold d-none d-md-table-cell">AEROLÍNEA</th>
                                <th scope="col" class="fw-bold d-none d-sm-table-cell">RUTA</th>
                                <th scope="col" class="fw-bold d-none d-lg-table-cell">SALIDA</th>
                                <th scope="col" class="fw-bold text-center">PASAJEROS</th>
                                <th scope="col" class="fw-bold text-end">TOTAL</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($filas)): ?>
                                <tr><td colspan="8" class="text-center py-4 text-muted">No hay ventas confirmadas para los filtros seleccionados.</td></tr>
                            <?php else: foreach ($filas as $f): ?>
                                <tr>
                                    <td class="text-center fw-medium"><?= (int)$f['idReserva'] ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($f['fechaReserva'])) ?></td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-medium"><?= htmlspecialchars($f['nombre'] . ' ' . $f['apellido']) ?></span>
                                            <span class="text-muted" style="font-size:0.78rem;"><?= htmlspecialchars($f['email']) ?></span>
                                        </div>
                                    </td>
                                    <td class="d-none d-md-table-cell">
                                        <?= htmlspecialchars($f['nombreAerolinea']) ?>
                                        <span class="text-muted text-uppercase ms-1" style="font-size:0.75rem;"><?= htmlspecialchars($f['codigoIATA']) ?></span>
                                    </td>
                                    <td class="d-none d-sm-table-cell">
                                        <?= htmlspecialchars($f['origenVuelo']) ?> →
                                        <?= htmlspecialchars($f['destinoVuelo']) ?>
                                    </td>
                                    <td class="d-none d-lg-table-cell"><?= date('d/m/Y H:i', strtotime($f['fechaHoraSalidaVuelo'])) ?></td>
                                    <td class="text-center"><?= (int)$f['cantPasajeros'] ?></td>
                                    <td class="text-end fw-semibold">$ <?= number_format((float)$f['precioTotal'], 2, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>

                    <?php elseif ($tab === 'vuelos'): ?>
                        <thead>
                            <tr>
                                <th scope="col" class="fw-bold text-center">#</th>
                                <th scope="col" class="fw-bold">RUTA</th>
                                <th scope="col" class="fw-bold d-none d-md-table-cell">AEROLÍNEA</th>
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
                                <tr><td colspan="10" class="text-center py-4 text-muted">No hay vuelos para los filtros seleccionados.</td></tr>
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
                                    <td class="d-none d-md-table-cell">
                                        <?= htmlspecialchars($f['nombreAerolinea']) ?>
                                        <span class="text-muted text-uppercase ms-1" style="font-size:0.75rem;"><?= htmlspecialchars($f['codigoIATA']) ?></span>
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

                    <?php else: ?>
                        <thead>
                            <tr>
                                <th scope="col" class="fw-bold text-center">#</th>
                                <th scope="col" class="fw-bold">CLIENTE</th>
                                <th scope="col" class="fw-bold d-none d-md-table-cell">CONTACTO</th>
                                <th scope="col" class="fw-bold d-none d-lg-table-cell">REGISTRO</th>
                                <th scope="col" class="fw-bold text-center">ESTADO</th>
                                <th scope="col" class="fw-bold text-center d-none d-sm-table-cell">VERIFICADO</th>
                                <th scope="col" class="fw-bold text-center">RESERVAS</th>
                                <th scope="col" class="fw-bold text-center">COMPRAS</th>
                                <th scope="col" class="fw-bold text-center d-none d-md-table-cell">PASAJEROS</th>
                                <th scope="col" class="fw-bold text-end">GASTADO</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($filas)): ?>
                                <tr><td colspan="10" class="text-center py-4 text-muted">No hay usuarios para los filtros seleccionados.</td></tr>
                            <?php else: foreach ($filas as $f): ?>
                                <tr>
                                    <td class="text-center fw-medium"><?= (int)$f['idUsuario'] ?></td>
                                    <td>
                                        <div class="fw-medium"><?= htmlspecialchars($f['nombre'] . ' ' . $f['apellido']) ?></div>
                                        <div class="text-muted" style="font-size:0.78rem;"><?= htmlspecialchars($f['email']) ?></div>
                                    </td>
                                    <td class="d-none d-md-table-cell">
                                        <div><?= htmlspecialchars($f['telefono'] ?: '—') ?></div>
                                        <div class="text-muted" style="font-size:0.78rem;"><?= date('d/m/Y', strtotime($f['fechaNacimiento'])) ?></div>
                                    </td>
                                    <td class="d-none d-lg-table-cell"><?= date('d/m/Y', strtotime($f['fechaCreacion'])) ?></td>
                                    <td class="text-center">
                                        <span class="badge text-bg-<?= $f['activo'] ? 'success' : 'secondary' ?>">
                                            <?= $f['activo'] ? 'Activo' : 'Inactivo' ?>
                                        </span>
                                    </td>
                                    <td class="text-center d-none d-sm-table-cell">
                                        <?php if ($f['emailVerificado']): ?>
                                            <i class="bi bi-check-circle-fill text-success" title="Verificado"></i>
                                        <?php else: ?>
                                            <i class="bi bi-dash-circle text-muted" title="Sin verificar"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center"><?= (int)$f['reservasTotales'] ?></td>
                                    <td class="text-center fw-medium"><?= (int)$f['compras'] ?></td>
                                    <td class="text-center d-none d-md-table-cell"><?= (int)$f['pasajeros'] ?></td>
                                    <td class="text-end fw-semibold">$ <?= number_format((float)$f['gastado'], 2, ',', '.') ?></td>
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
                                       href="<?= $pag > 1 ? repAdminUrl(1) : '#' ?>">«</a>
                                </li>
                                <li class="page-item <?= $pag <= 1 ? 'disabled' : '' ?>">
                                    <a class="page-link rounded-2 border-0 <?= $pag <= 1 ? 'text-muted' : '' ?>"
                                       href="<?= $pag > 1 ? repAdminUrl($pag - 1) : '#' ?>">‹</a>
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
                                           href="<?= repAdminUrl($i) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($fin < $totalPaginas): ?>
                                    <li class="page-item disabled"><span class="page-link border-0 text-muted rounded-2">…</span></li>
                                <?php endif; ?>

                                <li class="page-item <?= $pag >= $totalPaginas ? 'disabled' : '' ?>">
                                    <a class="page-link rounded-2 border-0 <?= $pag >= $totalPaginas ? 'text-muted' : '' ?>"
                                       href="<?= $pag < $totalPaginas ? repAdminUrl($pag + 1) : '#' ?>">›</a>
                                </li>
                                <li class="page-item <?= $pag >= $totalPaginas ? 'disabled' : '' ?>">
                                    <a class="page-link rounded-2 border-0 <?= $pag >= $totalPaginas ? 'text-muted' : '' ?>"
                                       href="<?= $pag < $totalPaginas ? repAdminUrl($totalPaginas) : '#' ?>">»</a>
                                </li>
                            </ul>
                        </nav>

                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted" style="font-size:0.8rem;">Por página:</span>
                            <select class="form-select form-select-sm border-0 bg-light rounded-2"
                                    style="width:auto; font-size:0.8rem; cursor:pointer;"
                                    onchange="window.location.href=this.value" aria-label="Resultados por página">
                                <?php foreach ($porPaginaPermitidos as $op): ?>
                                    <option value="<?= repAdminUrl(1, $op) ?>" <?= $op === $porPagina ? 'selected' : '' ?>>
                                        <?= $op ?> por página
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
