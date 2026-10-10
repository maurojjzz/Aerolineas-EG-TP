<?php

class Vuelo {

    private ?int $idVuelo;
    private int $idAerolinea;
    private string $origenVuelo;
    private string $destinoVuelo;
    private string $fechaHoraSalidaVuelo; 
    private int $asientosDisponibles;
    private float $precioVuelo;
    private ?string $fechaCreacion;

    public function __construct(
        int $idAerolinea,
        string $origenVuelo,
        string $destinoVuelo,
        string $fechaHoraSalidaVuelo,
        int $asientosDisponibles,
        float $precioVuelo,
        ?int $idVuelo = null,
        ?string $fechaCreacion = null
    ) {
        $this->idAerolinea         = $idAerolinea;
        $this->origenVuelo         = $origenVuelo;
        $this->destinoVuelo        = $destinoVuelo;
        $this->fechaHoraSalidaVuelo    = $fechaHoraSalidaVuelo;
        $this->asientosDisponibles = $asientosDisponibles;
        $this->precioVuelo         = $precioVuelo;
        $this->idVuelo             = $idVuelo;
        $this->fechaCreacion       = $fechaCreacion;
    }

    public function getIdVuelo(): ?int            { return $this->idVuelo; }
    public function getIdAerolinea(): int          { return $this->idAerolinea; }
    public function getOrigenVuelo(): string       { return $this->origenVuelo; }
    public function getDestinoVuelo(): string      { return $this->destinoVuelo; }
    public function getFechaHoraSalidaVuelo(): string  { return $this->fechaHoraSalidaVuelo; }
    public function getAsientosDisponibles(): int  { return $this->asientosDisponibles; }
    public function getPrecioVuelo(): float        { return $this->precioVuelo; }
    public function getFechaCreacion(): ?string    { return $this->fechaCreacion; }

    public function setIdVuelo(?int $v): void             { $this->idVuelo = $v; }
    public function setOrigenVuelo(string $v): void        { $this->origenVuelo = $v; }
    public function setDestinoVuelo(string $v): void       { $this->destinoVuelo = $v; }
    public function setFechaHoraSalidaVuelo(string $v): void   { $this->fechaHoraSalidaVuelo = $v; }
    public function setAsientosDisponibles(int $v): void   { $this->asientosDisponibles = $v; }
    public function setPrecioVuelo(float $v): void         { $this->precioVuelo = $v; }
}