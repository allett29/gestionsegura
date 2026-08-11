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
 * Cliente resiliente para REST Countries.
 */
class ClienteRestCountries
{
    public function __construct(
        private readonly RecargoPorRegion $recargoPorRegion,
    ) {}

    /**
     * Obtiene la lista de países (caché → API → fallback).
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
                return $this->consultarApiExterna()
                    ->map(fn (DatosPais $pais) => $pais->aArreglo())
                    ->values()
                    ->all();
            });

            return collect($cacheados)->map(
                fn (array $item) => new DatosPais(
                    nombre: $item['nombre'],
                    codigoIso: $item['codigo_iso'],
                    region: $item['region'],
                    urlBandera: $item['url_bandera'] ?? null,
                )
            );
        } catch (Throwable $excepcion) {
            Log::warning('Fallo al obtener países; se intenta fallback.', [
                'error' => $excepcion->getMessage(),
            ]);

            $fallback = $this->obtenerFallback();

            if ($fallback->isNotEmpty()) {
                return $fallback;
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
     * Consulta REST Countries y valida la respuesta.
     *
     * @return Collection<int, DatosPais>
     */
    private function consultarApiExterna(): Collection
    {
        $urlBase = rtrim((string) config('seguro.paises.url'), '/');
        $campos = (string) config('seguro.paises.campos');
        $timeout = (int) config('seguro.paises.timeout_segundos', 5);

        try {
            $respuesta = Http::timeout($timeout)
                ->retry(1, 200)
                ->acceptJson()
                ->get($urlBase, ['fields' => $campos]);

            if (! $respuesta->successful()) {
                throw new ExcepcionServicioPaises(
                    'La API de países respondió con un estado inesperado.',
                    502,
                );
            }

            $cuerpo = $respuesta->json();

            if (! is_array($cuerpo)) {
                throw new ExcepcionServicioPaises(
                    'La API de países devolvió una respuesta inválida.',
                    502,
                );
            }

            return collect($cuerpo)
                ->map(fn ($item) => $this->mapearPais($item))
                ->filter()
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
     * Mapea un ítem crudo de la API a DatosPais.
     */
    private function mapearPais(mixed $item): ?DatosPais
    {
        if (! is_array($item)) {
            return null;
        }

        $nombre = data_get($item, 'name.common');
        $codigoIso = data_get($item, 'cca2');
        // Preferimos subregión (South America / North America) sobre "Americas".
        $regionCruda = data_get($item, 'subregion') ?: data_get($item, 'region');
        $bandera = data_get($item, 'flags.svg') ?? data_get($item, 'flags.png');

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
     * Lista mínima para disponibilidad temporal del API.
     *
     * @return Collection<int, DatosPais>
     */
    private function obtenerFallback(): Collection
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
