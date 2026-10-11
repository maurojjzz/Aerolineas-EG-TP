<?php
require_once __DIR__ . '/../models/Novedad.php';

class NovedadController {
    private $model;

    public function __construct($db) {
        $this->model = new Novedad($db);
    }

    public function listarPaginado($pagina, $porPagina, $estado, $categoria, $busqueda) {
        return $this->model->listarPaginado($pagina, $porPagina, $estado, $categoria, $busqueda);
    }

    public function obtenerPorId($id) {
        return $this->model->obtenerPorId($id);
    }

    public function obtenerEstadisticas() {
        return $this->model->obtenerEstadisticas();
    }

    public function guardar($datos, $id = null) {
        if ($id) {
            return $this->model->actualizar($id, $datos);
        }
        return $this->model->crear($datos);
    }

    public function cambiarEstado($id, $estado) {
        return $this->model->cambiarEstado($id, $estado);
    }

    public function eliminar($id) {
        return $this->model->eliminar($id);
    }

    public function obtenerUltimasPublicadas($limite = 3) {
        return $this->model->obtenerUltimasPublicadas($limite);
    }

    public function listarTodasPublicadas() {
        return $this->model->listarTodasPublicadas();
    }
}