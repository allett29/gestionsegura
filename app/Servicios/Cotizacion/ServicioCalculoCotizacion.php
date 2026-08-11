<?php

namespace App\Servicios\Cotizacion;

use App\Soporte\RecargoPorRegion;
use Carbon\CarbonInterface;
use InvalidArgumentException;

/**
 * Calcula tarifa base, recargo y total del seguro de viaje.
 */
class ServicioCalculoCotizacion
{
    public function __construct(
        private readonly RecargoPorRegion $recargoPorRegion,
    ) {}

    /**
     * Calcula el precio del seguro a partir de fechas y región.
     */
    public function calcular(
        CarbonInterface $fechaSalida,
        CarbonInterface $fechaRegreso,
        ?string $region,
    ): ResultadoCalculoCotizacion {
        $salida = $fechaSalida->copy()->startOfDay();
        $regreso = $fechaRegreso->copy()->startOfDay();

        if ($regreso->lt($salida)) {
            throw new InvalidArgumentException('La fecha de regreso no puede ser anterior a la fecha de salida.');
        }

        $cantidadDias = $salida->diffInDays($regreso) + 1;
        $diasMaximos = (int) config('seguro.dias_maximos_viaje', 180);

        if ($cantidadDias > $diasMaximos) {
            throw new InvalidArgumentException("El viaje no puede superar {$diasMaximos} días.");
        }

        $tarifaDiaria = (float) config('seguro.tarifa_base_diaria', 3);
        $tarifaBase = round($cantidadDias * $tarifaDiaria, 2);
        $regionAplicada = $this->recargoPorRegion->normalizarRegion($region) ?? 'Desconocida';
        $porcentajeRecargo = $this->recargoPorRegion->obtenerPorcentaje($region);
        $valorRecargo = round($tarifaBase * ($porcentajeRecargo / 100), 2);
        $valorTotal = round($tarifaBase + $valorRecargo, 2);

        return new ResultadoCalculoCotizacion(
            cantidadDias: $cantidadDias,
            tarifaBase: $tarifaBase,
            porcentajeRecargo: $porcentajeRecargo,
            valorRecargo: $valorRecargo,
            valorTotal: $valorTotal,
            regionAplicada: $regionAplicada,
        );
    }
}
