<?php

namespace App\Servicios\Cotizacion;

use App\Enumeraciones\EstadoCotizacion;
use App\Modelos\Cotizacion;
use App\Servicios\Pais\ClienteRestCountries;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

/**
 * Casos de uso de creación y consulta de cotizaciones.
 */
class ServicioCotizacion
{
    public function __construct(
        private readonly ClienteRestCountries $clientePaises,
        private readonly ServicioCalculoCotizacion $servicioCalculo,
    ) {}

    /**
     * Crea una cotización en estado Cotizado.
     *
     * @param  array<string, mixed>  $datos
     */
    public function crear(array $datos): Cotizacion
    {
        $pais = $this->clientePaises->buscarPorCodigoIso((string) $datos['codigo_iso_destino']);

        if ($pais === null) {
            throw ValidationException::withMessages([
                'codigo_iso_destino' => 'El país de destino seleccionado no es válido.',
            ]);
        }

        $calculo = $this->servicioCalculo->calcular(
            fechaSalida: \Carbon\Carbon::parse((string) $datos['fecha_salida']),
            fechaRegreso: \Carbon\Carbon::parse((string) $datos['fecha_regreso']),
            region: $pais->region,
        );

        return Cotizacion::query()->create([
            'nombres' => $datos['nombres'],
            'apellidos' => $datos['apellidos'],
            'numero_identificacion' => $datos['numero_identificacion'],
            'correo_electronico' => $datos['correo_electronico'],
            'fecha_nacimiento' => $datos['fecha_nacimiento'],
            'pais_destino' => $pais->nombre,
            'codigo_iso_destino' => $pais->codigoIso,
            'region_destino' => $calculo->regionAplicada,
            'fecha_salida' => $datos['fecha_salida'],
            'fecha_regreso' => $datos['fecha_regreso'],
            'cantidad_dias' => $calculo->cantidadDias,
            'tarifa_base' => $calculo->tarifaBase,
            'porcentaje_recargo' => $calculo->porcentajeRecargo,
            'valor_total' => $calculo->valorTotal,
            'moneda' => 'USD',
            'estado' => EstadoCotizacion::Cotizado,
            'fecha_contratacion' => null,
        ]);
    }

    /**
     * Obtiene una cotización por UUID público.
     */
    public function obtenerPorUuid(string $uuid): Cotizacion
    {
        return Cotizacion::query()->where('uuid', $uuid)->firstOrFail();
    }

    /**
     * Lista cotizaciones con búsqueda, filtro y paginación.
     *
     * @param  array{buscar?: string|null, estado?: string|null, por_pagina?: int|null}  $filtros
     * @return LengthAwarePaginator<int, Cotizacion>
     */
    public function listar(array $filtros = []): LengthAwarePaginator
    {
        $buscar = trim((string) Arr::get($filtros, 'buscar', ''));
        $estado = Arr::get($filtros, 'estado');
        $porPagina = (int) Arr::get($filtros, 'por_pagina', 10);

        $consulta = Cotizacion::query()->latest();

        if ($buscar !== '') {
            $consulta->where(function ($q) use ($buscar): void {
                $q->where('nombres', 'like', "%{$buscar}%")
                    ->orWhere('apellidos', 'like', "%{$buscar}%")
                    ->orWhere('numero_identificacion', 'like', "%{$buscar}%")
                    ->orWhere('pais_destino', 'like', "%{$buscar}%");
            });
        }

        if (is_string($estado) && $estado !== '') {
            $consulta->where('estado', $estado);
        }

        return $consulta->paginate(max(5, min($porPagina, 50)));
    }
}
