<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

it('lista países desde la API externa v5', function () {
    config([
        'seguro.paises.api_key' => 'test-key',
        'seguro.paises.clave_cache' => 'paises.test.v5',
    ]);

    Http::fake([
        'api.restcountries.com/*' => Http::response([
            'data' => [
                'objects' => [[
                    'names' => ['common' => 'Spain'],
                    'codes' => ['alpha_2' => 'ES'],
                    'region' => 'Europe',
                    'subregion' => 'Southern Europe',
                    'flag' => ['url_svg' => 'https://example.com/es.svg'],
                ]],
                'meta' => ['total' => 1],
            ],
        ], 200),
    ]);

    Cache::flush();

    $this->getJson('/api/paises')
        ->assertOk()
        ->assertJsonPath('data.0.codigo_iso', 'ES')
        ->assertJsonPath('data.0.region', 'Europe');
});

it('responde error cuando la API externa falla', function () {
    config([
        'seguro.paises.api_key' => 'test-key',
        'seguro.paises.clave_cache' => 'paises.test.error',
    ]);

    Http::fake([
        'api.restcountries.com/*' => Http::response(['success' => false], 500),
    ]);

    Cache::flush();

    $this->getJson('/api/paises')
        ->assertStatus(502)
        ->assertJsonPath('data', []);
});

it('responde error sin API key configurada', function () {
    config([
        'seguro.paises.api_key' => '',
        'seguro.paises.clave_cache' => 'paises.test.sin-key',
    ]);

    Cache::flush();

    $this->getJson('/api/paises')
        ->assertStatus(503)
        ->assertJsonPath('mensaje', 'REST Countries v5 requiere REST_COUNTRIES_API_KEY.');
});
