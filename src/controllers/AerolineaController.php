<?php 

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/Aerolinea.php';
require_once __DIR__ . '/CloudinaryController.php';

class AerolineaController {

    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    /**
     * Valida y sube el logo a Cloudinary.
     * Devuelve [null, null] cuando no se envió archivo.
     *
     * @return array{0: ?string, 1: ?string}
     */
    private function subirLogo(?array $archivo): array {
        if (!$archivo || ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return [null, null];
        }

        if (($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('No se pudo recibir el archivo del logo. Intentá de nuevo.');
        }

        $tamano = (int)($archivo['size'] ?? 0);
        if ($tamano <= 0) {
            throw new RuntimeException('El archivo del logo está vacío.');
        }

        if ($tamano > 2 * 1024 * 1024) {
            throw new RuntimeException('El logo no puede superar los 2 MB.');
        }

        $mimesPermitidos = ['image/png', 'image/jpeg', 'image/webp'];
        $info = @getimagesize((string)($archivo['tmp_name'] ?? ''));
        $mime = $info['mime'] ?? '';

        if (!in_array($mime, $mimesPermitidos, true)) {
            throw new RuntimeException('El logo debe ser una imagen PNG, JPG o WebP.');
        }

        $cloudinary = new CloudinaryController();
        $resultado  = $cloudinary->subirFoto($archivo['tmp_name']);

        if (empty($resultado['secure_url']) || empty($resultado['public_id'])) {
            throw new RuntimeException('Cloudinary no devolvió los datos del logo.');
        }

        return [$resultado['secure_url'], $resultado['public_id']];
    }

    /**
     * Condición SQL para considerar a un CEO como aprobado.
     * Incluye CEOs activos legacy sin fecha y excluye pendientes/denegados.
     */
    private function condicionCEOAprobado(string $alias = 'u'): string {
        $fechaDenegacion = defined('FECHA_DENEGACION_CEO')
            ? FECHA_DENEGACION_CEO
            : '1970-01-01 00:00:00';

        return "(
            ({$alias}.fecha_hora_autorizacion IS NOT NULL AND {$alias}.fecha_hora_autorizacion <> '{$fechaDenegacion}')
            OR ({$alias}.fecha_hora_autorizacion IS NULL AND {$alias}.activo = 1)
        )";
    }

    public function crearAerolinea(): void {
        $nombre = trim($_POST['nombre'] ?? null);
        $codigoIATA = trim($_POST['codigo'] ?? null);
        $codPais = trim($_POST['pais'] ?? null);
        $email = trim($_POST['email'] ?? null);
        $descripcion = trim($_POST['descripcion'] ?? null);
        $activo = ($_POST['estadoAerolinea'] ?? '') === 'activa' ? 1 : 0;
        $logoUrl = null; 
        $logoPublicId = null;

        if (!$nombre || !$codigoIATA || !$codPais || !$email) {
            flash_set('error', 'Faltan campos obligatorios');
            redirect("index.php?pagina=aerolinea&seccion=alta");
        }

        try {
            [$logoUrl, $logoPublicId] = $this->subirLogo($_FILES['logo'] ?? null);
        } catch (Throwable $e) {
            error_log('Error al subir el logo de la aerolínea: ' . $e->getMessage());
            flash_set('error', 'No se pudo subir el logo: ' . $e->getMessage());
            redirect("index.php?pagina=aerolinea&seccion=alta");
        }

        $aerolinea  = new Aerolinea($nombre, $codigoIATA, $descripcion, $codPais, $email, $logoUrl, $activo, null, $logoPublicId);

        $query = "INSERT INTO aerolinea (nombreAerolinea, codigoIATA, descripcion, codPais, email, logoUrl, activo, logoPublicId) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($this->conexion, $query);

        if(!$stmt){
            flash_set('error', 'Error interno del servidor');
            redirect("index.php?pagina=aerolinea&seccion=alta");
        }

        $nombreBD = $aerolinea->getNombreAerolinea();
        $codigoBD= $aerolinea->getCodigoIATA();
        $descripcionBD = $aerolinea->getDescripcion();
        $paisBD = $aerolinea->getCodPais();
        $emailBD = $aerolinea->getEmail();
        $logoUrlBD = $aerolinea->getLogoUrl();
        $activoBD = $aerolinea->getActivo() ? 1 : 0;
        $logoPubIdBD = $aerolinea->getLogoPublicId();

        mysqli_stmt_bind_param(
            $stmt,
            "ssssssis",
            $nombreBD,
            $codigoBD,
            $descripcionBD,
            $paisBD,
            $emailBD,
            $logoUrlBD,
            $activoBD,
            $logoPubIdBD
        );

        try {
            mysqli_stmt_execute($stmt);
        } catch (mysqli_sql_exception $e) {
            $errno = $e->getCode();
            $error = $e->getMessage();
            mysqli_stmt_close($stmt);

            if ($errno === 1062) {
                flash_set('error', 'Ya existe una aerolínea con ese código IATA.');
            } else {
                flash_set('error', 'Error al crear la aerolínea.');
            }
            redirect('index.php?pagina=aerolinea&seccion=alta');
        }

        mysqli_stmt_close($stmt);

        flash_set('success', 'Aerolínea creada correctamente.');
        redirect('index.php?pagina=aerolinea&seccion=listado');

    }


