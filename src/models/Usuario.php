<?php

class Usuario{

    private ?int $idUsuario;
    private string $nombre;
    private string $apellido;
    private string $tipoDocumento;
    private string $nroDocumento;
    private string $contrasena;
    private string $email;
    private string $telefono;
    private string $fechaNacimiento;
    private string $rol;
    private bool $activo;
    private bool $emailVerificado;
    private ?string $tokenVerificacion;

    public function __construct(string $nombre, string $apellido, string $tipoDocumento, string $nroDocumento, string $contrasena, string $email, string $telefono, string $fechaNacimiento, string $rol = 'cliente', bool $activo=false, bool $emailVerificado=false, ?string $tokenVerificacion = null, ?int $idUsuario = null) {
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->tipoDocumento = $tipoDocumento;
        $this->nroDocumento = $nroDocumento;
        $this->contrasena = $contrasena;
        $this->email = $email;
        $this->telefono = $telefono;
        $this->fechaNacimiento = $fechaNacimiento;
        $this->rol = $rol;
        $this->activo = $activo;
        $this->emailVerificado = $emailVerificado;
        $this->tokenVerificacion = $tokenVerificacion;
        $this->idUsuario = $idUsuario;
    }

    public function getIdUsuario(): ?int { return $this->idUsuario; }
    public function getNombre(): string { return $this->nombre; }
    public function getApellido(): string { return $this->apellido; }
    public function getTipoDocumento(): string { return $this->tipoDocumento; }
    public function getNroDocumento(): string { return $this->nroDocumento; }
    public function getContrasena(): string { return $this->contrasena; }
    public function getEmail(): string { return $this->email; }
    public function getTelefono(): string { return $this->telefono; }
    public function getFechaNacimiento(): string { return $this->fechaNacimiento; }
    public function getRol(): string { return $this->rol; }
    public function getActivo(): bool { return $this->activo; }
    public function getEmailVerificado(): bool { return $this->emailVerificado; }
    public function getTokenVerificacion(): ?string { return $this->tokenVerificacion; }

    public function setIdUsuario(?int $idUsuario): void { $this->idUsuario = $idUsuario; }
    public function setNombre(string $nombre): void { $this->nombre = $nombre; }
    public function setApellido(string $apellido): void { $this->apellido = $apellido; }
    public function setTipoDocumento(string $t): void { $this->tipoDocumento = $t; }
    public function setNroDocumento(string $n): void { $this->nroDocumento = $n; }
    public function setContrasena(string $c): void { $this->contrasena = $c; }
    public function setEmail(string $email): void { $this->email = $email; }
    public function setTelefono(string $telefono): void { $this->telefono = $telefono; }
    public function setFechaNacimiento(string $f): void { $this->fechaNacimiento = $f; }
    public function setRol(string $rol): void { $this->rol = $rol; }
    public function setActivo(bool $activo): void { $this->activo = $activo; }
    public function setEmailVerificado(bool $v): void { $this->emailVerificado = $v; }
    public function setTokenVerificacion(?string $t): void { $this->tokenVerificacion = $t; }



}

?>