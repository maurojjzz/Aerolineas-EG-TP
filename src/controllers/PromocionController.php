<?php

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/Promocion.php';

class PromocionController {

    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // ── CEO: crear promoción ──────────────────────────────────────────────────
    public function crearPromocion(): void {
        $idUsuario   = (int)($_SESSION['usuario']['idUsuario']   ?? 0);
        $idAerolinea = (int)($_SESSION['usuario']['idAerolinea'] ?? 0);

        if (!$idAerolinea) {
            flash_set('error', 'No tenés una aerolínea asociada para crear promociones.');
            redirect('index.php?pagina=promociones');
        }

        $nombre              = trim($_POST['nombre']              ?? '');
        $descuentoPorcentaje = (float)($_POST['descuentoPorcentaje'] ?? 0);
        $fechaInicio         = trim($_POST['fechaInicio']         ?? '');
        $fechaFin            = trim($_POST['fechaFin']            ?? '');
        $descripcion         = trim($_POST['descripcion']         ?? '') ?: null;
        $condiciones         = trim($_POST['condiciones']         ?? '') ?: null;
        $acumulable        = isset($_POST['acumulable']) ? 1 : 0;

        // Alcance: 'aerolinea' | 'ruta' | 'vuelo'
        $alcance = $_POST['alcance'] ?? 'aerolinea';
        if (!in_array($alcance, ['aerolinea', 'ruta', 'vuelo'], true)) {
            $alcance = 'aerolinea';
        }

        $idVuelo      = ($alcance === 'vuelo')      ? (int)($_POST['idVuelo'] ?? 0)      : null;
        $codigoOrigen = ($alcance === 'ruta')       ? trim($_POST['codigoOrigen'] ?? '') : null;
        $codigoDestino= ($alcance === 'ruta')       ? trim($_POST['codigoDestino'] ?? '') : null;

        // Validaciones por alcance
        if ($alcance === 'vuelo' && $idVuelo <= 0) {
            flash_set('error', 'Debés seleccionar un vuelo.');
            redirect('index.php?pagina=promociones');
        }
        if ($alcance === 'ruta' && (!$codigoOrigen || !$codigoDestino)) {
            flash_set('error', 'Debés seleccionar origen y destino para la ruta.');
            redirect('index.php?pagina=promociones');
        }
        if ($alcance === 'ruta' && $codigoOrigen === $codigoDestino) {
            flash_set('error', 'El origen y el destino no pueden ser iguales.');
            redirect('index.php?pagina=promociones');
        }

        if (!$nombre || $descuentoPorcentaje <= 0 || !$fechaInicio || !$fechaFin) {
            flash_set('error', 'Por favor completá todos los campos requeridos.');
            redirect('index.php?pagina=promociones');
        }

        if ($descuentoPorcentaje > 100) {
            flash_set('error', 'El descuento no puede superar el 100%.');
            redirect('index.php?pagina=promociones');
        }

        $tsInicio = strtotime($fechaInicio);
        $tsFin    = strtotime($fechaFin);
        if ($tsInicio === false || $tsFin === false || $tsFin < $tsInicio) {
            flash_set('error', 'La fecha de fin no puede ser anterior a la fecha de inicio.');
            redirect('index.php?pagina=promociones');
        }

        // Validar que fechaInicio no sea pasada (>= hoy)
        $hoy = strtotime(date('Y-m-d'));
        if ($tsInicio < $hoy) {
            flash_set('error', 'La fecha de inicio no puede ser anterior a hoy.');
            redirect('index.php?pagina=promociones');
        }

        // Para alcance "vuelo": validar que fechaFin no sea posterior a la fecha del vuelo
        if ($alcance === 'vuelo' && $idVuelo > 0) {
            $stmtVuelo = mysqli_prepare($this->conexion,
                "SELECT fechaHoraSalidaVuelo FROM vuelo WHERE idVuelo = ? AND idAerolinea = ?");
            mysqli_stmt_bind_param($stmtVuelo, 'ii', $idVuelo, $idAerolinea);
            mysqli_stmt_execute($stmtVuelo);
            $vueloData = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtVuelo));
            mysqli_stmt_close($stmtVuelo);
            
