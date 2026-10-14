<?php

class Reserva {
    private ?int    $idReserva;
    private int     $idUsuario;
    private int     $idVuelo;
    private ?string $fechaReserva;
    private string  $estadoReserva;
    private int     $cantPasajeros;
    private float   $precioTotal;

    public function __construct(
        int     $idUsuario,
        int     $idVuelo,
        int     $cantPasajeros,
        float   $precioTotal,
        string  $estadoReserva = 'pendiente de pago',
        ?string $fechaReserva  = null,
        ?int    $idReserva     = null
    ) {
        $this->idUsuario     = $idUsuario;
        $this->idVuelo       = $idVuelo;
        $this->cantPasajeros = $cantPasajeros;
        $this->precioTotal   = $precioTotal;
        $this->estadoReserva = $estadoReserva;
        $this->fechaReserva  = $fechaReserva;
        $this->idReserva     = $idReserva;
    }

    public function getIdReserva(): ?int    { return $this->idReserva; }
    public function getIdUsuario(): int     { return $this->idUsuario; }
    public function getIdVuelo(): int       { return $this->idVuelo; }
    public function getFechaReserva(): ?string  { return $this->fechaReserva; }
    public function getEstadoReserva(): string  { return $this->estadoReserva; }
    public function getCantPasajeros(): int { return $this->cantPasajeros; }
    public function getPrecioTotal(): float { return $this->precioTotal; }

    public function setEstadoReserva(string $v): void { $this->estadoReserva = $v; }
    public function setFechaReserva(string $v): void { $this->fechaReserva = $v; }
    public function setIdReserva(int $v): void { $this->idReserva = $v; }
    public function setIdUsuario(int $v): void { $this->idUsuario = $v; }
    public function setIdVuelo(int $v): void { $this->idVuelo = $v; }
    public function setCantPasajeros(int $v): void { $this->cantPasajeros = $v; }
    public function setPrecioTotal(float $v): void { $this->precioTotal = $v; }
    
}