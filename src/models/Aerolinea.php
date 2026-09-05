<?php 

class Aerolinea {

    private ?int $idAerolinea;
    private string $nombreAerolinea;
    private string $codigoIATA;
    private ?string $descripcion;
    private string $codPais;
    private string $email;
    private ?string $logoUrl;
    private bool $activo;
    private ?string $logoPublicId;

    public function __construct(string $nombreAerolinea, string $codigoIATA, ?string $descripcion, string $codPais, string $email, ?string $logoUrl, bool $activo, ?int $idAerolinea = null, ?string $logoPublicId = null) {
        $this->nombreAerolinea = $nombreAerolinea;
        $this->codigoIATA = $codigoIATA;
        $this->descripcion = $descripcion;
        $this->codPais = $codPais;
        $this->email = $email;
        $this->logoUrl = $logoUrl;
        $this->activo = $activo;
        $this->idAerolinea = $idAerolinea;
        $this->logoPublicId = $logoPublicId;
    }


    public function getIdAerolinea(): ?int
    {
        return $this->idAerolinea;
    }

    public function getNombreAerolinea(): string
    {
        return $this->nombreAerolinea;
    }

    public function getCodigoIATA(): string
    {
        return $this->codigoIATA;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function getCodPais(): string
    {
        return $this->codPais;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getLogoUrl(): ?string
    {
        return $this->logoUrl;
    }

    public function getActivo(): bool
    {
        return $this->activo;
    }

    public function getLogoPublicId(): ?string
    {
        return $this->logoPublicId;
    }


    public function setIdAerolinea(?int $idAerolinea): void
    {
        $this->idAerolinea = $idAerolinea;
    }

    public function setNombreAerolinea(string $nombreAerolinea): void
    {
        $this->nombreAerolinea = $nombreAerolinea;
    }

    public function setCodigoIATA(string $codigoIATA): void
    {
        $this->codigoIATA = $codigoIATA;
    }

    public function setDescripcion(?string $descripcion): void
    {
        $this->descripcion = $descripcion;
    }

    public function setCodPais(string $codPais): void
    {
        $this->codPais = $codPais;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function setLogoUrl(?string $logoUrl): void
    {
        $this->logoUrl = $logoUrl;
    }

    public function setActivo(bool $activo): void
    {
        $this->activo = $activo;
    }

    public function setLogoPublicId(?string $logoPublicId): void
    {
        $this->logoPublicId = $logoPublicId;
    }



}





?>