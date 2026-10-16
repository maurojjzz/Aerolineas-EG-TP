<?php
require_once __DIR__ . '/Promocion.php';

class PromocionDAO {
    private mysqli $db;

    public function __construct(mysqli $db) {
        $this->db = $db;
    }

    public function crear(Promocion $promocion): bool {
        $query = "INSERT INTO promocion (codigo, idAerolinea, idUsuarioCreador, nombre, descripcion, descuentoPorcentaje, fechaInicio, fechaFin, estado, condiciones) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($this->db, $query);

        $codigo = $promocion->getCodigo();
        $idAerolinea = $promocion->getIdAerolinea();
        $idUsuarioCreador = $promocion->getIdUsuarioCreador();
        $nombre = $promocion->getNombre();
        $descripcion = $promocion->getDescripcion();
        $descuentoPorcentaje = $promocion->getDescuentoPorcentaje();
        $fechaInicio = $promocion->getFechaInicio();
        $fechaFin = $promocion->getFechaFin();
        $estado = $promocion->getEstado();
        $condiciones = $promocion->getCondiciones();

        mysqli_stmt_bind_param(
            $stmt, 
            'siissdssss', 
            $codigo, 
            $idAerolinea, 
            $idUsuarioCreador, 
            $nombre, 
            $descripcion, 
            $descuentoPorcentaje, 
            $fechaInicio, 
            $fechaFin, 
            $estado,
            $condiciones
        );

        return mysqli_stmt_execute($stmt);
    }

    public function obtenerPorAerolinea(int $idAerolinea): array {
        $query = "SELECT p.*, CONCAT(u.nombre, ' ', u.apellido) AS creadorNombre 
                  FROM promocion p
                  JOIN usuario u ON p.idUsuarioCreador = u.idUsuario
                  WHERE p.idAerolinea = ? 
                  ORDER BY p.fechaCreacion DESC";
                  
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, 'i', $idAerolinea);
        mysqli_stmt_execute($stmt);
        
        return mysqli_stmt_get_result($stmt)->fetch_all(MYSQLI_ASSOC);
    }

    /** ADMIN: todas las promociones de todas las aerolíneas, con aerolínea y CEO creador. */
    public function obtenerTodas(): array {
        $query = "SELECT p.*, a.nombreAerolinea, a.codigoIATA,
                         u.nombre AS ceoNombre, u.apellido AS ceoApellido
                  FROM promocion p
                  JOIN aerolinea a ON p.idAerolinea = a.idAerolinea
                  JOIN usuario u   ON p.idUsuarioCreador = u.idUsuario
                  ORDER BY p.fechaCreacion DESC";

        $result = mysqli_query($this->db, $query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    /** ADMIN: cambia el estado SOLO si la promoción sigue pendiente. */
    public function actualizarEstado(int $idPromocion, string $estado): bool {
        $query = "UPDATE promocion SET estado = ? WHERE idPromocion = ?";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, 'si', $estado, $idPromocion);

        return mysqli_stmt_execute($stmt) && mysqli_stmt_affected_rows($stmt) > 0;
    }

    /*** Obtiene una promoción específica por su ID junto con su aerolínea y datos del CEO */
       public function obtenerPorId(int $id): ?array {
        $query = "SELECT p.*, a.nombreAerolinea, a.codigoIATA,
                         u.nombre AS ceoNombre, u.apellido AS ceoApellido
                  FROM promocion p
                  JOIN aerolinea a ON p.idAerolinea = a.idAerolinea
                  JOIN usuario u   ON p.idUsuarioCreador = u.idUsuario
                  WHERE p.idPromocion = ?";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        return mysqli_stmt_get_result($stmt)->fetch_assoc() ?: null;
    }

    public function obtenerPorIdYAerolinea(int $id, int $idAerolinea): ?array {
        $query = "SELECT p.*, a.nombreAerolinea, a.codigoIATA,
                         u.nombre AS ceoNombre, u.apellido AS ceoApellido
                  FROM promocion p
                  JOIN aerolinea a ON p.idAerolinea = a.idAerolinea
                  JOIN usuario u   ON p.idUsuarioCreador = u.idUsuario
                  WHERE p.idPromocion = ? AND p.idAerolinea = ?";
        $stmt = mysqli_prepare($this->db, $query);
        mysqli_stmt_bind_param($stmt, 'ii', $id, $idAerolinea);
        mysqli_stmt_execute($stmt);
        return mysqli_stmt_get_result($stmt)->fetch_assoc() ?: null;
    }
}