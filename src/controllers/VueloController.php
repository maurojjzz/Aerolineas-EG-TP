<?php

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/Vuelos.php';
require_once __DIR__ . '/PromocionController.php';

class VueloController {

    private $conexion;
    private $promoCtrl;

    public function __construct($conexion) {
        $this->conexion = $conexion;
        $this->promoCtrl = new PromocionController($conexion);
    }

    public function crearVuelo(int $idAerolinea): void {
        $origen = trim($_POST['origen']   ?? '');
        $codigoOrigen = trim($_POST['codigo_origen'] ?? '');
        $destino = trim($_POST['destino']  ?? '');
        $codigoDestino = trim($_POST['codigo_destino']  ?? '');
        $fechaSalida = trim($_POST['fecha']    ?? '');
        $horaSalida = trim($_POST['hora']     ?? '');
        $fechaLlegada = trim($_POST['fecha_llegada'] ?? '');
        $horaLlegada = trim($_POST['hora_llegada'] ?? '');
        $asientosTot = (int)($_POST['asientos_totales'] ?? 0);
        $precio = (float)($_POST['precio'] ?? 0);

        if (!$origen || !$codigoOrigen || !$destino || !$codigoDestino || !$fechaSalida || !$horaSalida || !$fechaLlegada || !$horaLlegada || $asientosTot <= 0 || $precio <= 0 ||  strcasecmp($origen, $destino) === 0) {
            flash_set('error', 'Faltan campos obligatorios o valores inválidos.');
            redirect("index.php?pagina=vuelo");
        }

        if (strcasecmp($origen, $destino) === 0) {
            flash_set('error', 'El origen y el destino no pueden ser iguales.');
            redirect("index.php?pagina=vuelo");
        }

        if ($codigoOrigen && $codigoDestino && $codigoOrigen === $codigoDestino) {
            flash_set('error', 'El origen y el destino no pueden ser el mismo aeropuerto.');
            redirect("index.php?pagina=vuelo");
        }

        // Validar que la fecha sea hoy o futura
        $fechaHoraSalida = $fechaSalida . ' ' . $horaSalida . ':00';
        if (strtotime($fechaHoraSalida) < time()) {
            flash_set('error', 'La fecha y hora de salida no puede ser en el pasado.');
            redirect("index.php?pagina=vuelo");
        }

        $fechaHoraLlegada = $fechaLlegada . ' ' . $horaLlegada . ':00';

        $asientosDisp = $asientosTot;

        if (strtotime($fechaHoraLlegada) <= strtotime($fechaHoraSalida)) {
            flash_set('error', 'La llegada debe ser posterior a la salida.');
            redirect("index.php?pagina=vuelo");
        }

        $query = "INSERT INTO vuelo (idAerolinea, origenVuelo, codigoOrigen, destinoVuelo, codigoDestino, fechaHoraSalidaVuelo, fechaHoraLlegadaVuelo, asientosTotales, asientosDisponibles, precioVuelo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($this->conexion, $query);
        
        mysqli_stmt_bind_param(
            $stmt, 
            'issssssiid',    
            $idAerolinea, 
            $origen, 
            $codigoOrigen,
            $destino, 
            $codigoDestino,
            $fechaHoraSalida, 
            $fechaHoraLlegada, 
            $asientosTot, 
            $asientosDisp, 
            $precio
        );

        if (!$stmt) {
            flash_set('error', 'Error interno del servidor.');
            redirect("index.php?pagina=vuelo");
        }

        try {
            mysqli_stmt_execute($stmt);
        } catch (mysqli_sql_exception $e) {
            mysqli_stmt_close($stmt);
            flash_set('error', 'Error al crear el vuelo.');
            redirect("index.php?pagina=vuelo");
        }

        mysqli_stmt_close($stmt);
        flash_set('success', 'Vuelo creado correctamente.');
        redirect("index.php?pagina=vuelos");
    }

