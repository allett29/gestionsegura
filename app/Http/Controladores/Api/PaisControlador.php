<?php

namespace App\Http\Controladores\Api;

use App\Excepciones\ExcepcionServicioPaises;
use App\Http\Controllers\Controller;
use App\Servicios\Pais\ClienteRestCountries;
use App\Servicios\Pais\DatosPais;
use Illuminate\Http\JsonResponse;

/**
 * Endpoints de países.
 */
class PaisControlador extends Controller
{
    public function __construct(
        private readonly ClienteRestCountries $clientePaises,
    ) {}

    /**
     * Lista países disponibles para cotizar.
     */
    public function index(): JsonResponse
    {
        try {
            $paises = $this->clientePaises->obtenerPaises()
                ->map(fn (DatosPais $pais) => $pais->aArreglo())
                ->values();

            return response()->json([
                'mensaje' => 'Países obtenidos correctamente.',
                'data' => $paises,
            ]);
        } catch (ExcepcionServicioPaises $excepcion) {
            return response()->json([
                'mensaje' => $excepcion->getMessage(),
                'data' => [],
            ], $excepcion->getCode() ?: 503);
        }
    }
}