    public function editarAerolinea(int $id): void {
        $nombre = trim($_POST['nombre']           ?? '');
        $codigoIATA  = trim($_POST['codigo']           ?? '');
        $codPais = trim($_POST['pais']             ?? '');
        $email = trim($_POST['email']            ?? '');
        $descripcion = trim($_POST['descripcion']      ?? '');
        $activo = ($_POST['estadoAerolinea'] ?? '') === 'activa' ? 1 : 0;

        if (!$nombre || !$codigoIATA || !$codPais || !$email) {
            flash_set('error', 'Faltan campos obligatorios.');
            redirect("index.php?pagina=aerolinea&seccion=editar&id=$id");
        }

        // logo: solo actualizar si subieron uno nuevo
        $logoUrl      = null;
        $logoPublicId = null;

        try {
            [$logoUrl, $logoPublicId] = $this->subirLogo($_FILES['logo'] ?? null);
        } catch (Throwable $e) {
            error_log('Error al subir el logo de la aerolínea #' . $id . ': ' . $e->getMessage());
            flash_set('error', 'No se pudo subir el logo: ' . $e->getMessage());
            redirect("index.php?pagina=aerolinea&seccion=editar&id=$id");
        }

        if ($logoUrl) {
            // subieron logo nuevo: actualizar todo
            $query = "UPDATE aerolinea 
                    SET nombreAerolinea=?, codigoIATA=?, codPais=?, email=?, descripcion=?, activo=?, logoUrl=?, logoPublicId=?
                    WHERE idAerolinea=?";
            $stmt = mysqli_prepare($this->conexion, $query);
            if (!$stmt) {
                flash_set('error', 'Error interno del servidor.');
                redirect("index.php?pagina=aerolinea&seccion=editar&id=$id");
            }
            mysqli_stmt_bind_param($stmt, 'sssssissi',
                $nombre, $codigoIATA, $codPais, $email, $descripcion, $activo, $logoUrl, $logoPublicId, $id);
        } else {
            // sin logo nuevo: no tocar logoUrl ni logoPublicId
            $query = "UPDATE aerolinea 
                    SET nombreAerolinea=?, codigoIATA=?, codPais=?, email=?, descripcion=?, activo=?
                    WHERE idAerolinea=?";
            $stmt = mysqli_prepare($this->conexion, $query);
            if (!$stmt) {
                flash_set('error', 'Error interno del servidor.');
                redirect("index.php?pagina=aerolinea&seccion=editar&id=$id");
            }
            mysqli_stmt_bind_param($stmt, 'sssssii',
                $nombre, $codigoIATA, $codPais, $email, $descripcion, $activo, $id);
        }

        try {
            mysqli_stmt_execute($stmt);
        } catch (mysqli_sql_exception $e) {
            mysqli_stmt_close($stmt);
            flash_set('error', 'Error al actualizar la aerolínea.');
            redirect("index.php?pagina=aerolinea&seccion=editar&id=$id");
        }

        mysqli_stmt_close($stmt);

        flash_set('success', 'Aerolínea actualizada correctamente.');
        redirect("index.php?pagina=aerolinea&seccion=listado");
    }



