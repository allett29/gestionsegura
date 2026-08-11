<?php

namespace App\Servicios\Cotizacion;

use App\Enumeraciones\EstadoCotizacion;
use App\Excepciones\ExcepcionCotizacionNoContratable;
use App\Modelos\Cotizacion;
use Illuminate\Support\Facades\DB;

/**
 * Confirma la contratación de una cotización existente.
 */
class ServicioContratarCotizacion
{
    public function __construct(
        private readonly ServicioCotizacion $servicioCotizacion,
    ) {}

    /**
     * Contrata una cotización si está en estado Cotizado.
     */
    public function contratar(string $uuid): Cotizacion
    {
        return DB::transaction(function () use ($uuid): Cotizacion {
            /** @var Cotizacion $cotizacion */
            $cotizacion = Cotizacion::query()
                ->where('uuid', $uuid)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $cotizacion->puedeContratar()) {
                throw new ExcepcionCotizacionNoContratable(
                    'Solo se pueden contratar cotizaciones en estado Cotizado.',
                );
            }

            $cotizacion->estado = EstadoCotizacion::Contratado;
            $cotizacion->fecha_contratacion = now();
            $cotizacion->save();

            return $cotizacion->refresh();
        });
    }
}
