<?php

namespace App\Servicios\Pais;

use App\Excepciones\ExcepcionServicioPaises;
use App\Soporte\RecargoPorRegion;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Cliente resiliente para REST Countries (v5 con API key; respaldo local si falla).
 */
class ClienteRestCountries
{
    public function __construct(
        private readonly RecargoPorRegion $recargoPorRegion,
    ) {}

    /**
     * Obtiene la lista de países (caché → API v5 → archivo local).
     *
     * @return Collection<int, DatosPais>
     */
    public function obtenerPaises(): Collection
    {
        $claveCache = (string) config('seguro.paises.clave_cache');
        $ttl = (int) config('seguro.paises.cache_segundos', 86400);

        try {
            /** @var array<int, array{nombre: string, codigo_iso: string, region: string, url_bandera: string|null}> $cacheados */
            $cacheados = Cache::remember($claveCache, $ttl, function (): array {
                $paises = $this->consultarApiExterna();

                if ($paises->isEmpty()) {
                    throw new ExcepcionServicioPaises(
                        'La API de países no devolvió resultados.',
                        502,
                    );
                }

                return $paises
                    ->map(fn (DatosPais $pais) => $pais->aArreglo())
                    ->values()
                    ->all();
            });

            return $this->desdeArreglos($cacheados);
        } catch (Throwable $excepcion) {
            Log::warning('Fallo al obtener países; se usa respaldo local.', [
                'error' => $excepcion->getMessage(),
            ]);

            $respaldo = $this->obtenerDesdeArchivoRespaldo();

            if ($respaldo->isNotEmpty()) {
                return $respaldo;
            }

            throw new ExcepcionServicioPaises(
                'El servicio de países no está disponible temporalmente.',
                503,
                $excepcion,
            );
        }
    }

    /**
     * Busca un país por código ISO.
     */
    public function buscarPorCodigoIso(string $codigoIso): ?DatosPais
    {
        $codigo = strtoupper(trim($codigoIso));

        return $this->obtenerPaises()->first(
            fn (DatosPais $pais) => strtoupper($pais->codigoIso) === $codigo
        );
    }

    /**
     * Consulta REST Countries v5 (requiere API key) y valida la respuesta.
     *
     * @return Collection<int, DatosPais>
     */
    private function consultarApiExterna(): Collection
    {
        $apiKey = trim((string) config('seguro.paises.api_key'));

        if ($apiKey === '') {
            throw new ExcepcionServicioPaises(
                'REST Countries v5 requiere REST_COUNTRIES_API_KEY.',
                503,
            );
        }

        $urlBase = rtrim((string) config('seguro.paises.url'), '/');
        $campos = (string) config('seguro.paises.campos');
        $timeout = (int) config('seguro.paises.timeout_segundos', 5);
        $limite = (int) config('seguro.paises.limite_pagina', 100);

        try {
            $paises = collect();
            $offset = 0;
            $total = null;

            do {
                $respuesta = Http::timeout($timeout)
                    ->retry(1, 200)
                    ->acceptJson()
                    ->withToken($apiKey)
                    ->get($urlBase, [
                        'limit' => $limite,
                        'offset' => $offset,
                        'response_fields' => $campos,
                    ]);

                if (! $respuesta->successful()) {
                    throw new ExcepcionServicioPaises(
                        'La API de países respondió con un estado inesperado.',
                        502,
                    );
                }

                $cuerpo = $respuesta->json();

                if (! is_array($cuerpo) || data_get($cuerpo, 'success') === false) {
                    throw new ExcepcionServicioPaises(
                        'La API de países devolvió una respuesta inválida o deprecada.',
                        502,
                    );
                }

                $objetos = data_get($cuerpo, 'data.objects', []);

                if (! is_array($objetos)) {
                    throw new ExcepcionServicioPaises(
                        'La API de países devolvió una respuesta inválida.',
                        502,
                    );
                }

                $total ??= (int) data_get($cuerpo, 'data.meta.total', count($objetos));

                $paises = $paises->merge(
                    collect($objetos)
                        ->map(fn ($item) => $this->mapearPaisV5($item))
                        ->filter()
                );

                $offset += $limite;
            } while ($offset < $total);

            return $paises
                ->sortBy(fn (DatosPais $pais) => $pais->nombre)
                ->values();
        } catch (ConnectionException $excepcion) {
            throw new ExcepcionServicioPaises(
                'Tiempo de espera agotado al consultar países.',
                503,
                $excepcion,
            );
        } catch (RequestException $excepcion) {
            throw new ExcepcionServicioPaises(
                'Error de red al consultar países.',
                503,
                $excepcion,
            );
        }
    }

