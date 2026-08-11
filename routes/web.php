<?php

use Illuminate\Support\Facades\Route;

/**
 * Rutas SPA de Vue (sin catch-all global para no interferir con /api ni /build).
 */
Route::view('/', 'aplicacion');
Route::view('/cotizaciones', 'aplicacion');
Route::view('/cotizaciones/{uuid}', 'aplicacion')->whereUuid('uuid');