    public function editarVuelo(int $idVuelo, int $idAerolinea): void {
        $origen = trim($_POST['origen']   ?? '');
        $codigoOrigen = trim($_POST['codigo_origen'] ?? '');
        $destino = trim($_POST['destino']  ?? '');
        $codigoDestino = trim($_POST['codigo_destino']  ?? '');
        $fechaSalida = trim($_POST['fecha']    ?? '');
        $horaSalida = trim($_POST['hora']     ?? '');
        $fechaLlegada = trim($_POST['fecha_llegada'] ?? '');
        $horaLlegada = trim($_POST['hora_llegada'] ?? '');
        $asientosTot = (int)($_POST['asientos_totales'] ?? 0);
        $precio = (float)($_POST['precio'] ?? 0);

        if (!$origen || !$codigoOrigen || !$destino || !$codigoDestino || !$fechaSalida || !$horaSalida || !$fechaLlegada || !$horaLlegada || $asientosTot <= 0 || $precio <= 0) {
            flash_set('error', 'Faltan campos obligatorios o valores inválidos.');
            redirect("index.php?pagina=vuelo&id=$idVuelo");
        }

        if ($codigoOrigen && $codigoDestino && $codigoOrigen === $codigoDestino) {
            flash_set('error', 'El origen y el destino no pueden ser el mismo aeropuerto.');
            redirect("index.php?pagina=vuelo&id=$idVuelo");
        }

        if (strcasecmp($origen, $destino) === 0) {
            flash_set('error', 'El origen y el destino no pueden ser iguales.');
            redirect("index.php?pagina=vuelo&id=$idVuelo");
        }

        $fechaHoraSalida = $fechaSalida . ' ' . $horaSalida . ':00';
        if (strtotime($fechaHoraSalida) < time()) {
            flash_set('error', 'La fecha y hora de salida no puede ser en el pasado.');
            redirect("index.php?pagina=vuelo&id=$idVuelo");
        }

        $fechaHoraLlegada = $fechaLlegada . ' ' . $horaLlegada . ':00';
        if (strtotime($fechaHoraLlegada) <= strtotime($fechaHoraSalida)) {
            flash_set('error', 'La llegada debe ser posterior a la salida.');
            redirect("index.php?pagina=vuelo&id=$idVuelo");
        }

        // aca se obtiene la cantidad de asientos disponibles disponibles = totales - reservados... re ver
        $stmtRes = mysqli_prepare(
            $this->conexion,
            "SELECT COALESCE(SUM(cantPasajeros), 0) as reservados
            FROM reserva 
            WHERE idVuelo = ? AND estadoReserva != 'cancelada'"
        );

        mysqli_stmt_bind_param($stmtRes, 'i', $idVuelo);
        
        mysqli_stmt_execute($stmtRes);

        $reservados = (int) mysqli_fetch_assoc(mysqli_stmt_get_result($stmtRes))['reservados'];
        mysqli_stmt_close($stmtRes);

        $asientosDisp = max(0, $asientosTot - $reservados);

        $query = "UPDATE vuelo SET origenVuelo=?, codigoOrigen=?, destinoVuelo=?, codigoDestino=?, fechaHoraSalidaVuelo=?,
                fechaHoraLlegadaVuelo=?, asientosTotales=?, asientosDisponibles=?, precioVuelo=?
                WHERE idVuelo=? AND idAerolinea=?";

        $stmt = mysqli_prepare($this->conexion, $query);

        if (!$stmt) {
            flash_set('error', 'Error interno del servidor.');
            redirect("index.php?pagina=vuelo&id=$idVuelo");
        }

        mysqli_stmt_bind_param(
            $stmt, 
            'ssssssiidii',    
            $origen,
            $codigoOrigen,
            $destino,
            $codigoDestino,
            $fechaHoraSalida,
            $fechaHoraLlegada,
            $asientosTot,
            $asientosDisp,
            $precio,
            $idVuelo,
            $idAerolinea
        );
        
        try {
            mysqli_stmt_execute($stmt);
        } catch (mysqli_sql_exception $e) {
            mysqli_stmt_close($stmt);
            flash_set('error', 'Error al actualizar el vuelo.');
            redirect("index.php?pagina=vuelo&id=$idVuelo");
        }

        mysqli_stmt_close($stmt);
        flash_set('success', 'Vuelo actualizado correctamente.');
        redirect("index.php?pagina=vuelos");
    }

