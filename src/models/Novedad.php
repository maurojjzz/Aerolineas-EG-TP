<?php

class Novedad {

    private ?int    $idNovedad;
    private string  $titulo;
    private ?string $subtitulo;
    private string  $contenido;
    private string  $categoria;
    private string  $prioridad;
    private string  $estado;
    private ?string $imagen;
    private ?string $fechaPublicacion;

    public function __construct(
        string  $titulo,
        string  $contenido,
        string  $categoria      = 'general',
        string  $prioridad      = 'media',
        string  $estado         = 'publicada',
        ?string $subtitulo      = null,
        ?string $imagen         = null,
        ?int    $idNovedad      = null,
        ?string $fechaPublicacion = null
    ) {
        $this->titulo           = $titulo;
        $this->contenido        = $contenido;
        $this->categoria        = $categoria;
        $this->prioridad        = $prioridad;
        $this->estado           = $estado;
        $this->subtitulo        = $subtitulo;
        $this->imagen           = $imagen;
        $this->idNovedad        = $idNovedad;
        $this->fechaPublicacion = $fechaPublicacion;
    }

    public function getIdNovedad(): ?int        { return $this->idNovedad; }
    public function getTitulo(): string          { return $this->titulo; }
    public function getSubtitulo(): ?string      { return $this->subtitulo; }
    public function getContenido(): string       { return $this->contenido; }
    public function getCategoria(): string       { return $this->categoria; }
    public function getPrioridad(): string       { return $this->prioridad; }
    public function getEstado(): string          { return $this->estado; }
    public function getImagen(): ?string         { return $this->imagen; }
    public function getFechaPublicacion(): ?string { return $this->fechaPublicacion; }

    public function setIdNovedad(?int $v): void        { $this->idNovedad = $v; }
    public function setTitulo(string $v): void          { $this->titulo = $v; }
    public function setSubtitulo(?string $v): void      { $this->subtitulo = $v; }
    public function setContenido(string $v): void       { $this->contenido = $v; }
    public function setCategoria(string $v): void       { $this->categoria = $v; }
    public function setPrioridad(string $v): void       { $this->prioridad = $v; }
    public function setEstado(string $v): void          { $this->estado = $v; }
    public function setImagen(?string $v): void         { $this->imagen = $v; }
    public function setFechaPublicacion(?string $v): void { $this->fechaPublicacion = $v; }
}