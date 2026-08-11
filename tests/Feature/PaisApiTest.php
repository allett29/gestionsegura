<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

it('lista países desde la API externa', function () {
    Http::fake([
        'restcountries.com/*' => Http::response([
            [
                'name' => ['common' => 'Spain'],
                'cca2' => 'ES',
                'region' => 'Europe',
                'subregion' => 'Southern Europe',
                'flags' => ['svg' => 'https://example.com/es.svg'],
            ],
        ], 200),
    ]);

    Cache::flush();

    $this->getJson('/api/paises')
        ->assertOk()
        ->assertJsonPath('data.0.codigo_iso', 'ES')
        ->assertJsonPath('data.0.region', 'Europe');
});

it('usa fallback cuando la API externa falla', function () {
    Http::fake([
        'restcountries.com/*' => Http::response('error', 500),
    ]);

    Cache::flush();

    $this->getJson('/api/paises')
        ->assertOk()
        ->assertJsonStructure(['data' => [['nombre', 'codigo_iso', 'region']]]);
});
