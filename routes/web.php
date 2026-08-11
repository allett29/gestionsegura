<?php

use Illuminate\Support\Facades\Route;

/**
 * La SPA Vue se sirve desde una única vista Blade.
 */
Route::view('/{any?}', 'aplicacion')->where('any', '.*');
