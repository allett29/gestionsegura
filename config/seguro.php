<?php

/**
 * Configuración de negocio del seguro de viaje.
 */
return [

    /*
    |--------------------------------------------------------------------------
    | Tarifa base por día (USD)
    |--------------------------------------------------------------------------
    */
    'tarifa_base_diaria' => (float) env('SEGURO_TARIFA_BASE_DIARIA', 3),

    /*
    |--------------------------------------------------------------------------
    | Recargos por región (%)
    |--------------------------------------------------------------------------
    */
    'recargos_por_region' => [
        'South America' => 0,
        'North America' => 15,
        'Europe' => 20,
        'Asia' => 25,
        'Africa' => 20,
        'Oceania' => 25,
    ],

    /*
    |--------------------------------------------------------------------------
    | Recargo por defecto si la región no está mapeada
    |--------------------------------------------------------------------------
    */
    'recargo_region_desconocida' => (float) env('SEGURO_RECARGO_DESCONOCIDA', 25),

    /*
    |--------------------------------------------------------------------------
    | Reglas de validación de negocio
    |--------------------------------------------------------------------------
    */
    'edad_minima' => (int) env('SEGURO_EDAD_MINIMA', 18),
    'dias_maximos_viaje' => (int) env('SEGURO_DIAS_MAXIMOS', 180),

    /*
    |--------------------------------------------------------------------------
    | Integración REST Countries
    |--------------------------------------------------------------------------
    */
    'paises' => [
        'url' => env('REST_COUNTRIES_URL', 'https://api.restcountries.com/countries/v5'),
        'api_key' => env('REST_COUNTRIES_API_KEY', ''),
        'campos' => 'names.common,codes.alpha_2,region,subregion,flag.url_svg',
        'limite_pagina' => (int) env('REST_COUNTRIES_PAGE_LIMIT', 100),
        'timeout_segundos' => (int) env('REST_COUNTRIES_TIMEOUT', 5),
        'cache_segundos' => (int) env('REST_COUNTRIES_CACHE', 86400),
        'clave_cache' => 'paises.todos.v2',
    ],
];
