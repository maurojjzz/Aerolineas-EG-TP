<?php

class Promocion {

    private ?int $idPromocion;
    private string $codigo;
    private int $idAerolinea;
    private int $idUsuarioCreador;
    private string $nombre;
    private ?string $descripcion;
    private float $descuentoPorcentaje;
    private string $fechaInicio;
    private string $fechaFin;
    private string $estado;
    private ?string $condiciones;
    private ?string $fechaCreacion;

    public function __construct(
        string $codigo,
        int $idAerolinea,
        int $idUsuarioCreador,
        string $nombre,
        float $descuentoPorcentaje,
        string $fechaInicio,
        string $fechaFin,
        ?string $descripcion = null,
        string $estado = 'Pendiente',
        ?string $condiciones = null,
        ?int $idPromocion = null,
        ?string $fechaCreacion = null
    ) {
        $this->codigo = $codigo;
        $this->idAerolinea = $idAerolinea;
        $this->idUsuarioCreador = $idUsuarioCreador;
        $this->nombre = $nombre;
        $this->descuentoPorcentaje = $descuentoPorcentaje;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
        $this->descripcion = $descripcion;
        $this->estado = $estado;
        $this->condiciones = $condiciones;
        $this->idPromocion = $idPromocion;
        $this->fechaCreacion = $fechaCreacion;
    }

    public function getIdPromocion(): ?int { return $this->idPromocion; }
    public function getCodigo(): string { return $this->codigo; }
    public function getIdAerolinea(): int { return $this->idAerolinea; }
    public function getIdUsuarioCreador(): int { return $this->idUsuarioCreador; }
    public function getNombre(): string { return $this->nombre; }
    public function getDescripcion(): ?string { return $this->descripcion; }
    public function getDescuentoPorcentaje(): float { return $this->descuentoPorcentaje; }
    public function getFechaInicio(): string { return $this->fechaInicio; }
    public function getFechaFin(): string { return $this->fechaFin; }
    public function getEstado(): string { return $this->estado; }
    public function getCondiciones(): ?string { return $this->condiciones; }
    public function getFechaCreacion(): ?string { return $this->fechaCreacion; }

    public function setIdPromocion(?int $idPromocion): void { $this->idPromocion = $idPromocion; }
    public function setCodigo(string $codigo): void { $this->codigo = $codigo; }
    public function setIdAerolinea(int $idAerolinea): void { $this->idAerolinea = $idAerolinea; }
    public function setIdUsuarioCreador(int $idUsuarioCreador): void { $this->idUsuarioCreador = $idUsuarioCreador; }
    public function setNombre(string $nombre): void { $this->nombre = $nombre; }
    public function setDescripcion(?string $descripcion): void { $this->descripcion = $descripcion; }
    public function setDescuentoPorcentaje(float $descuentoPorcentaje): void { $this->descuentoPorcentaje = $descuentoPorcentaje; }
    public function setFechaInicio(string $fechaInicio): void { $this->fechaInicio = $fechaInicio; }
    public function setFechaFin(string $fechaFin): void { $this->fechaFin = $fechaFin; }
    public function setEstado(string $estado): void { $this->estado = $estado; }
    public function setCondiciones(?string $condiciones): void { $this->condiciones = $condiciones; }
    public function setFechaCreacion(?string $fechaCreacion): void { $this->fechaCreacion = $fechaCreacion; }
}