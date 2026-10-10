<?php
require_once __DIR__ . '/../models/PromocionDAO.php';
require_once __DIR__ . '/../models/Promocion.php';

class PromocionController {
    private PromocionDAO $promocionDAO;

    public function __construct(mysqli $db) {
        $this->promocionDAO = new PromocionDAO($db);
    }

    public function guardar(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        // Validar CSRF
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            flash_set('error', 'Token de seguridad inválido.');
            redirect('index.php?pagina=ceo&seccion=promociones');
            return;
        }

        $idUsuario = $_SESSION['usuario']['idUsuario'] ?? 0;
        $idAerolinea = $_SESSION['usuario']['idAerolinea'] ?? 0;

        if (!$idAerolinea) {
            flash_set('error', 'No tenés una aerolínea asociada para crear promociones.');
            redirect('index.php?pagina=ceo&seccion=promociones');
            return;
        }

        $nombre = trim($_POST['nombre'] ?? '');
        $descuentoPorcentaje = (float)($_POST['descuentoPorcentaje'] ?? 0);
        $fechaInicio = $_POST['fechaInicio'] ?? '';
        $fechaFin = $_POST['fechaFin'] ?? '';
        $descripcion = trim($_POST['descripcion'] ?? '');
        $condiciones = trim($_POST['condiciones'] ?? '');

        if (empty($nombre) || $descuentoPorcentaje <= 0 || empty($fechaInicio) || empty($fechaFin)) {
            flash_set('error', 'Por favor completá todos los campos requeridos.');
            redirect('index.php?pagina=ceo&seccion=promociones');
            return;
        }

        // NUEVO: validación de fechas en el servidor (el formulario solo protege desde el navegador)
        $tsInicio = strtotime($fechaInicio);
        $tsFin    = strtotime($fechaFin);
        if ($tsInicio === false || $tsFin === false || $tsFin < $tsInicio) {
            flash_set('error', 'La fecha de fin no puede ser anterior a la fecha de inicio.');
            redirect('index.php?pagina=ceo&seccion=promociones');
            return;
        }

        $codigo = 'PROM-' . strtoupper(substr(md5(uniqid()), 0, 4));

        $promocion = new Promocion(
            $codigo,
            (int)$idAerolinea,
            (int)$idUsuario,
            $nombre,
            $descuentoPorcentaje,
            $fechaInicio,
            $fechaFin,
            $descripcion ?: null,
            'Pendiente',
            $condiciones ?: null
        );

        if ($this->promocionDAO->crear($promocion)) {
            flash_set('success', 'Promoción enviada correctamente. Quedó pendiente de aprobación.');
        } else {
            flash_set('error', 'Error al guardar la promoción.');
        }

        redirect('index.php?pagina=ceo&seccion=promociones');
    }

    public function listarPorCEO(): array {
        $idAerolinea = $_SESSION['usuario']['idAerolinea'] ?? 0;
        return $this->promocionDAO->obtenerPorAerolinea((int)$idAerolinea);
    }

    // ---------- ADMIN ----------

    /** Todas las promociones de todos los CEOs (con aerolínea y CEO). */
    public function listarTodas(): array {
        return $this->promocionDAO->obtenerTodas();
    }

    public function obtenerPorId(int $id): ?array {
        return $this->promocionDAO->obtenerPorId($id);
    }

    public function obtenerPorIdCEO(int $id): ?array {
        $idAerolinea = (int)($_SESSION['usuario']['idAerolinea'] ?? 0);
        if ($idAerolinea <= 0) return null;
        return $this->promocionDAO->obtenerPorIdYAerolinea($id, $idAerolinea);
    }

    /** Aprueba o deniega una promoción pendiente. $estado: 'Aprobada' | 'Denegada'. */
    public function resolver(string $estado): void {
        $volver = 'index.php?pagina=promociones&seccion=listado';

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect($volver);
            return;
        }

        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            flash_set('error', 'Token de seguridad inválido.');
            redirect($volver);
            return;
        }

        if (!in_array($estado, ['Aprobada', 'Denegada'], true)) {
            flash_set('error', 'Acción no válida.');
            redirect($volver);
            return;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            flash_set('error', 'Promoción no válida.');
            redirect($volver);
            return;
        }

        if ($this->promocionDAO->actualizarEstado($id, $estado)) {
            flash_set('success', $estado === 'Aprobada' ? 'Promoción aprobada correctamente.' : 'Promoción denegada correctamente.');
        } else {
            flash_set('error', 'No se pudo actualizar: la promoción no existe o ya fue resuelta.');
        }

        redirect($volver);
    }
}