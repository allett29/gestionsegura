<?php

use App\Enumeraciones\EstadoCotizacion;
use App\Modelos\Cotizacion;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::fake([
        'restcountries.com/*' => Http::response([
            [
                'name' => ['common' => 'Spain'],
                'cca2' => 'ES',
                'region' => 'Europe',
                'subregion' => 'Southern Europe',
                'flags' => ['svg' => 'https://example.com/es.svg'],
            ],
            [
                'name' => ['common' => 'Ecuador'],
                'cca2' => 'EC',
                'region' => 'Americas',
                'subregion' => 'South America',
                'flags' => ['svg' => 'https://example.com/ec.svg'],
            ],
        ], 200),
    ]);
});

it('crea una cotización válida en estado cotizado', function () {
    $respuesta = $this->postJson('/api/cotizaciones', [
        'nombres' => 'Ana',
        'apellidos' => 'Pérez',
        'numero_identificacion' => '1712345678',
        'correo_electronico' => 'ana@example.com',
        'fecha_nacimiento' => '1995-01-15',
        'codigo_iso_destino' => 'ES',
        'fecha_salida' => now()->addDays(5)->toDateString(),
        'fecha_regreso' => now()->addDays(14)->toDateString(),
    ]);

    $respuesta->assertCreated()
        ->assertJsonPath('data.estado', EstadoCotizacion::Cotizado->value)
        ->assertJsonPath('data.cantidad_dias', 10)
        ->assertJsonPath('data.tarifa_base', 30)
        ->assertJsonPath('data.porcentaje_recargo', 20)
        ->assertJsonPath('data.valor_total', 36);

    $this->assertDatabaseHas('cotizaciones', [
        'correo_electronico' => 'ana@example.com',
        'estado' => 'cotizado',
    ]);
});

it('rechaza datos inválidos con 422', function () {
    $respuesta = $this->postJson('/api/cotizaciones', [
        'nombres' => '',
        'apellidos' => '',
        'numero_identificacion' => '',
        'correo_electronico' => 'no-valido',
        'fecha_nacimiento' => now()->toDateString(),
        'codigo_iso_destino' => 'X',
        'fecha_salida' => now()->subDay()->toDateString(),
        'fecha_regreso' => now()->subDays(2)->toDateString(),
    ]);

    $respuesta->assertStatus(422);
});

it('contrata una cotización y bloquea una segunda contratación', function () {
    $cotizacion = Cotizacion::factory()->create([
        'estado' => EstadoCotizacion::Cotizado,
        'fecha_contratacion' => null,
    ]);

    $this->postJson("/api/cotizaciones/{$cotizacion->uuid}/contratar")
        ->assertOk()
        ->assertJsonPath('data.estado', 'contratado');

    $this->assertNotNull($cotizacion->fresh()->fecha_contratacion);

    $this->postJson("/api/cotizaciones/{$cotizacion->uuid}/contratar")
        ->assertStatus(409);
});

it('lista cotizaciones con paginación', function () {
    Cotizacion::factory()->count(12)->create();

    $this->getJson('/api/cotizaciones?por_pagina=5')
        ->assertOk()
        ->assertJsonPath('meta.por_pagina', 5)
        ->assertJsonPath('meta.total', 12);
});

it('devuelve el PDF de una cotización', function () {
    $cotizacion = Cotizacion::factory()->create();

    $this->get("/api/cotizaciones/{$cotizacion->uuid}/pdf")
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});