    public function listarAerolineas(): array {
        $query = "SELECT * FROM aerolinea";
        $result = mysqli_query($this->conexion, $query);

        if (!$result) {
            return [];
        }

        $aerolineas = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $aerolinea = new Aerolinea(
                $row['nombreAerolinea'],
                $row['codigoIATA'],
                $row['descripcion'],
                $row['codPais'],
                $row['email'],
                $row['logoUrl'],
                (bool)$row['activo'],
                (int)$row['idAerolinea'],
                $row['logoPublicId']
            );
            $aerolineas[] = $aerolinea;
        }

        mysqli_free_result($result);

        return $aerolineas;
    }

    public function listarAerolineasConCEO(): array {
        $ceoAprobado = $this->condicionCEOAprobado();
        $query = "SELECT a.*, u.nombre as ceoNombre, u.apellido as ceoApellido, u.email as ceoEmail
                FROM aerolinea a
                LEFT JOIN usuario u 
                    ON u.idAerolinea = a.idAerolinea AND u.rol = 'ceo' AND {$ceoAprobado}
                ORDER BY a.nombreAerolinea;";

        $result = mysqli_query($this->conexion, $query);
        if (!$result) return [];

        $filas = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $filas[] = $row;
        }
        mysqli_free_result($result);
        return $filas;
    }

    public function listarAerolineasPaginado(
        int $pagina,
        int $porPagina,
        string $estado = '',
        string $pais = '',
        string $conCeo = '',
        string $busqueda = ''
    ): array {

        $offset = ($pagina - 1) * $porPagina;
        $condiciones = [];
        $params = [];
        $tipos = '';

        if ($estado === 'activa') {
            $condiciones[] = "a.activo = 1";
        } elseif ($estado === 'inactiva') {
            $condiciones[] = "a.activo = 0";
        }

        if ($pais !== '') {
            $condiciones[] = "a.codPais = ?";
            $params[] = $pais;
            $tipos .= 's';
        }

        if ($conCeo === 'sin') {
            $condiciones[] = "u.idUsuario IS NULL";
        } elseif ($conCeo === 'con') {
            $condiciones[] = "u.idUsuario IS NOT NULL";
        }

        if ($busqueda !== '') {
            $condiciones[] = "(a.nombreAerolinea LIKE ? OR a.codigoIATA LIKE ?)";
            $like = "%$busqueda%";
            $params[] = $like;
            $params[] = $like;
            $tipos .= 'ss';
        }

        $where = count($condiciones) ? 'WHERE ' . implode(' AND ', $condiciones) : '';
        $ceoAprobado = $this->condicionCEOAprobado();

        // Total sin LIMIT para calcular páginas
        $queryTotal = "SELECT COUNT(*) as total
                    FROM aerolinea a
                    LEFT JOIN usuario u ON u.idAerolinea = a.idAerolinea AND u.rol = 'ceo' AND {$ceoAprobado}
                    $where";

        $stmtTotal = mysqli_prepare($this->conexion, $queryTotal);

        if ($params) {
            mysqli_stmt_bind_param($stmtTotal, $tipos, ...$params);
        }

        mysqli_stmt_execute($stmtTotal);

        $total = (int) mysqli_fetch_assoc(mysqli_stmt_get_result($stmtTotal))['total'];
        mysqli_stmt_close($stmtTotal);

        // Query paginada
        $query = "SELECT a.*, u.nombre as ceoNombre, u.apellido as ceoApellido
                FROM aerolinea a
                LEFT JOIN usuario u ON u.idAerolinea = a.idAerolinea AND u.rol = 'ceo' AND {$ceoAprobado}
                $where
                ORDER BY a.nombreAerolinea
                LIMIT ? OFFSET ?";

        $params[] = $porPagina;
        $params[] = $offset;
        $tipos .= 'ii';

        $stmt = mysqli_prepare($this->conexion, $query);
        mysqli_stmt_bind_param($stmt, $tipos, ...$params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $filas = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $filas[] = $row;
        }
        mysqli_stmt_close($stmt);

        return [
            'data' => $filas,
            'total' => $total,
            'pagina' => $pagina,
            'porPagina' => $porPagina,
            'totalPaginas' => (int) ceil($total / $porPagina)
        ];
    }

    public function obtenerPaisesDisponibles(): array {
        $paises = [];
        $query = "SELECT DISTINCT codPais
                  FROM aerolinea
                  WHERE codPais IS NOT NULL AND codPais <> ''
                  ORDER BY codPais";
        $resultado = mysqli_query($this->conexion, $query);

        if ($resultado) {
            while ($fila = mysqli_fetch_assoc($resultado)) {
                $paises[] = $fila['codPais'];
            }
        }

        return $paises;
    }

    public function obtenerAerolineaPorId(int $id): ?array {
        $ceoAprobado = $this->condicionCEOAprobado();
        $query = "SELECT a.*, u.nombre as ceoNombre, u.apellido as ceoApellido, u.email as ceoEmail
                FROM aerolinea a
                LEFT JOIN usuario u ON u.idAerolinea = a.idAerolinea AND u.rol = 'ceo' AND {$ceoAprobado}
                WHERE a.idAerolinea = ?";

        $stmt = mysqli_prepare($this->conexion, $query);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $fila = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        return $fila ?: null;
    }
    // aca la logica para las cards de aerolinea en el listado

    public function obtenerEstadisticasListado(): array {
        // Total aerolíneas este mes
        $queryEsteMes = "SELECT COUNT(*) as total FROM aerolinea 
                        WHERE MONTH(fechaCreacion) = MONTH(NOW()) 
                        AND YEAR(fechaCreacion) = YEAR(NOW())";

        // Total aerolíneas mes pasado
        $queryMesPasado = "SELECT COUNT(*) as total FROM aerolinea 
                        WHERE MONTH(fechaCreacion) = MONTH(DATE_SUB(NOW(), INTERVAL 1 MONTH)) 
                        AND YEAR(fechaCreacion) = YEAR(DATE_SUB(NOW(), INTERVAL 1 MONTH))";

        // Total CEOs aprobados con aerolínea asignada
        $ceoAprobado = $this->condicionCEOAprobado();
        $queryCeos = "SELECT COUNT(*) as total FROM usuario u
                    WHERE u.rol = 'ceo' AND u.idAerolinea IS NOT NULL AND {$ceoAprobado}";

        $esteMes   = (int) mysqli_fetch_assoc(mysqli_query($this->conexion, $queryEsteMes))['total'];
        $mesPasado = (int) mysqli_fetch_assoc(mysqli_query($this->conexion, $queryMesPasado))['total'];
        $ceos      = (int) mysqli_fetch_assoc(mysqli_query($this->conexion, $queryCeos))['total'];

        $diff = $esteMes - $mesPasado;
        return [
            'nuevasEsteMes' => $esteMes,
            'mesPasado'     => $mesPasado,
            'diff'          => $diff,
            'ceos'          => $ceos,
        ];
    }

    // ── ADMIN: datos del detalle de aerolínea ────────────────────────────────

    /**
     * Ejecuta una consulta escalar y devuelve el valor de la columna "total".
     */
    private function obtenerValorEscalar(string $query, string $tipos = '', array $params = []): float {
        $stmt = mysqli_prepare($this->conexion, $query);
        if (!$stmt) {
            return 0.0;
        }

        if ($params) {
            mysqli_stmt_bind_param($stmt, $tipos, ...$params);
        }

        mysqli_stmt_execute($stmt);
        $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        return (float)($fila['total'] ?? 0);
    }

    /**
     * Métricas que se muestran en el card lateral del detalle.
     */
    public function obtenerMetricasDetalle(int $idAerolinea): array {
        $vuelos = (int)$this->obtenerValorEscalar(
            "SELECT COUNT(*) AS total FROM vuelo WHERE idAerolinea = ?",
            'i',
            [$idAerolinea]
        );

        $promociones = (int)$this->obtenerValorEscalar(
            "SELECT COUNT(*) AS total
             FROM promocion
             WHERE idAerolinea = ?
               AND estado = 'Aprobada'
               AND fechaInicio <= CURDATE()
               AND fechaFin >= CURDATE()",
            'i',
            [$idAerolinea]
        );

        return [
            'vuelosActivos'       => $vuelos,
            'promocionesVigentes' => $promociones,
        ];
    }

    /**
     * KPIs del tab Resumen.
     * "total" es el valor histórico que se muestra en la card.
     * "actual" y "anterior" son los valores generados este mes y el mes pasado,
     * usados para calcular la tendencia "vs mes anterior".
     */
    public function obtenerResumenAerolinea(int $idAerolinea): array {
        $vuelosTotal = (int)$this->obtenerValorEscalar(
            "SELECT COUNT(*) AS total FROM vuelo WHERE idAerolinea = ?",
            'i',
            [$idAerolinea]
        );
        $vuelosMesActual = (int)$this->obtenerValorEscalar(
            "SELECT COUNT(*) AS total
             FROM vuelo
             WHERE idAerolinea = ?
               AND YEAR(fechaCreacion) = YEAR(CURDATE())
               AND MONTH(fechaCreacion) = MONTH(CURDATE())",
            'i',
            [$idAerolinea]
        );
        $vuelosMesAnterior = (int)$this->obtenerValorEscalar(
            "SELECT COUNT(*) AS total
             FROM vuelo
             WHERE idAerolinea = ?
               AND YEAR(fechaCreacion) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
               AND MONTH(fechaCreacion) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))",
            'i',
            [$idAerolinea]
        );

        $promosActivas = (int)$this->obtenerValorEscalar(
            "SELECT COUNT(*) AS total
             FROM promocion
             WHERE idAerolinea = ?
               AND estado = 'Aprobada'
               AND fechaInicio <= CURDATE()
               AND fechaFin >= CURDATE()",
            'i',
            [$idAerolinea]
        );
        $promosMesActual = (int)$this->obtenerValorEscalar(
            "SELECT COUNT(*) AS total
             FROM promocion
             WHERE idAerolinea = ?
               AND estado = 'Aprobada'
               AND fechaInicio <= LAST_DAY(CURDATE())
               AND fechaFin >= DATE_FORMAT(CURDATE(), '%Y-%m-01')",
            'i',
            [$idAerolinea]
        );
        $promosMesAnterior = (int)$this->obtenerValorEscalar(
            "SELECT COUNT(*) AS total
             FROM promocion
             WHERE idAerolinea = ?
               AND estado = 'Aprobada'
               AND fechaInicio <= LAST_DAY(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
               AND fechaFin >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 MONTH), '%Y-%m-01')",
            'i',
            [$idAerolinea]
        );

        $basePasajeros = "SELECT COALESCE(SUM(r.cantPasajeros), 0) AS total
                          FROM reserva r
                          JOIN vuelo v ON v.idVuelo = r.idVuelo
                          WHERE v.idAerolinea = ? AND r.estadoReserva = 'confirmada'";
        $pasajerosTotal = (int)$this->obtenerValorEscalar($basePasajeros, 'i', [$idAerolinea]);
        $pasajerosMesActual = (int)$this->obtenerValorEscalar(
            $basePasajeros . " AND YEAR(r.fechaReserva) = YEAR(CURDATE()) AND MONTH(r.fechaReserva) = MONTH(CURDATE())",
            'i',
            [$idAerolinea]
        );
        $pasajerosMesAnterior = (int)$this->obtenerValorEscalar(
            $basePasajeros . " AND YEAR(r.fechaReserva) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND MONTH(r.fechaReserva) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))",
            'i',
            [$idAerolinea]
        );

        $baseVentas = "SELECT COALESCE(SUM(r.precioTotal), 0) AS total
                       FROM reserva r
                       JOIN vuelo v ON v.idVuelo = r.idVuelo
                       WHERE v.idAerolinea = ? AND r.estadoReserva = 'confirmada'";
        $ventasTotal = $this->obtenerValorEscalar($baseVentas, 'i', [$idAerolinea]);
        $ventasMesActual = $this->obtenerValorEscalar(
            $baseVentas . " AND YEAR(r.fechaReserva) = YEAR(CURDATE()) AND MONTH(r.fechaReserva) = MONTH(CURDATE())",
            'i',
            [$idAerolinea]
        );
        $ventasMesAnterior = $this->obtenerValorEscalar(
            $baseVentas . " AND YEAR(r.fechaReserva) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND MONTH(r.fechaReserva) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))",
            'i',
            [$idAerolinea]
        );

        return [
            'vuelos' => [
                'total'    => $vuelosTotal,
                'actual'   => $vuelosMesActual,
                'anterior' => $vuelosMesAnterior,
            ],
            'promociones' => [
                'total'    => $promosActivas,
                'actual'   => $promosMesActual,
                'anterior' => $promosMesAnterior,
            ],
            'pasajeros' => [
                'total'    => $pasajerosTotal,
                'actual'   => $pasajerosMesActual,
                'anterior' => $pasajerosMesAnterior,
            ],
            'ventas' => [
                'total'    => $ventasTotal,
                'actual'   => $ventasMesActual,
                'anterior' => $ventasMesAnterior,
            ],
        ];
    }

    /**
     * Top de rutas por pasajeros confirmados.
     */
    public function obtenerVuelosMasVendidos(int $idAerolinea, int $limite = 5): array {
        $query = "SELECT
                    CONCAT(
                        SUBSTRING_INDEX(v.origenVuelo, ' (', 1),
                        ' → ',
                        SUBSTRING_INDEX(v.destinoVuelo, ' (', 1)
                    ) AS ruta,
                    COUNT(DISTINCT v.idVuelo) AS vuelos,
                    COALESCE(SUM(r.cantPasajeros), 0) AS pasajeros
                  FROM vuelo v
                  JOIN reserva r ON r.idVuelo = v.idVuelo AND r.estadoReserva = 'confirmada'
                  WHERE v.idAerolinea = ?
                  GROUP BY v.origenVuelo, v.destinoVuelo
                  ORDER BY pasajeros DESC, vuelos DESC
                  LIMIT ?";

        $stmt = mysqli_prepare($this->conexion, $query);
        if (!$stmt) {
            return [];
        }

        mysqli_stmt_bind_param($stmt, 'ii', $idAerolinea, $limite);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $filas = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $filas[] = $row;
        }
        mysqli_stmt_close($stmt);

        return $filas;
    }

    /**
     * Promociones aprobadas y vigentes de la aerolínea.
     */
    public function obtenerPromocionesVigentes(int $idAerolinea, int $limite = 3): array {
        $query = "SELECT p.*
                  FROM promocion p
                  WHERE p.idAerolinea = ?
                    AND p.estado = 'Aprobada'
                    AND p.fechaInicio <= CURDATE()
                    AND p.fechaFin >= CURDATE()
                  ORDER BY p.fechaFin ASC, p.nombre ASC
                  LIMIT ?";

        $stmt = mysqli_prepare($this->conexion, $query);
        if (!$stmt) {
            return [];
        }

        mysqli_stmt_bind_param($stmt, 'ii', $idAerolinea, $limite);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $filas = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $filas[] = $row;
        }
        mysqli_stmt_close($stmt);

        return $filas;
    }

    /**
     * Top de rutas por recaudación de reservas confirmadas.
     */
    public function obtenerTramosMasRecaudados(int $idAerolinea, int $limite = 5): array {
        $query = "SELECT
                    CONCAT(
                        SUBSTRING_INDEX(v.origenVuelo, ' (', 1),
                        ' → ',
                        SUBSTRING_INDEX(v.destinoVuelo, ' (', 1)
                    ) AS ruta,
                    COALESCE(SUM(r.precioTotal), 0) AS recaudado
                  FROM vuelo v
                  JOIN reserva r ON r.idVuelo = v.idVuelo AND r.estadoReserva = 'confirmada'
                  WHERE v.idAerolinea = ?
                  GROUP BY v.origenVuelo, v.destinoVuelo
                  ORDER BY recaudado DESC
                  LIMIT ?";

        $stmt = mysqli_prepare($this->conexion, $query);
        if (!$stmt) {
            return [];
        }

        mysqli_stmt_bind_param($stmt, 'ii', $idAerolinea, $limite);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $filas = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $filas[] = $row;
        }
        mysqli_stmt_close($stmt);

        return $filas;
    }

    /**
     * Listado paginado de vuelos de una aerolínea para el tab "Vuelos".
     */
    public function listarVuelosAerolineaPaginado(
        int $idAerolinea,
        int $pagina,
        int $porPagina,
        string $busqueda = '',
        string $estado = ''
    ): array {
        $offset = ($pagina - 1) * $porPagina;
        $condiciones = ["v.idAerolinea = ?"];
        $params = [$idAerolinea];
        $tipos = 'i';

        if ($busqueda !== '') {
            $condiciones[] = "(v.origenVuelo LIKE ? OR v.destinoVuelo LIKE ? OR v.codigoOrigen LIKE ? OR v.codigoDestino LIKE ?)";
            $like = "%$busqueda%";
            array_push($params, $like, $like, $like, $like);
            $tipos .= 'ssss';
        }

        if ($estado === 'proximos') {
            $condiciones[] = "v.fechaHoraSalidaVuelo >= NOW()";
        } elseif ($estado === 'pasados') {
            $condiciones[] = "v.fechaHoraSalidaVuelo < NOW()";
        }

        $where = 'WHERE ' . implode(' AND ', $condiciones);

        $stmtTotal = mysqli_prepare($this->conexion, "SELECT COUNT(*) AS total FROM vuelo v $where");
        if (!$stmtTotal) {
            return ['data' => [], 'total' => 0, 'pagina' => $pagina, 'porPagina' => $porPagina, 'totalPaginas' => 0];
        }

        mysqli_stmt_bind_param($stmtTotal, $tipos, ...$params);
        mysqli_stmt_execute($stmtTotal);
        $total = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($stmtTotal))['total'];
        mysqli_stmt_close($stmtTotal);

        $orden = match ($estado) {
            'proximos' => 'v.fechaHoraSalidaVuelo ASC',
            'pasados'  => 'v.fechaHoraSalidaVuelo DESC',
            default    => 'v.fechaHoraSalidaVuelo DESC',
        };

        $params[] = $porPagina;
        $params[] = $offset;
        $tipos .= 'ii';

        $stmt = mysqli_prepare(
            $this->conexion,
            "SELECT v.* FROM vuelo v $where ORDER BY $orden LIMIT ? OFFSET ?"
        );

        if (!$stmt) {
            return ['data' => [], 'total' => $total, 'pagina' => $pagina, 'porPagina' => $porPagina, 'totalPaginas' => (int)ceil($total / $porPagina)];
        }

        mysqli_stmt_bind_param($stmt, $tipos, ...$params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $filas = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $filas[] = $row;
        }
        mysqli_stmt_close($stmt);

        return [
            'data'         => $filas,
            'total'        => $total,
            'pagina'       => $pagina,
            'porPagina'    => $porPagina,
            'totalPaginas' => (int)ceil($total / $porPagina),
        ];
    }

    /**
     * Listado paginado de promociones de una aerolínea para el tab "Promociones".
     */
    public function listarPromocionesAerolineaPaginado(
        int $idAerolinea,
        int $pagina,
        int $porPagina,
        string $busqueda = '',
        string $estado = ''
    ): array {
        $offset = ($pagina - 1) * $porPagina;
        $condiciones = ["p.idAerolinea = ?"];
        $params = [$idAerolinea];
        $tipos = 'i';

        if ($busqueda !== '') {
            $condiciones[] = "(p.codigo LIKE ? OR p.nombre LIKE ?)";
            $like = "%$busqueda%";
            $params[] = $like;
            $params[] = $like;
            $tipos .= 'ss';
        }

        if (in_array($estado, ['Pendiente', 'Aprobada', 'Denegada'], true)) {
            $condiciones[] = "p.estado = ?";
            $params[] = $estado;
            $tipos .= 's';
        }

        $where = 'WHERE ' . implode(' AND ', $condiciones);

        $stmtTotal = mysqli_prepare($this->conexion, "SELECT COUNT(*) AS total FROM promocion p $where");
        if (!$stmtTotal) {
            return ['data' => [], 'total' => 0, 'pagina' => $pagina, 'porPagina' => $porPagina, 'totalPaginas' => 0];
        }

        mysqli_stmt_bind_param($stmtTotal, $tipos, ...$params);
        mysqli_stmt_execute($stmtTotal);
        $total = (int)mysqli_fetch_assoc(mysqli_stmt_get_result($stmtTotal))['total'];
        mysqli_stmt_close($stmtTotal);

        $params[] = $porPagina;
        $params[] = $offset;
        $tipos .= 'ii';

        $stmt = mysqli_prepare(
            $this->conexion,
            "SELECT p.* FROM promocion p $where ORDER BY p.fechaCreacion DESC LIMIT ? OFFSET ?"
        );

        if (!$stmt) {
            return ['data' => [], 'total' => $total, 'pagina' => $pagina, 'porPagina' => $porPagina, 'totalPaginas' => (int)ceil($total / $porPagina)];
        }

        mysqli_stmt_bind_param($stmt, $tipos, ...$params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $filas = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $filas[] = $row;
        }
        mysqli_stmt_close($stmt);

        return [
            'data'         => $filas,
            'total'        => $total,
            'pagina'       => $pagina,
            'porPagina'    => $porPagina,
            'totalPaginas' => (int)ceil($total / $porPagina),
        ];
    }

    public function toggleEstadoAerolinea(int $id): void {
        $query = "UPDATE aerolinea SET activo = NOT activo WHERE idAerolinea = ?";
        $stmt = mysqli_prepare($this->conexion, $query);
        mysqli_stmt_bind_param($stmt, 'i', $id);

        try {
            mysqli_stmt_execute($stmt);
        } catch (mysqli_sql_exception $e) {
            flash_set('error', 'Error al cambiar el estado de la aerolínea.');
            redirect('index.php?pagina=aerolinea&seccion=listado');
        }

        mysqli_stmt_close($stmt);

        flash_set('success', 'Estado de la aerolínea actualizado.');
        redirect('index.php?pagina=aerolinea&seccion=listado');
    }


}



?>