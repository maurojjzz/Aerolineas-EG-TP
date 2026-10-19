<?php

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/Novedad.php';

class NovedadController {

    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // ── ADMIN: crear novedad ──────────────────────────────────────────────────
    public function crearNovedad(): void {
        $titulo     = trim($_POST['titulo']     ?? '');
        $subtitulo  = trim($_POST['subtitulo']  ?? '') ?: null;
        $contenido  = trim($_POST['contenido']  ?? '');
        $categoria  = $_POST['categoria']  ?? 'general';
        $prioridad  = $_POST['prioridad']  ?? 'media';
        $estado     = $_POST['estado']     ?? 'publicada';
        $imagen     = $_POST['imagen']     ?? null;

        if (!$titulo || !$contenido) {
            flash_set('error', 'El título y el contenido son obligatorios.');
            redirect('index.php?pagina=novedades&seccion=crear');
        }

        $stmt = mysqli_prepare($this->conexion,
            "INSERT INTO novedades (titulo, subtitulo, contenido, categoria, prioridad, estado, imagen)
             VALUES (?, ?, ?, ?, ?, ?, ?)");

        if (!$stmt) {
            flash_set('error', 'Error interno del servidor.');
            redirect('index.php?pagina=novedades&seccion=crear');
        }

        mysqli_stmt_bind_param($stmt, 'sssssss',
            $titulo, $subtitulo, $contenido, $categoria, $prioridad, $estado, $imagen);

        try {
            mysqli_stmt_execute($stmt);
        } catch (mysqli_sql_exception $e) {
            mysqli_stmt_close($stmt);
            flash_set('error', 'Error al crear la novedad.');
            redirect('index.php?pagina=novedades&seccion=crear');
        }

        mysqli_stmt_close($stmt);
        flash_set('success', 'Novedad publicada correctamente.');
        redirect('index.php?pagina=novedades&seccion=listado');
    }

    // ── ADMIN: editar novedad ─────────────────────────────────────────────────
    public function editarNovedad(int $id): void {
        $titulo    = trim($_POST['titulo']    ?? '');
        $subtitulo = trim($_POST['subtitulo'] ?? '') ?: null;
        $contenido = trim($_POST['contenido'] ?? '');
        $categoria = $_POST['categoria'] ?? 'general';
        $prioridad = $_POST['prioridad'] ?? 'media';
        $estado    = $_POST['estado']    ?? 'publicada';
        $imagen    = $_POST['imagen']    ?? null;

        if (!$titulo || !$contenido) {
            flash_set('error', 'El título y el contenido son obligatorios.');
            redirect("index.php?pagina=novedades&seccion=editar&id=$id");
        }

        $stmt = mysqli_prepare($this->conexion,
            "UPDATE novedades
             SET titulo=?, subtitulo=?, contenido=?, categoria=?, prioridad=?, estado=?, imagen=?
             WHERE idNovedad=?");

        if (!$stmt) {
            flash_set('error', 'Error interno del servidor.');
            redirect("index.php?pagina=novedades&seccion=editar&id=$id");
        }

        mysqli_stmt_bind_param($stmt, 'sssssss i',
            $titulo, $subtitulo, $contenido, $categoria, $prioridad, $estado, $imagen, $id);

        try {
            mysqli_stmt_execute($stmt);
        } catch (mysqli_sql_exception $e) {
            mysqli_stmt_close($stmt);
            flash_set('error', 'Error al actualizar la novedad.');
            redirect("index.php?pagina=novedades&seccion=editar&id=$id");
        }

        mysqli_stmt_close($stmt);
        flash_set('success', 'Novedad actualizada correctamente.');
        redirect('index.php?pagina=novedades&seccion=listado');
    }

    // ── ADMIN: eliminar novedad ───────────────────────────────────────────────
    public function eliminarNovedad(int $id): void {
        $stmt = mysqli_prepare($this->conexion,
            "DELETE FROM novedades WHERE idNovedad = ?");

        if (!$stmt) {
            flash_set('error', 'Error interno del servidor.');
            redirect('index.php?pagina=novedades&seccion=listado');
        }

        mysqli_stmt_bind_param($stmt, 'i', $id);

        try {
            mysqli_stmt_execute($stmt);
        } catch (mysqli_sql_exception $e) {
            mysqli_stmt_close($stmt);
            flash_set('error', 'Error al eliminar la novedad.');
            redirect('index.php?pagina=novedades&seccion=listado');
        }

        mysqli_stmt_close($stmt);
        flash_set('success', 'Novedad eliminada correctamente.');
        redirect('index.php?pagina=novedades&seccion=listado');
    }

    // ── ADMIN: listar paginado con filtros ────────────────────────────────────
    public function listarPaginado(
        int    $pagina    = 1,
        int    $porPagina = 10,
        string $estado    = '',
        string $categoria = '',
        string $busqueda  = ''
    ): array {
        $offset     = ($pagina - 1) * $porPagina;
        $where      = ['1=1'];
        $params     = [];
        $tipos      = '';

        if ($estado !== '') {
            $where[]  = 'estado = ?';
            $params[] = $estado;
            $tipos   .= 's';
        }
        if ($categoria !== '') {
            $where[]  = 'categoria = ?';
            $params[] = $categoria;
            $tipos   .= 's';
        }
        if ($busqueda !== '') {
            $where[]  = '(titulo LIKE ? OR subtitulo LIKE ? OR contenido LIKE ?)';
            $like     = "%$busqueda%";
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $tipos   .= 'sss';
        }

        $whereClause = implode(' AND ', $where);

        // Total
        $stmtTotal = mysqli_prepare($this->conexion,
            "SELECT COUNT(*) as total FROM novedades WHERE $whereClause");
        if ($params) mysqli_stmt_bind_param($stmtTotal, $tipos, ...$params);
        mysqli_stmt_execute($stmtTotal);
        $total = (int) mysqli_fetch_assoc(mysqli_stmt_get_result($stmtTotal))['total'];
        mysqli_stmt_close($stmtTotal);

        // Datos paginados
        $paramsData = [...$params, $porPagina, $offset];
        $stmtData   = mysqli_prepare($this->conexion,
            "SELECT * FROM novedades WHERE $whereClause
             ORDER BY fechaPublicacion DESC LIMIT ? OFFSET ?");
        mysqli_stmt_bind_param($stmtData, $tipos . 'ii', ...$paramsData);
        mysqli_stmt_execute($stmtData);
        $result = mysqli_stmt_get_result($stmtData);
        $data   = [];
        while ($row = mysqli_fetch_assoc($result)) $data[] = $row;
        mysqli_stmt_close($stmtData);

        return [
            'data'         => $data,
            'total'        => $total,
            'totalPaginas' => (int) ceil($total / $porPagina),
        ];
    }

    // ── Obtener por id ────────────────────────────────────────────────────────
    public function obtenerPorId(int $id): ?array {
        $stmt = mysqli_prepare($this->conexion,
            "SELECT * FROM novedades WHERE idNovedad = ?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        return $fila ?: null;
    }

    // ── Estadísticas para cards del listado admin ─────────────────────────────
    public function obtenerEstadisticas(): array {
        $result = mysqli_query($this->conexion,
            "SELECT
                COUNT(*) as total,
                SUM(CASE WHEN estado='publicada'  THEN 1 ELSE 0 END) as publicadas,
                SUM(CASE WHEN estado='borrador'   THEN 1 ELSE 0 END) as borradores,
                SUM(CASE WHEN estado='archivada'  THEN 1 ELSE 0 END) as archivadas
             FROM novedades");
        return mysqli_fetch_assoc($result) ?? ['total'=>0,'publicadas'=>0,'borradores'=>0,'archivadas'=>0];
    }

    // ── Últimas N publicadas (para home del cliente) ──────────────────────────
    public function obtenerUltimasPublicadas(int $limite = 3): array {
        $stmt = mysqli_prepare($this->conexion,
            "SELECT * FROM novedades WHERE estado = 'publicada'
             ORDER BY fechaPublicacion DESC LIMIT ?");
        mysqli_stmt_bind_param($stmt, 'i', $limite);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $filas  = [];
        while ($row = mysqli_fetch_assoc($result)) $filas[] = $row;
        mysqli_stmt_close($stmt);
        return $filas;
    }

    // ── Todas las publicadas (para listado del cliente) ───────────────────────
    public function listarTodasPublicadas(): array {
        $result = mysqli_query($this->conexion,
            "SELECT * FROM novedades WHERE estado = 'publicada'
             ORDER BY fechaPublicacion DESC");
        if (!$result) return [];
        $filas = [];
        while ($row = mysqli_fetch_assoc($result)) $filas[] = $row;
        return $filas;
    }
}