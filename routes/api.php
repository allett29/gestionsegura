<?php

use App\Http\Controladores\Api\CotizacionControlador;
use App\Http\Controladores\Api\PaisControlador;
use Illuminate\Support\Facades\Route;

/**
 * Rutas de la API del seguro de viaje.
 */
Route::get('/paises', [PaisControlador::class, 'index']);

Route::get('/cotizaciones', [CotizacionControlador::class, 'index']);
Route::post('/cotizaciones', [CotizacionControlador::class, 'store']);
Route::get('/cotizaciones/{uuid}', [CotizacionControlador::class, 'show']);
Route::post('/cotizaciones/{uuid}/contratar', [CotizacionControlador::class, 'contratar']);
Route::get('/cotizaciones/{uuid}/pdf', [CotizacionControlador::class, 'pdf']);