    /**
     * Mapea un ítem v5 de REST Countries a DatosPais.
     */
    private function mapearPaisV5(mixed $item): ?DatosPais
    {
        if (! is_array($item)) {
            return null;
        }

        $nombre = data_get($item, 'names.common');
        $codigoIso = data_get($item, 'codes.alpha_2');
        $regionCruda = data_get($item, 'subregion') ?: data_get($item, 'region');
        $bandera = data_get($item, 'flag.url_svg') ?? data_get($item, 'flag.url_png');

        if (! is_string($nombre) || $nombre === '' || ! is_string($codigoIso) || strlen($codigoIso) !== 2) {
            return null;
        }

        $region = $this->recargoPorRegion->normalizarRegion(
            is_string($regionCruda) ? $regionCruda : null
        ) ?? 'Desconocida';

        return new DatosPais(
            nombre: $nombre,
            codigoIso: strtoupper($codigoIso),
            region: $region,
            urlBandera: is_string($bandera) ? $bandera : null,
        );
    }

    /**
     * Carga países desde el archivo local de respaldo (ISO 3166).
     *
     * @return Collection<int, DatosPais>
     */
    private function obtenerDesdeArchivoRespaldo(): Collection
    {
        $ruta = resource_path('data/paises-respaldo.json');

        if (! is_readable($ruta)) {
            return $this->obtenerFallbackMinimo();
        }

        $contenido = file_get_contents($ruta);
        $datos = json_decode($contenido ?: '[]', true);

        if (! is_array($datos)) {
            return $this->obtenerFallbackMinimo();
        }

        return $this->desdeArreglos($datos);
    }

    /**
     * @param  array<int, array{nombre?: string, codigo_iso?: string, region?: string, url_bandera?: string|null}>  $items
     * @return Collection<int, DatosPais>
     */
    private function desdeArreglos(array $items): Collection
    {
        return collect($items)
            ->map(function (array $item): ?DatosPais {
                $nombre = $item['nombre'] ?? null;
                $codigoIso = $item['codigo_iso'] ?? null;
                $regionCruda = $item['region'] ?? null;
                $bandera = $item['url_bandera'] ?? null;

                if (! is_string($nombre) || $nombre === '' || ! is_string($codigoIso) || strlen($codigoIso) !== 2) {
                    return null;
                }

                $region = $this->recargoPorRegion->normalizarRegion(
                    is_string($regionCruda) ? $regionCruda : null
                ) ?? 'Desconocida';

                return new DatosPais(
                    nombre: $nombre,
                    codigoIso: strtoupper($codigoIso),
                    region: $region,
                    urlBandera: is_string($bandera) ? $bandera : null,
                );
            })
            ->filter()
            ->sortBy(fn (DatosPais $pais) => $pais->nombre)
            ->values();
    }

    /**
     * Lista mínima si también falla el archivo local.
     *
     * @return Collection<int, DatosPais>
     */
    private function obtenerFallbackMinimo(): Collection
    {
        return collect([
            new DatosPais('Ecuador', 'EC', 'South America'),
            new DatosPais('Spain', 'ES', 'Europe'),
            new DatosPais('United States', 'US', 'North America'),
            new DatosPais('Japan', 'JP', 'Asia'),
            new DatosPais('South Africa', 'ZA', 'Africa'),
            new DatosPais('Australia', 'AU', 'Oceania'),
        ]);
    }
}
