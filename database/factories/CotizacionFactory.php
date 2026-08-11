<?php

namespace Database\Factories;

use App\Enumeraciones\EstadoCotizacion;
use App\Modelos\Cotizacion;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Factory de cotizaciones para pruebas y seeders.
 *
 * @extends Factory<Cotizacion>
 */
class CotizacionFactory extends Factory
{
    protected $model = Cotizacion::class;

    /**
     * Estado por defecto del modelo.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        /** @var Generator $faker */
        $faker = $this->faker;

        $fechaSalida = now()->addDays($faker->numberBetween(1, 30))->startOfDay();
        $cantidadDias = $faker->numberBetween(1, 15);
        $fechaRegreso = (clone $fechaSalida)->addDays($cantidadDias - 1);

        $destinos = [
            ['Ecuador', 'EC', 'South America', 0],
            ['Spain', 'ES', 'Europe', 20],
            ['United States', 'US', 'North America', 15],
            ['Japan', 'JP', 'Asia', 25],
            ['South Africa', 'ZA', 'Africa', 20],
            ['Australia', 'AU', 'Oceania', 25],
        ];

        [$pais, $iso, $region, $recargo] = $faker->randomElement($destinos);
        $tarifaBase = $cantidadDias * 3;
        $valorTotal = round($tarifaBase + ($tarifaBase * $recargo / 100), 2);

        return [
            'uuid' => (string) Str::uuid(),
            'nombres' => $faker->firstName(),
            'apellidos' => $faker->lastName(),
            'numero_identificacion' => $faker->numerify('##########'),
            'correo_electronico' => $faker->unique()->safeEmail(),
            'fecha_nacimiento' => $faker->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
            'pais_destino' => $pais,
            'codigo_iso_destino' => $iso,
            'region_destino' => $region,
            'fecha_salida' => $fechaSalida->toDateString(),
            'fecha_regreso' => $fechaRegreso->toDateString(),
            'cantidad_dias' => $cantidadDias,
            'tarifa_base' => $tarifaBase,
            'porcentaje_recargo' => $recargo,
            'valor_total' => $valorTotal,
            'moneda' => 'USD',
            'estado' => EstadoCotizacion::Cotizado,
            'fecha_contratacion' => null,
        ];
    }

    /**
     * Estado contratado.
     */
    public function contratada(): static
    {
        return $this->state(fn () => [
            'estado' => EstadoCotizacion::Contratado,
            'fecha_contratacion' => now(),
        ]);
    }
}
