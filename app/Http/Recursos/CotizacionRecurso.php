<?php

namespace App\Http\Recursos;

use App\Modelos\Cotizacion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Recurso JSON de una cotización.
 *
 * @mixin Cotizacion
 */
class CotizacionRecurso extends JsonResource
{
    /**
     * Transforma el recurso a arreglo.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'nombres' => $this->nombres,
            'apellidos' => $this->apellidos,
            'nombre_completo' => $this->nombre_completo,
            'numero_identificacion' => $this->numero_identificacion,
            'correo_electronico' => $this->correo_electronico,
            'fecha_nacimiento' => $this->fecha_nacimiento?->toDateString(),
            'pais_destino' => $this->pais_destino,
            'codigo_iso_destino' => $this->codigo_iso_destino,
            'region_destino' => $this->region_destino,
            'fecha_salida' => $this->fecha_salida?->toDateString(),
            'fecha_regreso' => $this->fecha_regreso?->toDateString(),
            'cantidad_dias' => $this->cantidad_dias,
            'tarifa_base' => (float) $this->tarifa_base,
            'porcentaje_recargo' => (float) $this->porcentaje_recargo,
            'valor_total' => (float) $this->valor_total,
            'moneda' => $this->moneda,
            'estado' => $this->estado?->value,
            'estado_etiqueta' => $this->estado?->etiqueta(),
            'fecha_contratacion' => $this->fecha_contratacion?->toIso8601String(),
            'fecha_creacion' => $this->created_at?->toIso8601String(),
            'puede_contratar' => $this->puedeContratar(),
        ];
    }
}