            if ($vueloData) {
                $fechaVuelo = strtotime(date('Y-m-d', strtotime($vueloData['fechaHoraSalidaVuelo'])));
                if ($tsFin > $fechaVuelo) {
                    flash_set('error', 'La fecha de fin de la promoción no puede ser posterior a la fecha del vuelo.');
                    redirect('index.php?pagina=promociones');
                }
            }
        }

        // Validar que no haya otra promo APROBADA con el MISMO ALCANCE y fechas que se superpongan
        $where = "WHERE idAerolinea = ? AND estado = 'Aprobada' AND fechaFin >= ? AND fechaInicio <= ?";
        $params = [$idAerolinea, $fechaInicio, $fechaFin];
        $tipos  = 'iss';

        if ($alcance === 'vuelo') {
            $where .= " AND idVuelo = ?";
            $params[] = $idVuelo;
            $tipos .= 'i';
        } elseif ($alcance === 'ruta') {
            $where .= " AND codigoOrigen = ? AND codigoDestino = ?";
            $params[] = $codigoOrigen;
            $params[] = $codigoDestino;
            $tipos .= 'ss';
        } else { // aerolinea: sin vuelo y sin ruta
            $where .= " AND idVuelo IS NULL AND codigoOrigen IS NULL AND codigoDestino IS NULL";
        }

        $stmtCheck = mysqli_prepare($this->conexion, "SELECT idPromocion FROM promocion $where LIMIT 1");
        mysqli_stmt_bind_param($stmtCheck, $tipos, ...$params);
        mysqli_stmt_execute($stmtCheck);
        $existente = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCheck));
        mysqli_stmt_close($stmtCheck);

        if ($existente) {
            $msg = match($alcance) {
                'vuelo'      => "Ya existe una promoción aprobada para ese vuelo en fechas que se superponen.",
                'ruta'       => "Ya existe una promoción aprobada para esa ruta ($codigoOrigen-$codigoDestino) en fechas que se superponen.",
                default      => "Ya existe una promoción aprobada general para esta aerolínea en esas fechas.",
            };
            flash_set('error', $msg);
            redirect('index.php?pagina=promociones');
        }

        // Si es vuelo, verificar que pertenezca a la aerolínea del CEO
        if ($alcance === 'vuelo') {
            $stmtV = mysqli_prepare($this->conexion, "SELECT idVuelo FROM vuelo WHERE idVuelo = ? AND idAerolinea = ?");
            mysqli_stmt_bind_param($stmtV, 'ii', $idVuelo, $idAerolinea);
            mysqli_stmt_execute($stmtV);
            if (!mysqli_fetch_assoc(mysqli_stmt_get_result($stmtV))) {
                flash_set('error', 'El vuelo seleccionado no pertenece a tu aerolínea.');
                redirect('index.php?pagina=promociones');
            }
            mysqli_stmt_close($stmtV);
        }

        // Si es ruta, verificar que la aerolínea tenga vuelos en esa ruta
        if ($alcance === 'ruta') {
            $stmtR = mysqli_prepare($this->conexion,
                "SELECT 1 FROM vuelo WHERE idAerolinea = ? AND codigoOrigen = ? AND codigoDestino = ? LIMIT 1");
            mysqli_stmt_bind_param($stmtR, 'iss', $idAerolinea, $codigoOrigen, $codigoDestino);
            mysqli_stmt_execute($stmtR);
            if (!mysqli_fetch_assoc(mysqli_stmt_get_result($stmtR))) {
                flash_set('error', 'Tu aerolínea no opera vuelos en esa ruta.');
                redirect('index.php?pagina=promociones');
            }
            mysqli_stmt_close($stmtR);
        }

        $codigo = 'PROM-' . strtoupper(substr(md5(uniqid()), 0, 4));

        $query = "INSERT INTO promocion
                    (codigo, idAerolinea, idUsuarioCreador, idVuelo, codigoOrigen, codigoDestino,
                     nombre, descripcion, descuentoPorcentaje, fechaInicio, fechaFin, estado, condiciones, acumulable)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pendiente', ?, ?)";

        $stmt = mysqli_prepare($this->conexion, $query);
        if (!$stmt) {
            flash_set('error', 'Error interno del servidor.');
            redirect('index.php?pagina=promociones');
        }

        mysqli_stmt_bind_param(
            $stmt,
            'siiiisssdsssi',
            $codigo,
            $idAerolinea,
            $idUsuario,
            $idVuelo,
            $codigoOrigen,
            $codigoDestino,
            $nombre,
            $descripcion,
            $descuentoPorcentaje,
            $fechaInicio,
            $fechaFin,
            $condiciones,
            $acumulable
        );

        try {
            mysqli_stmt_execute($stmt);
        } catch (mysqli_sql_exception $e) {
            mysqli_stmt_close($stmt);
            flash_set('error', 'Error al guardar la promoción.');
            redirect('index.php?pagina=promociones');
        }

        mysqli_stmt_close($stmt);
        flash_set('success', 'Promoción enviada. Quedó pendiente de aprobación por el administrador.');
        redirect('index.php?pagina=promociones');
    }

    // ── CEO: listar promociones de su aerolínea ───────────────────────────────
    public function listarPorCEO(): array {
        $idAerolinea = (int)($_SESSION['usuario']['idAerolinea'] ?? 0);
        if (!$idAerolinea) return [];

        $stmt = mysqli_prepare($this->conexion,
            "SELECT p.*, CONCAT(u.nombre, ' ', u.apellido) AS creadorNombre
            FROM promocion p
            JOIN usuario u ON p.idUsuarioCreador = u.idUsuario
            WHERE p.idAerolinea = ?
            ORDER BY p.fechaCreacion DESC");
        mysqli_stmt_bind_param($stmt, 'i', $idAerolinea);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $filas = [];
        while ($row = mysqli_fetch_assoc($result)) $filas[] = $row;
        mysqli_stmt_close($stmt);
        return $filas;
    }

    // ── Obtener vuelos de la aerolínea del CEO (para selector en modal) ───────
    public function obtenerVuelosCEO(): array {
        $idAerolinea = (int)($_SESSION['usuario']['idAerolinea'] ?? 0);
        if (!$idAerolinea) return [];

        $stmt = mysqli_prepare($this->conexion,
            "SELECT idVuelo, origenVuelo, codigoOrigen, destinoVuelo, codigoDestino, fechaHoraSalidaVuelo
             FROM vuelo
             WHERE idAerolinea = ? AND fechaHoraSalidaVuelo > NOW()
             ORDER BY fechaHoraSalidaVuelo ASC");
        mysqli_stmt_bind_param($stmt, 'i', $idAerolinea);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $filas = [];
        while ($row = mysqli_fetch_assoc($result)) $filas[] = $row;
        mysqli_stmt_close($stmt);
        return $filas;
    }

    // ── Obtener rutas únicas de la aerolínea del CEO (para selector en modal) ─
    public function obtenerRutasCEO(): array {
        $idAerolinea = (int)($_SESSION['usuario']['idAerolinea'] ?? 0);
        if (!$idAerolinea) return [];

        $stmt = mysqli_prepare($this->conexion,
            "SELECT DISTINCT codigoOrigen, codigoDestino, origenVuelo, destinoVuelo
             FROM vuelo
             WHERE idAerolinea = ? AND codigoOrigen IS NOT NULL AND codigoDestino IS NOT NULL
             ORDER BY origenVuelo, destinoVuelo");
        mysqli_stmt_bind_param($stmt, 'i', $idAerolinea);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $filas = [];
        while ($row = mysqli_fetch_assoc($result)) $filas[] = $row;
        mysqli_stmt_close($stmt);
        return $filas;
    }

    // ── Obtener orígenes únicos con vuelos futuros (para select Origen en modal ruta) ──
    public function obtenerOrigenesCEO(): array {
        $idAerolinea = (int)($_SESSION['usuario']['idAerolinea'] ?? 0);
        if (!$idAerolinea) return [];

        $stmt = mysqli_prepare($this->conexion,
            "SELECT DISTINCT codigoOrigen, origenVuelo
             FROM vuelo
             WHERE idAerolinea = ? 
               AND codigoOrigen IS NOT NULL 
               AND fechaHoraSalidaVuelo > NOW()
             ORDER BY origenVuelo");
        mysqli_stmt_bind_param($stmt, 'i', $idAerolinea);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $filas = [];
        while ($row = mysqli_fetch_assoc($result)) $filas[] = $row;
        mysqli_stmt_close($stmt);
        return $filas;
    }

    // ── Obtener destinos por origen (vuelos futuros de la aerolínea) ──
    public function obtenerDestinosPorOrigen(string $origen): array {
        $idAerolinea = (int)($_SESSION['usuario']['idAerolinea'] ?? 0);
        if (!$idAerolinea || $origen === '') return [];

        $stmt = mysqli_prepare($this->conexion,
            "SELECT DISTINCT codigoDestino, destinoVuelo
             FROM vuelo
             WHERE idAerolinea = ? 
               AND codigoOrigen = ? 
               AND codigoDestino IS NOT NULL 
               AND fechaHoraSalidaVuelo > NOW()
             ORDER BY destinoVuelo");
        mysqli_stmt_bind_param($stmt, 'is', $idAerolinea, $origen);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $filas = [];
        while ($row = mysqli_fetch_assoc($result)) $filas[] = $row;
        mysqli_stmt_close($stmt);
        return $filas;
    }

    // ── Obtener TODAS las promos aplicables a un vuelo (para stacking) ───────────
    // Retorna array ordenado por prioridad: 1=vuelo, 2=ruta, 3=aerolinea
    public function obtenerPromosAplicables(int $idVuelo, int $idAerolinea, string $codigoOrigen, string $codigoDestino): array {
        $stmt = mysqli_prepare($this->conexion,
            "SELECT p.idPromocion, p.nombre, p.descuentoPorcentaje, p.acumulable, p.idVuelo, p.codigoOrigen, p.codigoDestino,
                    CASE
                        WHEN p.idVuelo IS NOT NULL THEN 1
                        WHEN p.codigoOrigen IS NOT NULL THEN 2
                        ELSE 3
                    END AS prioridad
             FROM promocion p
             WHERE p.idAerolinea = ?
               AND p.estado = 'Aprobada'
               AND p.fechaInicio <= CURDATE()
               AND p.fechaFin >= CURDATE()
               AND (
                   (p.idVuelo = ?)
                   OR (p.idVuelo IS NULL AND p.codigoOrigen = ? AND p.codigoDestino = ?)
                   OR (p.idVuelo IS NULL AND p.codigoOrigen IS NULL AND p.codigoDestino IS NULL)
               )
             ORDER BY prioridad ASC");
        mysqli_stmt_bind_param($stmt, 'iiss', $idAerolinea, $idVuelo, $codigoOrigen, $codigoDestino);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $filas = [];
        while ($row = mysqli_fetch_assoc($result)) $filas[] = $row;
        mysqli_stmt_close($stmt);
        return $filas;
    }

    // ── CEO: obtener una promo propia por id ──────────────────────────────────
    public function obtenerPorIdCEO(int $id): ?array {
        $idAerolinea = (int)($_SESSION['usuario']['idAerolinea'] ?? 0);
        if (!$idAerolinea) return null;

        $stmt = mysqli_prepare($this->conexion,
            "SELECT p.*, a.nombreAerolinea, a.codigoIATA,
                    u.nombre AS ceoNombre, u.apellido AS ceoApellido
            FROM promocion p
            JOIN aerolinea a ON p.idAerolinea = a.idAerolinea
            JOIN usuario   u ON p.idUsuarioCreador = u.idUsuario
            WHERE p.idPromocion = ? AND p.idAerolinea = ?");
        mysqli_stmt_bind_param($stmt, 'ii', $id, $idAerolinea);
        mysqli_stmt_execute($stmt);
        $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        return $fila ?: null;
    }

    // ── ADMIN: listar todas las promociones ───────────────────────────────────
    public function listarTodas(): array {
        $result = mysqli_query($this->conexion,
            "SELECT p.*, a.nombreAerolinea, a.codigoIATA,
                    u.nombre AS ceoNombre, u.apellido AS ceoApellido
            FROM promocion p
            JOIN aerolinea a ON p.idAerolinea = a.idAerolinea
            JOIN usuario   u ON p.idUsuarioCreador = u.idUsuario
            ORDER BY p.fechaCreacion DESC");
        if (!$result) return [];
        $filas = [];
        while ($row = mysqli_fetch_assoc($result)) $filas[] = $row;
        return $filas;
    }

    // ── ADMIN: obtener una promoción por id (con aerolínea y CEO) ─────────────
    public function obtenerPorId(int $id): ?array {
        $stmt = mysqli_prepare($this->conexion,
            "SELECT p.*, a.nombreAerolinea, a.codigoIATA,
                    u.nombre AS ceoNombre, u.apellido AS ceoApellido
            FROM promocion p
            JOIN aerolinea a ON p.idAerolinea = a.idAerolinea
            JOIN usuario   u ON p.idUsuarioCreador = u.idUsuario
            WHERE p.idPromocion = ?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        return $fila ?: null;
    }

    // ── ADMIN: aprobar o denegar ──────────────────────────────────────────────
    public function resolverEstado(string $estado): void {
        if (!in_array($estado, ['Aprobada', 'Denegada'], true)) {
            flash_set('error', 'Acción no válida.');
            redirect('index.php?pagina=promociones&seccion=listado');
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            flash_set('error', 'Promoción no válida.');
            redirect('index.php?pagina=promociones&seccion=listado');
        }

        $stmt = mysqli_prepare($this->conexion,
            "UPDATE promocion SET estado = ? WHERE idPromocion = ? AND estado = 'Pendiente'");
        mysqli_stmt_bind_param($stmt, 'si', $estado, $id);

        try {
            mysqli_stmt_execute($stmt);
            $afectadas = mysqli_stmt_affected_rows($stmt);
            mysqli_stmt_close($stmt);

            if ($afectadas > 0) {
                flash_set('success', $estado === 'Aprobada'
                    ? 'Promoción aprobada correctamente.'
                    : 'Promoción denegada correctamente.');
            } else {
                flash_set('error', 'No se pudo actualizar: la promoción no existe o ya fue resuelta.');
            }
        } catch (mysqli_sql_exception $e) {
            mysqli_stmt_close($stmt);
            flash_set('error', 'Error al actualizar la promoción.');
        }

        redirect('index.php?pagina=promociones&seccion=listado');
    }

    // ── CEO: eliminar promo propia (si está Pendiente o Denegada) ───────────────────
    public function eliminarPromocion(): void {
        $id          = (int)($_POST['id']          ?? 0);
        $idAerolinea = (int)($_SESSION['usuario']['idAerolinea'] ?? 0);

        if (!$id || !$idAerolinea) {
            flash_set('error', 'Datos inválidos.');
            redirect('index.php?pagina=promociones');
        }

        $stmt = mysqli_prepare($this->conexion,
            "DELETE FROM promocion
            WHERE idPromocion = ? AND idAerolinea = ? AND estado IN ('Pendiente', 'Denegada')");
        mysqli_stmt_bind_param($stmt, 'ii', $id, $idAerolinea);

        try {
            mysqli_stmt_execute($stmt);
            $afectadas = mysqli_stmt_affected_rows($stmt);
            mysqli_stmt_close($stmt);

            if ($afectadas > 0) {
                flash_set('success', 'Promoción eliminada correctamente.');
            } else {
                flash_set('error', 'No se puede eliminar: la promoción no existe, no te pertenece o no está en estado eliminable.');
            }
        } catch (mysqli_sql_exception $e) {
            mysqli_stmt_close($stmt);
            flash_set('error', 'Error al eliminar la promoción.');
        }

        redirect('index.php?pagina=promociones');
    }

    // ── CEO: editar promo propia (solo si está Pendiente) ────────────────────────
    public function editarPromocion(): void {
        $id          = (int)($_POST['id']          ?? 0);
        $idAerolinea = (int)($_SESSION['usuario']['idAerolinea'] ?? 0);
        $idUsuario   = (int)($_SESSION['usuario']['idUsuario']   ?? 0);

        if (!$id || !$idAerolinea) {
            flash_set('error', 'Datos inválidos.');
            redirect('index.php?pagina=promociones');
        }

        // Verificar que la promo existe, es del CEO y está Pendiente
        $stmtCheck = mysqli_prepare($this->conexion,
            "SELECT * FROM promocion WHERE idPromocion = ? AND idAerolinea = ? AND estado = 'Pendiente'");
        mysqli_stmt_bind_param($stmtCheck, 'ii', $id, $idAerolinea);
        mysqli_stmt_execute($stmtCheck);
        $promoExistente = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCheck));
        mysqli_stmt_close($stmtCheck);

        if (!$promoExistente) {
            flash_set('error', 'No se puede editar: la promoción no existe, no te pertenece o ya fue procesada.');
            redirect('index.php?pagina=promociones');
        }

        // Mismos campos que crearPromocion
        $nombre              = trim($_POST['nombre']              ?? '');
        $descuentoPorcentaje = (float)($_POST['descuentoPorcentaje'] ?? 0);
        $fechaInicio         = trim($_POST['fechaInicio']         ?? '');
        $fechaFin            = trim($_POST['fechaFin']            ?? '');
        $descripcion         = trim($_POST['descripcion']         ?? '') ?: null;
        $condiciones         = trim($_POST['condiciones']         ?? '') ?: null;
        $acumulable          = isset($_POST['acumulable']) ? 1 : 0;

        $alcance = $_POST['alcance'] ?? 'aerolinea';
        if (!in_array($alcance, ['aerolinea', 'ruta', 'vuelo'], true)) {
            $alcance = 'aerolinea';
        }

        $idVuelo      = ($alcance === 'vuelo')      ? (int)($_POST['idVuelo'] ?? 0)      : null;
        $codigoOrigen = ($alcance === 'ruta')       ? trim($_POST['codigoOrigen'] ?? '') : null;
        $codigoDestino= ($alcance === 'ruta')       ? trim($_POST['codigoDestino'] ?? '') : null;

        // Validaciones por alcance (igual que crear)
        if ($alcance === 'vuelo' && $idVuelo <= 0) {
            flash_set('error', 'Debés seleccionar un vuelo.');
            redirect('index.php?pagina=promociones');
        }
        if ($alcance === 'ruta' && (!$codigoOrigen || !$codigoDestino)) {
            flash_set('error', 'Debés seleccionar origen y destino para la ruta.');
            redirect('index.php?pagina=promociones');
        }
        if ($alcance === 'ruta' && $codigoOrigen === $codigoDestino) {
            flash_set('error', 'El origen y el destino no pueden ser iguales.');
            redirect('index.php?pagina=promociones');
        }

        if (!$nombre || $descuentoPorcentaje <= 0 || !$fechaInicio || !$fechaFin) {
            flash_set('error', 'Por favor completá todos los campos requeridos.');
            redirect('index.php?pagina=promociones');
        }

        if ($descuentoPorcentaje > 100) {
            flash_set('error', 'El descuento no puede superar el 100%.');
            redirect('index.php?pagina=promociones');
        }

        $tsInicio = strtotime($fechaInicio);
        $tsFin    = strtotime($fechaFin);
        if ($tsInicio === false || $tsFin === false || $tsFin < $tsInicio) {
            flash_set('error', 'La fecha de fin no puede ser anterior a la fecha de inicio.');
            redirect('index.php?pagina=promociones');
        }

        $hoy = strtotime(date('Y-m-d'));
        if ($tsInicio < $hoy) {
            flash_set('error', 'La fecha de inicio no puede ser anterior a hoy.');
            redirect('index.php?pagina=promociones');
        }

        if ($alcance === 'vuelo' && $idVuelo > 0) {
            $stmtVuelo = mysqli_prepare($this->conexion,
                "SELECT fechaHoraSalidaVuelo FROM vuelo WHERE idVuelo = ? AND idAerolinea = ?");
            mysqli_stmt_bind_param($stmtVuelo, 'ii', $idVuelo, $idAerolinea);
            mysqli_stmt_execute($stmtVuelo);
            $vueloData = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtVuelo));
            mysqli_stmt_close($stmtVuelo);
            
            if ($vueloData) {
                $fechaVuelo = strtotime(date('Y-m-d', strtotime($vueloData['fechaHoraSalidaVuelo'])));
                if ($tsFin > $fechaVuelo) {
                    flash_set('error', 'La fecha de fin de la promoción no puede ser posterior a la fecha del vuelo.');
                    redirect('index.php?pagina=promociones');
                }
            }
        }

        // Validar solapamiento con MISMAS reglas que crear (excluyendo la promo actual)
        $where = "WHERE idAerolinea = ? AND estado = 'Aprobada' AND fechaFin >= ? AND fechaInicio <= ? AND idPromocion != ?";
        $params = [$idAerolinea, $fechaInicio, $fechaFin, $id];
        $tipos  = 'issi';

        if ($alcance === 'vuelo') {
            $where .= " AND idVuelo = ?";
            $params[] = $idVuelo;
            $tipos .= 'i';
        } elseif ($alcance === 'ruta') {
            $where .= " AND codigoOrigen = ? AND codigoDestino = ?";
            $params[] = $codigoOrigen;
            $params[] = $codigoDestino;
            $tipos .= 'ss';
        } else {
            $where .= " AND idVuelo IS NULL AND codigoOrigen IS NULL AND codigoDestino IS NULL";
        }

        $stmtCheck2 = mysqli_prepare($this->conexion, "SELECT idPromocion FROM promocion $where LIMIT 1");
        mysqli_stmt_bind_param($stmtCheck2, $tipos, ...$params);
        mysqli_stmt_execute($stmtCheck2);
        $existente = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtCheck2));
        mysqli_stmt_close($stmtCheck2);

        if ($existente) {
            $msg = match($alcance) {
                'vuelo'      => "Ya existe una promoción aprobada para ese vuelo en fechas que se superponen.",
                'ruta'       => "Ya existe una promoción aprobada para esa ruta ($codigoOrigen-$codigoDestino) en fechas que se superponen.",
                default      => "Ya existe una promoción aprobada general para esta aerolínea en esas fechas.",
            };
            flash_set('error', $msg);
            redirect('index.php?pagina=promociones');
        }

        // UPDATE
        $query = "UPDATE promocion SET
                    nombre = ?, descripcion = ?, descuentoPorcentaje = ?,
                    fechaInicio = ?, fechaFin = ?, condiciones = ?, acumulable = ?,
                    idVuelo = ?, codigoOrigen = ?, codigoDestino = ?
                  WHERE idPromocion = ? AND idAerolinea = ?";

        $stmt = mysqli_prepare($this->conexion, $query);
        if (!$stmt) {
            flash_set('error', 'Error interno del servidor.');
            redirect('index.php?pagina=promociones');
        }

        mysqli_stmt_bind_param(
            $stmt,
            'ssdsssiiiiii',
            $nombre,
            $descripcion,
            $descuentoPorcentaje,
            $fechaInicio,
            $fechaFin,
            $condiciones,
            $acumulable,
            $idVuelo,
            $codigoOrigen,
            $codigoDestino,
            $id,
            $idAerolinea
        );

        try {
            mysqli_stmt_execute($stmt);
        } catch (mysqli_sql_exception $e) {
            mysqli_stmt_close($stmt);
            flash_set('error', 'Error al actualizar la promoción.');
            redirect('index.php?pagina=promociones');
        }

        mysqli_stmt_close($stmt);
        flash_set('success', 'Promoción actualizada correctamente. Queda pendiente de aprobación por el administrador.');
        redirect('index.php?pagina=promociones');
    }

    // ── CEO: obtener promo para editar (JSON) ──────────────────────────────────
    public function obtenerParaEditar(int $id): ?array {
        $idAerolinea = (int)($_SESSION['usuario']['idAerolinea'] ?? 0);
        if (!$idAerolinea) return null;

        $stmt = mysqli_prepare($this->conexion,
            "SELECT * FROM promocion WHERE idPromocion = ? AND idAerolinea = ?");
        mysqli_stmt_bind_param($stmt, 'ii', $id, $idAerolinea);
        mysqli_stmt_execute($stmt);
        $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        return $fila ?: null;
    }

    // ── Obtener las últimas promociones habilitadas (Aprobadas y vigentes) ──
    public function obtenerUltimasPublicadas(int $limite = 3): array {
        $stmt = mysqli_prepare($this->conexion,
            "SELECT p.*, a.nombreAerolinea, a.codigoIATA, a.logoUrl
             FROM promocion p
             JOIN aerolinea a ON p.idAerolinea = a.idAerolinea
             WHERE p.estado = 'Aprobada'
               AND p.fechaInicio <= CURDATE()
               AND p.fechaFin >= CURDATE()
             ORDER BY p.fechaCreacion DESC
             LIMIT ?");
        
        mysqli_stmt_bind_param($stmt, 'i', $limite);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $filas = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $filas[] = $row;
        }
        
        mysqli_stmt_close($stmt);
        return $filas;
    }

    // ── Listar TODAS las promociones habilitadas (Aprobadas y vigentes) ──
    public function listarTodasPublicadas(): array {
        $result = mysqli_query($this->conexion,
            "SELECT p.*, a.nombreAerolinea, a.codigoIATA
             FROM promocion p
             JOIN aerolinea a ON p.idAerolinea = a.idAerolinea
             WHERE p.estado = 'Aprobada'
               AND p.fechaInicio <= CURDATE()
               AND p.fechaFin >= CURDATE()
             ORDER BY p.fechaCreacion DESC");
             
        if (!$result) return [];
        
        $filas = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $filas[] = $row;
        }
        return $filas;
    }
}