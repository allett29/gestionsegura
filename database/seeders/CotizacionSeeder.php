<?php

namespace Database\Seeders;

use App\Enumeraciones\EstadoCotizacion;
use App\Modelos\Cotizacion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Siembra cotizaciones de demostración sin depender de Faker.
 */
class CotizacionSeeder extends Seeder
{
    /**
     * Ejecuta el seeder.
     */
    public function run(): void
    {
        $demos = [
            [
                'nombres' => 'Ana',
                'apellidos' => 'Pérez',
                'numero_identificacion' => '1711111111',
                'correo_electronico' => 'ana.perez@example.com',
                'pais_destino' => 'Spain',
                'codigo_iso_destino' => 'ES',
                'region_destino' => 'Europe',
                'cantidad_dias' => 10,
                'porcentaje_recargo' => 20,
                'estado' => EstadoCotizacion::Cotizado,
            ],
            [
                'nombres' => 'Luis',
                'apellidos' => 'García',
                'numero_identificacion' => '1722222222',
                'correo_electronico' => 'luis.garcia@example.com',
                'pais_destino' => 'Ecuador',
                'codigo_iso_destino' => 'EC',
                'region_destino' => 'South America',
                'cantidad_dias' => 7,
                'porcentaje_recargo' => 0,
                'estado' => EstadoCotizacion::Contratado,
            ],
            [
                'nombres' => 'María',
                'apellidos' => 'Ruiz',
                'numero_identificacion' => '1733333333',
                'correo_electronico' => 'maria.ruiz@example.com',
                'pais_destino' => 'United States',
                'codigo_iso_destino' => 'US',
                'region_destino' => 'North America',
                'cantidad_dias' => 5,
                'porcentaje_recargo' => 15,
                'estado' => EstadoCotizacion::Cotizado,
            ],
            [
                'nombres' => 'Carlos',
                'apellidos' => 'Mena',
                'numero_identificacion' => '1744444444',
                'correo_electronico' => 'carlos.mena@example.com',
                'pais_destino' => 'Japan',
                'codigo_iso_destino' => 'JP',
                'region_destino' => 'Asia',
                'cantidad_dias' => 12,
                'porcentaje_recargo' => 25,
                'estado' => EstadoCotizacion::Contratado,
            ],
            [
                'nombres' => 'Elena',
                'apellidos' => 'Salazar',
                'numero_identificacion' => '1755555555',
                'correo_electronico' => 'elena.salazar@example.com',
                'pais_destino' => 'Australia',
                'codigo_iso_destino' => 'AU',
                'region_destino' => 'Oceania',
                'cantidad_dias' => 8,
                'porcentaje_recargo' => 25,
                'estado' => EstadoCotizacion::Cotizado,
            ],
        ];

        foreach ($demos as $indice => $demo) {
            $salida = now()->addDays(10 + $indice)->startOfDay();
            $regreso = $salida->copy()->addDays($demo['cantidad_dias'] - 1);
            $tarifaBase = $demo['cantidad_dias'] * 3;
            $valorTotal = round($tarifaBase + ($tarifaBase * $demo['porcentaje_recargo'] / 100), 2);
            $contratado = $demo['estado'] === EstadoCotizacion::Contratado;

            Cotizacion::query()->create([
                'uuid' => (string) Str::uuid(),
                'nombres' => $demo['nombres'],
                'apellidos' => $demo['apellidos'],
                'numero_identificacion' => $demo['numero_identificacion'],
                'correo_electronico' => $demo['correo_electronico'],
                'fecha_nacimiento' => '1990-05-15',
                'pais_destino' => $demo['pais_destino'],
                'codigo_iso_destino' => $demo['codigo_iso_destino'],
                'region_destino' => $demo['region_destino'],
                'fecha_salida' => $salida->toDateString(),
                'fecha_regreso' => $regreso->toDateString(),
                'cantidad_dias' => $demo['cantidad_dias'],
                'tarifa_base' => $tarifaBase,
                'porcentaje_recargo' => $demo['porcentaje_recargo'],
                'valor_total' => $valorTotal,
                'moneda' => 'USD',
                'estado' => $demo['estado'],
                'fecha_contratacion' => $contratado ? now() : null,
            ]);
        }
    }
}
