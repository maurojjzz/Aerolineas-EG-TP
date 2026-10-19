<?php

class Promocion {

    private ?int    $idPromocion;
    private string  $codigo;
    private int     $idAerolinea;
    private int     $idUsuarioCreador;
    private string  $nombre;
    private ?string $descripcion;
    private float   $descuentoPorcentaje;
    private string  $fechaInicio;
    private string  $fechaFin;
    private string  $estado;
    private ?string $condiciones;
    private ?string $fechaCreacion;

    public function __construct(
        string  $codigo,
        int     $idAerolinea,
        int     $idUsuarioCreador,
        string  $nombre,
        float   $descuentoPorcentaje,
        string  $fechaInicio,
        string  $fechaFin,
        ?string $descripcion   = null,
        string  $estado        = 'Pendiente',
        ?string $condiciones   = null,
        ?int    $idPromocion   = null,
        ?string $fechaCreacion = null
    ) {
        $this->codigo               = $codigo;
        $this->idAerolinea          = $idAerolinea;
        $this->idUsuarioCreador     = $idUsuarioCreador;
        $this->nombre               = $nombre;
        $this->descuentoPorcentaje  = $descuentoPorcentaje;
        $this->fechaInicio          = $fechaInicio;
        $this->fechaFin             = $fechaFin;
        $this->descripcion          = $descripcion;
        $this->estado               = $estado;
        $this->condiciones          = $condiciones;
        $this->idPromocion          = $idPromocion;
        $this->fechaCreacion        = $fechaCreacion;
    }

    public function getIdPromocion(): ?int           { return $this->idPromocion; }
    public function getCodigo(): string              { return $this->codigo; }
    public function getIdAerolinea(): int            { return $this->idAerolinea; }
    public function getIdUsuarioCreador(): int       { return $this->idUsuarioCreador; }
    public function getNombre(): string              { return $this->nombre; }
    public function getDescripcion(): ?string        { return $this->descripcion; }
    public function getDescuentoPorcentaje(): float  { return $this->descuentoPorcentaje; }
    public function getFechaInicio(): string         { return $this->fechaInicio; }
    public function getFechaFin(): string            { return $this->fechaFin; }
    public function getEstado(): string              { return $this->estado; }
    public function getCondiciones(): ?string        { return $this->condiciones; }
    public function getFechaCreacion(): ?string      { return $this->fechaCreacion; }

    public function setIdPromocion(?int $v): void           { $this->idPromocion = $v; }
    public function setCodigo(string $v): void              { $this->codigo = $v; }
    public function setIdAerolinea(int $v): void            { $this->idAerolinea = $v; }
    public function setIdUsuarioCreador(int $v): void       { $this->idUsuarioCreador = $v; }
    public function setNombre(string $v): void              { $this->nombre = $v; }
    public function setDescripcion(?string $v): void        { $this->descripcion = $v; }
    public function setDescuentoPorcentaje(float $v): void  { $this->descuentoPorcentaje = $v; }
    public function setFechaInicio(string $v): void         { $this->fechaInicio = $v; }
    public function setFechaFin(string $v): void            { $this->fechaFin = $v; }
    public function setEstado(string $v): void              { $this->estado = $v; }
    public function setCondiciones(?string $v): void        { $this->condiciones = $v; }
    public function setFechaCreacion(?string $v): void      { $this->fechaCreacion = $v; }
}