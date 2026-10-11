<?php
class Novedad {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function listarPaginado($pagina = 1, $porPagina = 10, $estado = '', $categoria = '', $busqueda = '') {
        $offset = ($pagina - 1) * $porPagina;
        $where = ["1=1"];
        $params = [];
        $types = "";

        if ($estado !== '') {
            $where[] = "estado = ?";
            $params[] = $estado;
            $types .= "s";
        }

        if ($categoria !== '') {
            $where[] = "categoria = ?";
            $params[] = $categoria;
            $types .= "s";
        }

        if ($busqueda !== '') {
            $where[] = "(titulo LIKE ? OR subtitulo LIKE ? OR contenido LIKE ?)";
            $searchTerm = "%" . $busqueda . "%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "sss";
        }

        $whereClause = implode(" AND ", $where);

        // Conteo total
        $sqlTotal = "SELECT COUNT(*) as total FROM novedades WHERE $whereClause";
        $stmtTotal = $this->db->prepare($sqlTotal);
        if (!empty($params)) {
            $stmtTotal->bind_param($types, ...$params);
        }
        $stmtTotal->execute();
        $total = $stmtTotal->get_result()->fetch_assoc()['total'];

        // Datos paginados (sin join de usuario)
        $sqlData = "SELECT * FROM novedades 
                    WHERE $whereClause 
                    ORDER BY fechaPublicacion DESC 
                    LIMIT ? OFFSET ?";
        
        $paramsData = array_merge($params, [$porPagina, $offset]);
        $typesData = $types . "ii";

        $stmtData = $this->db->prepare($sqlData);
        $stmtData->bind_param($typesData, ...$paramsData);
        $stmtData->execute();
        $data = $stmtData->get_result()->fetch_all(MYSQLI_ASSOC);

        return [
            'data' => $data,
            'total' => $total,
            'totalPaginas' => ceil($total / $porPagina)
        ];
    }

    public function obtenerPorId($id) {
        $sql = "SELECT * FROM novedades WHERE idNovedad = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function crear($datos) {
        $sql = "INSERT INTO novedades (titulo, subtitulo, contenido, categoria, prioridad, estado, imagen) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        // Corregido a 7 "s" (7 strings)
        $stmt->bind_param(
            "sssssss", 
            $datos['titulo'], 
            $datos['subtitulo'], 
            $datos['contenido'], 
            $datos['categoria'], 
            $datos['prioridad'], 
            $datos['estado'], 
            $datos['imagen']
        );
        return $stmt->execute();
    }

    public function actualizar($id, $datos) {
        $sql = "UPDATE novedades SET titulo = ?, subtitulo = ?, contenido = ?, categoria = ?, prioridad = ?, estado = ?, imagen = ? WHERE idNovedad = ?";
        $stmt = $this->db->prepare($sql);
        // 7 strings y 1 entero al final ("sssssssi")
        $stmt->bind_param(
            "sssssssi", 
            $datos['titulo'], 
            $datos['subtitulo'], 
            $datos['contenido'], 
            $datos['categoria'], 
            $datos['prioridad'], 
            $datos['estado'], 
            $datos['imagen'], 
            $id
        );
        return $stmt->execute();
    }

    public function cambiarEstado($id, $nuevoEstado) {
        $sql = "UPDATE novedades SET estado = ? WHERE idNovedad = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("si", $nuevoEstado, $id);
        return $stmt->execute();
    }

    public function eliminar($id) {
        $sql = "DELETE FROM novedades WHERE idNovedad = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function obtenerEstadisticas() {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN estado = 'publicada' THEN 1 ELSE 0 END) as publicadas,
                    SUM(CASE WHEN estado = 'borrador' THEN 1 ELSE 0 END) as borradores,
                    SUM(CASE WHEN estado = 'archivada' THEN 1 ELSE 0 END) as archivadas
                FROM novedades";
        $res = $this->db->query($sql);
        return $res->fetch_assoc();
    }

    public function obtenerUltimasPublicadas($limite = 3) {
        $sql = "SELECT * FROM novedades WHERE estado = 'publicada' ORDER BY fechaPublicacion DESC LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $limite);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function listarTodasPublicadas() {
        $sql = "SELECT * FROM novedades WHERE estado = 'publicada' ORDER BY fechaPublicacion DESC";
        $resultado = $this->db->query($sql);
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }
}