    public function eliminarVuelo(int $idVuelo, int $idAerolinea): void {
        $query = "DELETE FROM vuelo WHERE idVuelo=? AND idAerolinea=?";
        $stmt = mysqli_prepare($this->conexion, $query);
        if (!$stmt) {
            flash_set('error', 'Error interno del servidor.');
            redirect("index.php?pagina=vuelos");
        }

        mysqli_stmt_bind_param($stmt, 'ii', $idVuelo, $idAerolinea);

        try {
            mysqli_stmt_execute($stmt);
        } catch (mysqli_sql_exception $e) {
            mysqli_stmt_close($stmt);
            flash_set('error', 'Error al eliminar el vuelo.');
            redirect("index.php?pagina=vuelos");
        }

        mysqli_stmt_close($stmt);
        flash_set('success', 'Vuelo eliminado correctamente.');
        redirect("index.php?pagina=vuelos");
    }

    public function obtenerVueloPorId(int $idVuelo): ?array {
        $query = "SELECT * FROM vuelo WHERE idVuelo = ?";
        $stmt = mysqli_prepare($this->conexion, $query);
        if (!$stmt) return null;

        mysqli_stmt_bind_param($stmt, 'i', $idVuelo);
        mysqli_stmt_execute($stmt);
        $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        return $fila ?: null;
    }

