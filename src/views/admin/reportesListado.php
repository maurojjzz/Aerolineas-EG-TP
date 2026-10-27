<?php
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../components/reporteBloques.php';

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
$pag = ($_GET['imprimir'] ?? '') === '1' ? 1 : max(1, (int)($_GET['pag'] ?? 1));
$porPaginaRaw = (int)($_GET['porPagina'] ?? 10);
$porPagina = in_array($porPaginaRaw, $porPaginaPermitidos, true) ? $porPaginaRaw : 10;
$modoImpresion = ($_GET['imprimir'] ?? '') === '1';
// El informe imprimible sintetiza: muestra un detalle acotado, no todos los registros.
$limiteDetalleImpresion = 25;
if ($modoImpresion) {
    $porPagina = $limiteDetalleImpresion;
    $_GET['pag'] = 1;
}

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
                COALESCE(SUM(r.cantPasajeros), 0) AS pasajeros,
                COUNT(DISTINCT r.idUsuario) AS clientes
         $baseFrom", $tipos, $params)[0] ?? ['ventas' => 0, 'ingresos' => 0, 'pasajeros' => 0, 'clientes' => 0];

    // Análisis: mismas condiciones, sin el filtro de estado para medir cancelaciones.
    $whereSinEstado = implode(' AND ', array_slice($where, 1)) ?: '1 = 1';
    $estados = repAdminConsulta($link,
        "SELECT r.estadoReserva AS estado, COUNT(*) AS cantidad
         FROM reserva r
         INNER JOIN usuario u ON u.idUsuario = r.idUsuario
         INNER JOIN vuelo v ON v.idVuelo = r.idVuelo
         INNER JOIN aerolinea a ON a.idAerolinea = v.idAerolinea
         WHERE $whereSinEstado
         GROUP BY r.estadoReserva", $tipos, $params);
    $porEstado = array_column($estados, 'cantidad', 'estado');
    $reservasTotales = array_sum($porEstado);

    $analisisAerolineas = repAdminConsulta($link,
        "SELECT a.nombreAerolinea AS nombre, COUNT(*) AS ventas,
                SUM(r.cantPasajeros) AS pasajeros, SUM(r.precioTotal) AS ingresos
         $baseFrom
         GROUP BY a.idAerolinea, a.nombreAerolinea
         ORDER BY ingresos DESC LIMIT 6", $tipos, $params);

    $analisisRutas = repAdminConsulta($link,
        "SELECT CONCAT(v.codigoOrigen, ' → ', v.codigoDestino) AS ruta, COUNT(*) AS ventas,
                SUM(r.cantPasajeros) AS pasajeros, SUM(r.precioTotal) AS ingresos
         $baseFrom
         GROUP BY v.codigoOrigen, v.codigoDestino
         ORDER BY ingresos DESC LIMIT 6", $tipos, $params);

    $analisisMeses = repAdminConsulta($link,
        "SELECT DATE_FORMAT(r.fechaReserva, '%Y-%m') AS mes, COUNT(*) AS ventas,
                SUM(r.precioTotal) AS ingresos
         $baseFrom
         GROUP BY mes ORDER BY mes DESC LIMIT 6", $tipos, $params);

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
        "SELECT COALESCE(SUM(v.asientosDisponibles), 0) AS disponibles,
                COALESCE(SUM(v.asientosTotales), 0) AS asientos,
                COALESCE(SUM(v.fechaHoraSalidaVuelo >= NOW()), 0) AS proximos
         FROM vuelo v INNER JOIN aerolinea a ON a.idAerolinea = v.idAerolinea
         WHERE $whereSql", $tipos, $params)[0] ?? ['disponibles' => 0, 'asientos' => 0, 'proximos' => 0];

    // Una fila por vuelo (evita duplicar asientos por el JOIN con reservas).
    $porVuelo = "SELECT v.idVuelo, v.idAerolinea, v.codigoOrigen, v.codigoDestino, v.asientosTotales,
                        v.fechaHoraSalidaVuelo,
                        COALESCE(SUM(CASE WHEN r.estadoReserva = 'confirmada' THEN r.cantPasajeros ELSE 0 END), 0) AS vendidos,
                        COALESCE(SUM(CASE WHEN r.estadoReserva = 'confirmada' THEN r.precioTotal ELSE 0 END), 0) AS ingresos
                 $baseFrom
                 GROUP BY v.idVuelo";

    $sinVentas = (int)(repAdminConsulta($link,
        "SELECT COUNT(*) AS n FROM ($porVuelo) t WHERE t.vendidos = 0", $tipos, $params)[0]['n'] ?? 0);

    $analisisAerolineas = repAdminConsulta($link,
        "SELECT al.nombreAerolinea AS nombre, COUNT(*) AS vuelos,
                SUM(t.vendidos) AS vendidos, SUM(t.asientosTotales) AS asientos, SUM(t.ingresos) AS ingresos
         FROM ($porVuelo) t INNER JOIN aerolinea al ON al.idAerolinea = t.idAerolinea
         GROUP BY al.idAerolinea, al.nombreAerolinea
         ORDER BY ingresos DESC LIMIT 6", $tipos, $params);

    $sqlRutas = "SELECT CONCAT(t.codigoOrigen, ' → ', t.codigoDestino) AS ruta, COUNT(*) AS vuelos,
                        SUM(t.vendidos) AS vendidos, SUM(t.asientosTotales) AS asientos, SUM(t.ingresos) AS ingresos,
                        SUM(t.vendidos) / NULLIF(SUM(t.asientosTotales), 0) AS ocupacion
                 FROM ($porVuelo) t
                 GROUP BY t.codigoOrigen, t.codigoDestino";
    $analisisRutas = repAdminConsulta($link, "$sqlRutas ORDER BY ingresos DESC LIMIT 6", $tipos, $params);
    $analisisRutasBajas = repAdminConsulta($link, "$sqlRutas ORDER BY ocupacion ASC, vuelos DESC LIMIT 6", $tipos, $params);

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
                COALESCE(SUM(u.emailVerificado = 1), 0) AS verificados,
                COALESCE(SUM(u.fechaCreacion >= NOW() - INTERVAL 30 DAY), 0) AS nuevos30
         $baseFrom", $tipos, $params)[0] ?? ['total' => 0, 'activos' => 0, 'verificados' => 0, 'nuevos30' => 0];

    $conCompras = repAdminConsulta($link,
        "SELECT COUNT(DISTINCT u.idUsuario) AS conCompras
         FROM usuario u
         INNER JOIN reserva r ON r.idUsuario = u.idUsuario AND r.estadoReserva = 'confirmada'
         WHERE $whereSql", $tipos, $params)[0] ?? ['conCompras' => 0];

    $gastoClientes = repAdminConsulta($link,
        "SELECT COALESCE(SUM(r.precioTotal), 0) AS gastado
         FROM usuario u
         INNER JOIN reserva r ON r.idUsuario = u.idUsuario AND r.estadoReserva = 'confirmada'
         WHERE $whereSql", $tipos, $params)[0]['gastado'] ?? 0;

    $analisisTopClientes = repAdminConsulta($link,
        "SELECT CONCAT(u.nombre, ' ', u.apellido) AS cliente, COUNT(*) AS compras,
                SUM(r.precioTotal) AS gastado
         FROM usuario u
         INNER JOIN reserva r ON r.idUsuario = u.idUsuario AND r.estadoReserva = 'confirmada'
         WHERE $whereSql
         GROUP BY u.idUsuario, u.nombre, u.apellido
         ORDER BY gastado DESC LIMIT 6", $tipos, $params);

    $analisisMeses = repAdminConsulta($link,
        "SELECT DATE_FORMAT(u.fechaCreacion, '%Y-%m') AS mes, COUNT(*) AS altas,
                SUM(u.emailVerificado = 1) AS verificados
         $baseFrom
         GROUP BY mes ORDER BY mes DESC LIMIT 6", $tipos, $params);

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

function montoCompacto(float $monto): string {
    $signo = $monto < 0 ? '-' : '';
    $valor = abs($monto);
    if ($valor >= 1000000) {
        return $signo . number_format($valor / 1000000, 1, ',', '.') . 'M';
    }
    if ($valor >= 1000) {
        return $signo . number_format($valor / 1000, 1, ',', '.') . 'K';
    }
    return $signo . number_format($valor, 0, ',', '.');
}
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

// ── Indicadores, análisis y síntesis (compartidos por la pantalla y el informe) ──
$conclusiones = [];
$nombreAerolineaFiltro = '';
foreach ($aerolineas as $aero) {
    if ((int)$aero['idAerolinea'] === $idAerolineaFiltro) $nombreAerolineaFiltro = $aero['nombreAerolinea'];
}

if ($tab === 'ventas') {
    $ticketPromedio = (int)$resumen['ventas'] > 0 ? (float)$resumen['ingresos'] / (int)$resumen['ventas'] : 0;
    $canceladas = (int)($porEstado['cancelada'] ?? 0);
    $pendientes = (int)($porEstado['pendiente de pago'] ?? 0);
    $tasaCancelacion = $reservasTotales > 0 ? $canceladas / $reservasTotales : 0;
    $ingresosTotales = max(1, (float)$resumen['ingresos']);
    $maxMes = max(1, ...array_map(fn($f) => (float)$f['ingresos'], $analisisMeses ?: [['ingresos' => 1]]));

    $indicadores = [
        ['label' => 'Ventas confirmadas', 'valor' => repNum($resumen['ventas']), 'detalle' => repPct($resumen['ventas'], $reservasTotales) . ' de las reservas'],
        ['label' => 'Ingresos', 'valor' => '$ ' . montoCompacto((float)$resumen['ingresos']), 'titulo' => repPesos($resumen['ingresos'])],
        ['label' => 'Pasajeros', 'valor' => repNum($resumen['pasajeros']), 'detalle' => repNum((int)$resumen['ventas'] > 0 ? $resumen['pasajeros'] / $resumen['ventas'] : 0, 1) . ' por venta'],
        ['label' => 'Ticket promedio', 'valor' => '$ ' . montoCompacto($ticketPromedio), 'titulo' => repPesos($ticketPromedio)],
        ['label' => 'Clientes compradores', 'valor' => repNum($resumen['clientes'])],
        ['label' => 'Cancelación', 'valor' => repPct($canceladas, $reservasTotales, 1), 'detalle' => repNum($canceladas) . ' canceladas · ' . repNum($pendientes) . ' pendientes', 'tono' => $tasaCancelacion > 0.2 ? 'alerta' : ''],
    ];
    $secciones = [
        ['titulo' => 'Ingresos por aerolínea',
         'columnas' => [['label' => 'Aerolínea'], ['label' => 'Ventas', 'clase' => 'text-end'], ['label' => 'Ingresos', 'clase' => 'text-end'], ['label' => 'Participación']],
         'filas' => array_map(fn($f) => [$f['nombre'], repNum($f['ventas']), repPesos($f['ingresos']), repBarra($f['ingresos'] / $ingresosTotales * 100, repPct($f['ingresos'], $ingresosTotales))], $analisisAerolineas)],
        ['titulo' => 'Rutas con más ingresos',
         'columnas' => [['label' => 'Ruta'], ['label' => 'Pasajeros', 'clase' => 'text-end'], ['label' => 'Ingresos', 'clase' => 'text-end']],
         'filas' => array_map(fn($f) => [$f['ruta'], repNum($f['pasajeros']), repPesos($f['ingresos'])], $analisisRutas)],
        ['titulo' => 'Evolución mensual',
         'columnas' => [['label' => 'Mes'], ['label' => 'Ventas', 'clase' => 'text-end'], ['label' => 'Ingresos']],
         'filas' => array_map(fn($f) => [repMes($f['mes']), repNum($f['ventas']), repBarra($f['ingresos'] / $maxMes * 100, '$ ' . montoCompacto((float)$f['ingresos']))], $analisisMeses)],
    ];

    if ((int)$resumen['ventas'] > 0) {
        $conclusiones[] = 'Se confirmaron ' . repNum($resumen['ventas']) . ' ventas por ' . repPesos($resumen['ingresos'])
            . ' (' . repNum($resumen['pasajeros']) . ' pasajeros, ticket promedio de ' . repPesos($ticketPromedio) . ').';
        if (!$idAerolineaFiltro && count($analisisAerolineas) > 1) {
            $conclusiones[] = $analisisAerolineas[0]['nombre'] . ' lidera con el ' . repPct($analisisAerolineas[0]['ingresos'], $ingresosTotales) . ' de los ingresos.';
        }
        if ($analisisRutas) {
            $conclusiones[] = 'La ruta ' . $analisisRutas[0]['ruta'] . ' es la que más factura: ' . repPesos($analisisRutas[0]['ingresos'])
                . ' (' . repPct($analisisRutas[0]['ingresos'], $ingresosTotales) . ' del total).';
        }
        if ($tendencia = repTendenciaMensual($analisisMeses, 'ingresos', 'los ingresos')) $conclusiones[] = $tendencia;
    } else {
        $conclusiones[] = 'No hay ventas confirmadas para los filtros seleccionados.';
    }
    if ($reservasTotales > 0) {
        $conclusiones[] = 'La tasa de cancelación es de ' . repPct($canceladas, $reservasTotales, 1) . ' (' . repNum($canceladas) . ' reservas)'
            . ($tasaCancelacion > 0.2 ? ', por encima del 20% esperable.' : '.')
            . ($pendientes > 0 ? ' Quedan ' . repNum($pendientes) . ' reservas pendientes de pago.' : '');
    }

    $detalleImpresion = [
        'titulo' => 'Detalle de ventas',
        'columnas' => [['label' => '#'], ['label' => 'Fecha'], ['label' => 'Cliente'], ['label' => 'Aerolínea'], ['label' => 'Ruta'], ['label' => 'Pax', 'clase' => 'text-center'], ['label' => 'Total', 'clase' => 'text-end']],
        'filas' => array_map(fn($f) => [(int)$f['idReserva'], date('d/m/Y', strtotime($f['fechaReserva'])), $f['nombre'] . ' ' . $f['apellido'], $f['nombreAerolinea'], $f['codigoOrigen'] . ' → ' . $f['codigoDestino'], (int)$f['cantPasajeros'], repPesos($f['precioTotal'])], $filas),
        'totales' => ['', '', 'Total del período', '', '', repNum($resumen['pasajeros']), repPesos($resumen['ingresos'])],
    ];

} elseif ($tab === 'vuelos') {
    $asientos = (int)$disponibles['asientos'];
    $ocupacionGeneral = $asientos > 0 ? (int)$resumen['pasajeros'] / $asientos * 100 : 0;
    $filaRuta = fn($f) => [$f['ruta'], repNum($f['vuelos']), repBarra((float)$f['ocupacion'] * 100, repPct($f['vendidos'], $f['asientos'], 1)), '$ ' . montoCompacto((float)$f['ingresos'])];
    $colsRuta = [['label' => 'Ruta'], ['label' => 'Vuelos', 'clase' => 'text-end'], ['label' => 'Ocupación'], ['label' => 'Ingresos', 'clase' => 'text-end']];

    $indicadores = [
        ['label' => 'Vuelos', 'valor' => repNum($resumen['vuelos']), 'detalle' => repNum($disponibles['proximos']) . ' próximos'],
        ['label' => 'Ocupación promedio', 'valor' => repPct($resumen['pasajeros'], $asientos, 1), 'tono' => $ocupacionGeneral >= 70 ? 'ok' : ($ocupacionGeneral < 30 && $asientos > 0 ? 'alerta' : '')],
        ['label' => 'Pasajeros', 'valor' => repNum($resumen['pasajeros']), 'detalle' => 'de ' . repNum($asientos) . ' asientos'],
        ['label' => 'Ingresos', 'valor' => '$ ' . montoCompacto((float)$resumen['ingresos']), 'titulo' => repPesos($resumen['ingresos'])],
        ['label' => 'Ingreso por vuelo', 'valor' => '$ ' . montoCompacto((int)$resumen['vuelos'] > 0 ? (float)$resumen['ingresos'] / (int)$resumen['vuelos'] : 0)],
        ['label' => 'Vuelos sin ventas', 'valor' => repNum($sinVentas), 'detalle' => repPct($sinVentas, $resumen['vuelos']) . ' del total', 'tono' => $sinVentas > 0 ? 'alerta' : ''],
    ];
    $secciones = [
        ['titulo' => 'Rendimiento por aerolínea',
         'columnas' => [['label' => 'Aerolínea'], ['label' => 'Vuelos', 'clase' => 'text-end'], ['label' => 'Ocupación'], ['label' => 'Ingresos', 'clase' => 'text-end']],
         'filas' => array_map(fn($f) => [$f['nombre'], repNum($f['vuelos']), repBarra((float)$f['asientos'] > 0 ? $f['vendidos'] / $f['asientos'] * 100 : 0, repPct($f['vendidos'], $f['asientos'], 1)), '$ ' . montoCompacto((float)$f['ingresos'])], $analisisAerolineas)],
        ['titulo' => 'Rutas con más ingresos', 'columnas' => $colsRuta, 'filas' => array_map($filaRuta, $analisisRutas)],
        ['titulo' => 'Rutas con menor ocupación', 'columnas' => $colsRuta, 'filas' => array_map($filaRuta, $analisisRutasBajas)],
    ];

    if ((int)$resumen['vuelos'] > 0) {
        $conclusiones[] = repNum($resumen['vuelos']) . ' vuelos con una ocupación promedio de ' . repPct($resumen['pasajeros'], $asientos, 1)
            . ' (' . repNum($resumen['pasajeros']) . ' pasajeros sobre ' . repNum($asientos) . ' asientos); ' . repNum($disponibles['proximos']) . ' todavía no salieron.';
        if ($sinVentas > 0) {
            $conclusiones[] = repNum($sinVentas) . ' vuelos (' . repPct($sinVentas, $resumen['vuelos']) . ') no registran ninguna venta confirmada.';
        }
        if (!$idAerolineaFiltro && count($analisisAerolineas) > 1) {
            $conclusiones[] = $analisisAerolineas[0]['nombre'] . ' es la aerolínea con más ingresos (' . repPesos($analisisAerolineas[0]['ingresos']) . ').';
        }
        if ($analisisRutas) {
            $conclusiones[] = 'La ruta más rentable es ' . $analisisRutas[0]['ruta'] . ' (' . repPesos($analisisRutas[0]['ingresos']) . ')'
                . ($analisisRutasBajas ? '; la de menor ocupación es ' . $analisisRutasBajas[0]['ruta'] . ' (' . repPct($analisisRutasBajas[0]['vendidos'], $analisisRutasBajas[0]['asientos'], 1) . ').' : '.');
        }
    } else {
        $conclusiones[] = 'No hay vuelos para los filtros seleccionados.';
    }

    $detalleImpresion = [
        'titulo' => 'Detalle de vuelos',
        'columnas' => [['label' => '#'], ['label' => 'Ruta'], ['label' => 'Aerolínea'], ['label' => 'Salida'], ['label' => 'Vendidos', 'clase' => 'text-center'], ['label' => 'Ocupación', 'clase' => 'text-end'], ['label' => 'Ingresos', 'clase' => 'text-end']],
        'filas' => array_map(fn($f) => [(int)$f['idVuelo'], $f['codigoOrigen'] . ' → ' . $f['codigoDestino'], $f['nombreAerolinea'], date('d/m/Y H:i', strtotime($f['fechaHoraSalidaVuelo'])), (int)$f['vendidos'] . ' / ' . (int)$f['asientosTotales'], repPct($f['vendidos'], $f['asientosTotales'], 1), repPesos($f['ingresos'])], $filas),
        'totales' => ['', 'Total del período', '', '', repNum($resumen['pasajeros']) . ' / ' . repNum($asientos), repPct($resumen['pasajeros'], $asientos, 1), repPesos($resumen['ingresos'])],
    ];

} else {
    $totalClientes = (int)$resumen['total'];
    $compradores = (int)$conCompras['conCompras'];
    $noVerificados = $totalClientes - (int)$resumen['verificados'];
    $embudo = [
        ['Registrados', $totalClientes],
        ['Email verificado', (int)$resumen['verificados']],
        ['Activos', (int)$resumen['activos']],
        ['Con compras', $compradores],
    ];

    $indicadores = [
        ['label' => 'Clientes', 'valor' => repNum($totalClientes), 'detalle' => repNum($resumen['nuevos30']) . ' nuevos en 30 días'],
        ['label' => 'Activos', 'valor' => repNum($resumen['activos']), 'detalle' => repPct($resumen['activos'], $totalClientes)],
        ['label' => 'Email verificado', 'valor' => repNum($resumen['verificados']), 'detalle' => repPct($resumen['verificados'], $totalClientes)],
        ['label' => 'Con compras', 'valor' => repNum($compradores), 'detalle' => 'conversión ' . repPct($compradores, $totalClientes, 1)],
        ['label' => 'Gasto total', 'valor' => '$ ' . montoCompacto((float)$gastoClientes), 'titulo' => repPesos($gastoClientes)],
        ['label' => 'Gasto por comprador', 'valor' => '$ ' . montoCompacto($compradores > 0 ? (float)$gastoClientes / $compradores : 0)],
    ];
    $secciones = [
        ['titulo' => 'Embudo de clientes',
         'columnas' => [['label' => 'Etapa'], ['label' => 'Clientes', 'clase' => 'text-end'], ['label' => 'Del total']],
         'filas' => array_map(fn($e) => [$e[0], repNum($e[1]), repBarra($totalClientes > 0 ? $e[1] / $totalClientes * 100 : 0, repPct($e[1], $totalClientes))], $embudo)],
        ['titulo' => 'Clientes que más gastaron',
         'columnas' => [['label' => 'Cliente'], ['label' => 'Compras', 'clase' => 'text-end'], ['label' => 'Gastado', 'clase' => 'text-end']],
         'filas' => array_map(fn($f) => [$f['cliente'], repNum($f['compras']), repPesos($f['gastado'])], $analisisTopClientes),
         'vacio' => 'Todavía no hay compras confirmadas.'],
        ['titulo' => 'Altas por mes',
         'columnas' => [['label' => 'Mes'], ['label' => 'Altas', 'clase' => 'text-end'], ['label' => 'Verificaron']],
         'filas' => array_map(fn($f) => [repMes($f['mes']), repNum($f['altas']), repBarra($f['altas'] > 0 ? $f['verificados'] / $f['altas'] * 100 : 0, repPct($f['verificados'], $f['altas']))], $analisisMeses)],
    ];

    if ($totalClientes > 0) {
        $conclusiones[] = repNum($totalClientes) . ' clientes registrados, ' . repNum($resumen['nuevos30']) . ' de ellos en los últimos 30 días.';
        $conclusiones[] = 'El ' . repPct($compradores, $totalClientes, 1) . ' de los clientes realizó al menos una compra, con un gasto promedio de '
            . repPesos($compradores > 0 ? (float)$gastoClientes / $compradores : 0) . ' por comprador.';
        if ($noVerificados > 0) {
            $conclusiones[] = repNum($noVerificados) . ' clientes (' . repPct($noVerificados, $totalClientes) . ') todavía no verificaron su email.';
        }
        if ($analisisTopClientes) {
            $conclusiones[] = 'El cliente con mayor gasto es ' . $analisisTopClientes[0]['cliente'] . ' (' . repPesos($analisisTopClientes[0]['gastado']) . ' en ' . repNum($analisisTopClientes[0]['compras']) . ' compras).';
        }
        if ($tendencia = repTendenciaMensual($analisisMeses, 'altas', 'las altas')) $conclusiones[] = $tendencia;
    } else {
        $conclusiones[] = 'No hay clientes para los filtros seleccionados.';
    }

    $detalleImpresion = [
        'titulo' => 'Detalle de clientes',
        'columnas' => [['label' => 'Cliente'], ['label' => 'Email'], ['label' => 'Registro'], ['label' => 'Estado'], ['label' => 'Verificado', 'clase' => 'text-center'], ['label' => 'Compras', 'clase' => 'text-center'], ['label' => 'Gastado', 'clase' => 'text-end']],
        'filas' => array_map(fn($f) => [$f['nombre'] . ' ' . $f['apellido'], $f['email'], date('d/m/Y', strtotime($f['fechaCreacion'])), (int)$f['activo'] === 1 ? 'Activo' : 'Inactivo', (int)$f['emailVerificado'] === 1 ? 'Sí' : 'No', (int)$f['compras'], repPesos($f['gastado'])], $filas),
        'totales' => ['Total', '', '', '', '', '', repPesos($gastoClientes)],
    ];
}

// ── Informe imprimible: documento propio, sin la interfaz de administración ──
if ($modoImpresion) {
    $filtrosTexto = [];
    if ($nombreAerolineaFiltro !== '') $filtrosTexto[] = 'Aerolínea: ' . $nombreAerolineaFiltro;
    if ($busqueda !== '') $filtrosTexto[] = 'Búsqueda: "' . $busqueda . '"';
    if ($tab === 'usuarios' && $estadoUsuario !== '') $filtrosTexto[] = 'Estado: ' . $estadoUsuario;
    if ($tab === 'usuarios' && $verificado !== '') $filtrosTexto[] = 'Verificado: ' . $verificado;

    $mostrados = count($filas);
    $detalleImpresion['nota'] = $total > $mostrados
        ? 'Se listan los ' . $mostrados . ' registros más recientes de ' . repNum($total) . '. Los totales y el análisis consideran todos los registros.'
        : 'Se listan los ' . $mostrados . ' registros del período.';

    $paramsVolver = $_GET;
    unset($paramsVolver['imprimir']);

    $informe = [
        'titulo'       => 'Reporte de ' . mb_strtolower($tabs[$tab]),
        'alcance'      => $tab === 'usuarios' ? 'Clientes de la plataforma' : ($nombreAerolineaFiltro !== '' ? $nombreAerolineaFiltro : 'Todas las aerolíneas'),
        'meta'         => [
            'Período' => $tab === 'usuarios' ? 'Histórico' : (($desde ? date('d/m/Y', strtotime($desde)) : 'Inicio') . ' al ' . ($hasta ? date('d/m/Y', strtotime($hasta)) : 'hoy')),
            'Filtros' => $filtrosTexto ? implode(' · ', $filtrosTexto) : 'Ninguno',
            'Registros' => repNum($total),
        ],
        'conclusiones' => $conclusiones,
        'indicadores'  => $indicadores,
        'secciones'    => $secciones,
        'detalle'      => $detalleImpresion,
        'volver'       => 'index.php?' . http_build_query($paramsVolver),
    ];
    require __DIR__ . '/../components/reporteImpresion.php';
    return;
}
?>

<?php require __DIR__ . '/../components/alertToast.php'; ?>

<div>
    <div class="reporte-portada">
        <div>
            <h1>Reporte de <?= $tab === 'ventas' ? 'ventas' : ($tab === 'vuelos' ? 'vuelos' : 'usuarios') ?></h1>
            <p>Volaré · <?= $idAerolineaFiltro ? 'Aerolínea filtrada' : 'Todas las aerolíneas' ?></p>
        </div>
        <div class="reporte-meta">
            <strong>Informe de gestión</strong><br>
            Generado: <?= date('d/m/Y H:i') ?><br>
            <span class="reporte-registros">Registros incluidos: <?= number_format($total, 0, ',', '.') ?></span><br>
            <?= $desde || $hasta ? 'Período: ' . ($desde ? date('d/m/Y', strtotime($desde)) : 'sin inicio') . ' a ' . ($hasta ? date('d/m/Y', strtotime($hasta)) : 'sin fin') : 'Período: todos los registros' ?>
        </div>
    </div>
    <nav class="breadcrumbCont" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= url('index.php?pagina=aerolinea&seccion=listado') ?>">Administración</a></li>
            <li class="breadcrumb-item"><a href="<?= url('index.php?pagina=reportes&seccion=listado') ?>">Reportes</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= $tabs[$tab] ?></li>
        </ol>
    </nav>

    <div class="contForm col-12 d-flex flex-column p-2 admin-content h-100">
        <h1 class="m-0 p-0 fs-2 fw-bold reportes-titulo">Reportes del sistema</h1>
        <p class="m-0 p-0 mb-1 subt">Consultá las ventas confirmadas, los vuelos y los usuarios registrados.</p>

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
                       href="<?= url('index.php?pagina=reportes&seccion=listado&tab=' . $clave) ?>">
                        <?= $label ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <!-- Indicadores y análisis -->
        <?php repResumen($indicadores); ?>
        <section class="rep-analisis" aria-label="Análisis del reporte">
            <?php foreach ($secciones as $sec): ?>
                <?php repTablaMini($sec['titulo'], $sec['columnas'], $sec['filas'], $sec['vacio'] ?? 'Sin datos para el período.'); ?>
            <?php endforeach; ?>
        </section>

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
                <table class="table table-hover align-middle border-top mt-2 reporte-tabla reporte-<?= htmlspecialchars($tab) ?>">
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
                                <tr><td colspan="8" class="text-center py-4 text-muted">Todavía no hay ventas confirmadas que coincidan con los filtros seleccionados.</td></tr>
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
                                <tr><td colspan="10" class="text-center py-4 text-muted">Todavía no hay vuelos que coincidan con los filtros seleccionados.</td></tr>
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
                                    <td class="text-end fw-semibold d-none d-sm-table-cell">$ <?= montoCompacto((float)$f['ingresos']) ?></td>
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
                                <tr><td colspan="10" class="text-center py-4 text-muted">Todavía no hay usuarios que coincidan con los filtros seleccionados.</td></tr>
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
                                    <td class="text-end fw-semibold">$ <?= montoCompacto((float)$f['gastado']) ?></td>
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
