<?php

namespace App\Servicios\Pais;

/**
 * DTO interno con los datos de un país provenientes de la API externa.
 */
class DatosPais
{
    public function __construct(
        public readonly string $nombre,
        public readonly string $codigoIso,
        public readonly string $region,
        public readonly ?string $urlBandera = null,
    ) {}

    /**
     * Convierte el DTO a arreglo para respuestas JSON.
     *
     * @return array{nombre: string, codigo_iso: string, region: string, url_bandera: string|null}
     */
    public function aArreglo(): array
    {
        return [
            'nombre' => $this->nombre,
            'codigo_iso' => $this->codigoIso,
            'region' => $this->region,
            'url_bandera' => $this->urlBandera,
        ];
    }
}
