<?php

use App\Servicios\Cotizacion\ServicioCalculoCotizacion;
use App\Soporte\RecargoPorRegion;
use Carbon\Carbon;

/**
 * Pruebas unitarias del cálculo de cotización.
 */
describe('ServicioCalculoCotizacion', function () {
    it('calcula un viaje de un solo día', function () {
        $servicio = new ServicioCalculoCotizacion(new RecargoPorRegion());

        $resultado = $servicio->calcular(
            Carbon::parse('2026-04-01'),
            Carbon::parse('2026-04-01'),
            'South America',
        );

        expect($resultado->cantidadDias)->toBe(1)
            ->and($resultado->tarifaBase)->toBe(3.0)
            ->and($resultado->porcentajeRecargo)->toBe(0.0)
            ->and($resultado->valorTotal)->toBe(3.0);
    });

    it('calcula el ejemplo de España por 10 días (USD 36)', function () {
        $servicio = new ServicioCalculoCotizacion(new RecargoPorRegion());

        $resultado = $servicio->calcular(
            Carbon::parse('2026-05-01'),
            Carbon::parse('2026-05-10'),
            'Europe',
        );

        expect($resultado->cantidadDias)->toBe(10)
            ->and($resultado->tarifaBase)->toBe(30.0)
            ->and($resultado->porcentajeRecargo)->toBe(20.0)
            ->and($resultado->valorRecargo)->toBe(6.0)
            ->and($resultado->valorTotal)->toBe(36.0);
    });

    it('aplica recargos por región según la tabla de negocio', function (string $region, float $esperado) {
        $soporte = new RecargoPorRegion();
        expect($soporte->obtenerPorcentaje($region))->toBe($esperado);
    })->with([
        ['South America', 0.0],
        ['North America', 15.0],
        ['Europe', 20.0],
        ['Asia', 25.0],
        ['Africa', 20.0],
        ['Oceania', 25.0],
    ]);
});