    public function listarVuelosPaginado(
        int $idAerolinea,
        int $pagina,
        int $porPagina,
        string $busqueda = '',
        string $desde = '',
        string $hasta = '',
        string $estado = '',
        string $orden = 'salida'
    ): array {
        $offset = ($pagina - 1) * $porPagina;
        $where  = "WHERE idAerolinea = ?";
        $params = [$idAerolinea];
        $tipos  = 'i';

        if ($busqueda !== '') {
            $where   .= " AND (CAST(idVuelo AS CHAR) LIKE ? OR origenVuelo LIKE ? OR codigoOrigen LIKE ? OR destinoVuelo LIKE ? OR codigoDestino LIKE ?)";
            $like     = "%$busqueda%";
            array_push($params, $like, $like, $like, $like, $like);
            $tipos   .= 'sssss';
        }

        if ($desde !== '') {
            $where .= " AND DATE(fechaHoraSalidaVuelo) >= ?";
            $params[] = $desde;
            $tipos .= 's';
        }

        if ($hasta !== '') {
            $where .= " AND DATE(fechaHoraSalidaVuelo) <= ?";
            $params[] = $hasta;
            $tipos .= 's';
        }

        if ($estado === 'disponibles') {
            $where .= " AND asientosDisponibles > 0";
        } elseif ($estado === 'agotados') {
            $where .= " AND asientosDisponibles = 0";
        } elseif ($estado === 'proximos') {
            $where .= " AND fechaHoraSalidaVuelo >= NOW()";
        } elseif ($estado === 'finalizados') {
            $where .= " AND fechaHoraSalidaVuelo < NOW()";
        }

        // Total
        $stmtTotal = mysqli_prepare($this->conexion, "SELECT COUNT(*) as total FROM vuelo $where");
        mysqli_stmt_bind_param($stmtTotal, $tipos, ...$params);
        mysqli_stmt_execute($stmtTotal);
        $total = (int) mysqli_fetch_assoc(mysqli_stmt_get_result($stmtTotal))['total'];
        mysqli_stmt_close($stmtTotal);

        // Datos paginados
        $params[] = $porPagina;
        $params[] = $offset;
        $tipos   .= 'ii';

        $stmt = mysqli_prepare($this->conexion,
            "SELECT * FROM vuelo $where ORDER BY " .
            ($orden === 'precio_asc' ? 'precioVuelo ASC, fechaHoraSalidaVuelo ASC' :
                ($orden === 'precio_desc' ? 'precioVuelo DESC, fechaHoraSalidaVuelo ASC' :
                    ($orden === 'disponibles' ? 'asientosDisponibles DESC, fechaHoraSalidaVuelo ASC' :
                        'fechaHoraSalidaVuelo ASC'))) .
            " LIMIT ? OFFSET ?");
        mysqli_stmt_bind_param($stmt, $tipos, ...$params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $filas = [];
        while ($row = mysqli_fetch_assoc($result)) $filas[] = $row;
        mysqli_stmt_close($stmt);

        return [
            'data'         => $filas,
            'total'        => $total,
            'pagina'       => $pagina,
            'porPagina'    => $porPagina,
            'totalPaginas' => (int) ceil($total / $porPagina),
        ];
    }

    public function buscarVuelos(string $origen, string $destino, string $fecha,int $pasajeros, int $pagina = 1, int $porPagina = 10, string $orden = 'salida', array $filtros = []
    ): array {
        $offset = ($pagina - 1) * $porPagina;

        [$condiciones, $params, $tipos] = $this->condicionesBusqueda($origen, $destino, $fecha, $pasajeros, $filtros);

        $where = 'WHERE ' . implode(' AND ', $condiciones);

        $orderBy = match($orden) {
            'precio_asc'  => 'v.precioVuelo ASC',
            'precio_desc' => 'v.precioVuelo DESC',
            default       => 'v.fechaHoraSalidaVuelo ASC',  
        };

        $stmtTotal = mysqli_prepare($this->conexion, "SELECT COUNT(*) as total FROM vuelo v $where");
        mysqli_stmt_bind_param($stmtTotal, $tipos, ...$params);
        mysqli_stmt_execute($stmtTotal);
        $total = (int) mysqli_fetch_assoc(mysqli_stmt_get_result($stmtTotal))['total'];
        mysqli_stmt_close($stmtTotal);

        $paramsData = [...$params, $porPagina, $offset];
        $stmt = mysqli_prepare($this->conexion,
            "SELECT v.*, a.nombreAerolinea, a.codigoIATA, a.logoUrl
            FROM vuelo v JOIN aerolinea a ON v.idAerolinea = a.idAerolinea
            $where ORDER BY $orderBy LIMIT ? OFFSET ?");
        mysqli_stmt_bind_param($stmt, $tipos . 'ii', ...$paramsData);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $filas = [];
        while ($row = mysqli_fetch_assoc($result)) {
            // Calcular promos aplicables para este vuelo
            $promos = $this->promoCtrl->obtenerPromosAplicables(
                (int)$row['idVuelo'],
                (int)$row['idAerolinea'],
                $row['codigoOrigen'] ?? '',
                $row['codigoDestino'] ?? ''
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
            
            $row['promo_descuento_total'] = $descuentoTotal;
            $row['promo_nombres'] = $promosNombres;
            $row['precio_original'] = (float)$row['precioVuelo'];
            $row['precio_con_descuento'] = $row['precio_original'] * (1 - $descuentoTotal / 100);
            
            $filas[] = $row;
        }
        mysqli_stmt_close($stmt);

        return [
            'data'         => $filas,
            'total'        => $total,
            'pagina'       => $pagina,
            'porPagina'    => $porPagina,
            'totalPaginas' => (int) ceil($total / $porPagina),
            'orden'        => $orden,
        ];
    }

    // ── Facetas para los filtros de la vista de resultados ────────────────────
    // Devuelve las aerolíneas disponibles en la búsqueda base y el rango de
    // precios, para no ofrecer filtros que no devuelvan resultados.
    public function obtenerFacetasVuelos(string $origen, string $destino, string $fecha, int $pasajeros): array {
        [$condiciones, $params, $tipos] = $this->condicionesBusqueda($origen, $destino, $fecha, $pasajeros);

        $where = 'WHERE ' . implode(' AND ', $condiciones);

        $stmt = mysqli_prepare($this->conexion,
            "SELECT a.idAerolinea, a.nombreAerolinea, a.codigoIATA,
                    COUNT(*) AS total,
                    MIN(v.precioVuelo) AS precioMin,
                    MAX(v.precioVuelo) AS precioMax
            FROM vuelo v
            JOIN aerolinea a ON v.idAerolinea = a.idAerolinea
            $where
            GROUP BY a.idAerolinea, a.nombreAerolinea, a.codigoIATA
            ORDER BY a.nombreAerolinea ASC");

        mysqli_stmt_bind_param($stmt, $tipos, ...$params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $aerolineas = [];
        $precioMin  = null;
        $precioMax  = null;
        $total      = 0;

        while ($fila = mysqli_fetch_assoc($result)) {
            $minFila = (float)$fila['precioMin'];
            $maxFila = (float)$fila['precioMax'];

            $precioMin = $precioMin === null ? $minFila : min($precioMin, $minFila);
            $precioMax = $precioMax === null ? $maxFila : max($precioMax, $maxFila);
            $total    += (int)$fila['total'];

            $aerolineas[] = [
                'idAerolinea'     => (int)$fila['idAerolinea'],
                'nombreAerolinea' => $fila['nombreAerolinea'],
                'codigoIATA'      => $fila['codigoIATA'],
                'total'           => (int)$fila['total'],
            ];
        }

        mysqli_stmt_close($stmt);

        return [
            'aerolineas' => $aerolineas,
            'precioMin'  => $precioMin ?? 0.0,
            'precioMax'  => $precioMax ?? 0.0,
            'total'      => $total,
        ];
    }

    // ── Condiciones SQL compartidas por la búsqueda y las facetas ────────────
    private function condicionesBusqueda(string $origen, string $destino, string $fecha, int $pasajeros, array $filtros = []): array {
        $condiciones = ["v.asientosDisponibles >= ?", "v.fechaHoraSalidaVuelo >= NOW()"];
        $params = [$pasajeros];
        $tipos  = 'i';

        if ($origen  !== '') { $condiciones[] = "v.origenVuelo LIKE ?";  $params[] = "%$origen%";  $tipos .= 's'; }
        if ($destino !== '') { $condiciones[] = "v.destinoVuelo LIKE ?"; $params[] = "%$destino%"; $tipos .= 's'; }
        if ($fecha   !== '') { $condiciones[] = "DATE(v.fechaHoraSalidaVuelo) = ?"; $params[] = $fecha; $tipos .= 's'; }

        // Aerolíneas elegidas en los filtros.
        $aerolineas = $filtros['aerolineas'] ?? [];
        if (!empty($aerolineas)) {
            $marcadores = implode(',', array_fill(0, count($aerolineas), '?'));
            $condiciones[] = "v.idAerolinea IN ($marcadores)";

            foreach ($aerolineas as $idAerolinea) {
                $params[] = (int)$idAerolinea;
                $tipos   .= 'i';
            }
        }

        // Rango de precio de lista (las promos se siguen mostrando aparte).
        if (($filtros['precioMin'] ?? null) !== null) {
            $condiciones[] = "v.precioVuelo >= ?";
            $params[]      = (float)$filtros['precioMin'];
            $tipos        .= 'd';
        }

        if (($filtros['precioMax'] ?? null) !== null) {
            $condiciones[] = "v.precioVuelo <= ?";
            $params[]      = (float)$filtros['precioMax'];
            $tipos        .= 'd';
        }

        // Franjas horarias de salida.
        $rangos = [
            'madrugada' => [0, 5],
            'manana'    => [6, 11],
            'tarde'     => [12, 17],
            'noche'     => [18, 23],
        ];

        $franjas = [];
        foreach (($filtros['horarios'] ?? []) as $clave) {
            if (isset($rangos[$clave])) {
                $franjas[] = 'HOUR(v.fechaHoraSalidaVuelo) BETWEEN ' . $rangos[$clave][0] . ' AND ' . $rangos[$clave][1];
            }
        }

        if (!empty($franjas)) {
            $condiciones[] = '(' . implode(' OR ', $franjas) . ')';
        }

        return [$condiciones, $params, $tipos];
    }
}
