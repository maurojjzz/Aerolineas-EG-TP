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

        $codigo = 'PROM-' . strtoupper(substr(md5(uniqid()), 0, 4));

        $query = "INSERT INTO promocion
                    (codigo, idAerolinea, idUsuarioCreador, nombre, descripcion,
                    descuentoPorcentaje, fechaInicio, fechaFin, estado, condiciones)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pendiente', ?)";

        $stmt = mysqli_prepare($this->conexion, $query);
        if (!$stmt) {
            flash_set('error', 'Error interno del servidor.');
            redirect('index.php?pagina=promociones');
        }

        mysqli_stmt_bind_param(
            $stmt,
            'siissdsss',
            $codigo,
            $idAerolinea,
            $idUsuario,
            $nombre,
            $descripcion,
            $descuentoPorcentaje,
            $fechaInicio,
            $fechaFin,
            $condiciones
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

    // ── CEO: eliminar promo propia (solo si está Pendiente) ───────────────────
    public function eliminarPromocion(): void {
        $id          = (int)($_POST['id']          ?? 0);
        $idAerolinea = (int)($_SESSION['usuario']['idAerolinea'] ?? 0);

        if (!$id || !$idAerolinea) {
            flash_set('error', 'Datos inválidos.');
            redirect('index.php?pagina=promociones');
        }

        $stmt = mysqli_prepare($this->conexion,
            "DELETE FROM promocion
            WHERE idPromocion = ? AND idAerolinea = ? AND estado = 'Pendiente'");
        mysqli_stmt_bind_param($stmt, 'ii', $id, $idAerolinea);

        try {
            mysqli_stmt_execute($stmt);
            $afectadas = mysqli_stmt_affected_rows($stmt);
            mysqli_stmt_close($stmt);

            if ($afectadas > 0) {
                flash_set('success', 'Promoción eliminada correctamente.');
            } else {
                flash_set('error', 'No se puede eliminar: la promoción no existe, no te pertenece o ya fue procesada.');
            }
        } catch (mysqli_sql_exception $e) {
            mysqli_stmt_close($stmt);
            flash_set('error', 'Error al eliminar la promoción.');
        }

        redirect('index.php?pagina=promociones');
    }
}