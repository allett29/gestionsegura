<?php

namespace App\Servicios\Cotizacion;

/**
 * Resultado del cálculo de una cotización.
 */
class ResultadoCalculoCotizacion
{
    public function __construct(
        public readonly int $cantidadDias,
        public readonly float $tarifaBase,
        public readonly float $porcentajeRecargo,
        public readonly float $valorRecargo,
        public readonly float $valorTotal,
        public readonly string $regionAplicada,
    ) {}
}
