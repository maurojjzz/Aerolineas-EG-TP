<?php

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/Vuelos.php';

class VueloController {

    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function crearVuelo(int $idAerolinea): void {
        $origen = trim($_POST['origen']   ?? '');
        $destino = trim($_POST['destino']  ?? '');
        $fecha = trim($_POST['fecha']    ?? '');
        $hora = trim($_POST['hora']     ?? '');
        $asientos = (int)($_POST['asientos'] ?? 0);
        $precio = (float)($_POST['precio'] ?? 0);

        if (!$origen || !$destino || !$fecha || !$hora || $asientos <= 0 || $precio <= 0) {
            flash_set('error', 'Faltan campos obligatorios o valores inválidos.');
            redirect("index.php?pagina=vuelo");
        }

        // Validar que la fecha sea hoy o futura
        $fechaHora = $fecha . ' ' . $hora . ':00';
        if (strtotime($fechaHora) < time()) {
            flash_set('error', 'La fecha y hora de salida no puede ser en el pasado.');
            redirect("index.php?pagina=vuelo");
        }

        $query = "INSERT INTO vuelo 
                (idAerolinea, origenVuelo, destinoVuelo, fechaHoraSalidaVuelo, asientosDisponibles, precioVuelo)
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($this->conexion, $query);
        if (!$stmt) {
            flash_set('error', 'Error interno del servidor.');
            redirect("index.php?pagina=vuelo");
        }

        mysqli_stmt_bind_param(
            $stmt, 
            'isssid',
            $idAerolinea, 
            $origen, 
            $destino, 
            $fechaHora, 
            $asientos, 
            $precio
        );

        try {
            mysqli_stmt_execute($stmt);
        } catch (mysqli_sql_exception $e) {
            mysqli_stmt_close($stmt);
            flash_set('error', 'Error al crear el vuelo.');
            redirect("index.php?pagina=vuelo");
        }

        mysqli_stmt_close($stmt);
        flash_set('success', 'Vuelo creado correctamente.');
        redirect("index.php?pagina=ceo&tab=vuelos");
    }

    public function editarVuelo(int $idVuelo, int $idAerolinea): void {
        $origen = trim($_POST['origen']   ?? '');
        $destino = trim($_POST['destino']  ?? '');
        $fecha = trim($_POST['fecha']    ?? '');
        $hora = trim($_POST['hora']     ?? '');
        $asientos = (int)($_POST['asientos'] ?? 0);
        $precio = (float)($_POST['precio'] ?? 0);

        if (!$origen || !$destino || !$fecha || !$hora || $asientos <= 0 || $precio <= 0) {
            flash_set('error', 'Faltan campos obligatorios o valores inválidos.');
            redirect("index.php?pagina=vuelo&id=$idVuelo");
        }

        $fechaHora = $fecha . ' ' . $hora . ':00';

        $query = "UPDATE vuelo SET origenVuelo=?, destinoVuelo=?, fechaHoraSalidaVuelo=?, asientosDisponibles=?, precioVuelo=?
                WHERE idVuelo=? AND idAerolinea=?";

        $stmt = mysqli_prepare($this->conexion, $query);
        if (!$stmt) {
            flash_set('error', 'Error interno del servidor.');
            redirect("index.php?pagina=vuelo&id=$idVuelo");
        }

        mysqli_stmt_bind_param(
            $stmt, 
            'sssidii',
            $origen, 
            $destino, 
            $fechaHora, 
            $asientos, 
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
        redirect("index.php?pagina=ceo&tab=vuelos");
    }

    public function eliminarVuelo(int $idVuelo, int $idAerolinea): void {
        $query = "DELETE FROM vuelo WHERE idVuelo=? AND idAerolinea=?";
        $stmt = mysqli_prepare($this->conexion, $query);
        if (!$stmt) {
            flash_set('error', 'Error interno del servidor.');
            redirect("index.php?pagina=ceo&tab=vuelos");
        }

        mysqli_stmt_bind_param($stmt, 'ii', $idVuelo, $idAerolinea);

        try {
            mysqli_stmt_execute($stmt);
        } catch (mysqli_sql_exception $e) {
            mysqli_stmt_close($stmt);
            flash_set('error', 'Error al eliminar el vuelo.');
            redirect("index.php?pagina=ceo&tab=vuelos");
        }

        mysqli_stmt_close($stmt);
        flash_set('success', 'Vuelo eliminado correctamente.');
        redirect("index.php?pagina=ceo&tab=vuelos");
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
        string $busqueda = ''
    ): array {
        $offset = ($pagina - 1) * $porPagina;
        $where  = "WHERE idAerolinea = ?";
        $params = [$idAerolinea];
        $tipos  = 'i';

        if ($busqueda !== '') {
            $where   .= " AND (origenVuelo LIKE ? OR destinoVuelo LIKE ?)";
            $like     = "%$busqueda%";
            $params[] = $like;
            $params[] = $like;
            $tipos   .= 'ss';
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
            "SELECT * FROM vuelo $where ORDER BY fechaHoraSalidaVuelo ASC LIMIT ? OFFSET ?");
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
}