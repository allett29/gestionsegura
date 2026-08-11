<?php

namespace App\Http\Controladores\Api;

use App\Excepciones\ExcepcionCotizacionNoContratable;
use App\Http\Controllers\Controller;
use App\Http\Peticiones\GuardarCotizacionPeticion;
use App\Http\Peticiones\ListarCotizacionesPeticion;
use App\Http\Recursos\CotizacionRecurso;
use App\Servicios\Cotizacion\ServicioContratarCotizacion;
use App\Servicios\Cotizacion\ServicioCotizacion;
use App\Servicios\Pdf\ServicioPdfCotizacion;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Endpoints de cotización y contratación.
 */
class CotizacionControlador extends Controller
{
    public function __construct(
        private readonly ServicioCotizacion $servicioCotizacion,
        private readonly ServicioContratarCotizacion $servicioContratar,
        private readonly ServicioPdfCotizacion $servicioPdf,
    ) {}

    /**
     * Lista cotizaciones con filtros.
     */
    public function index(ListarCotizacionesPeticion $peticion): JsonResponse
    {
        $paginado = $this->servicioCotizacion->listar($peticion->validated());

        return response()->json([
            'mensaje' => 'Cotizaciones obtenidas correctamente.',
            'data' => CotizacionRecurso::collection($paginado->items()),
            'meta' => [
                'actual' => $paginado->currentPage(),
                'ultima' => $paginado->lastPage(),
                'por_pagina' => $paginado->perPage(),
                'total' => $paginado->total(),
            ],
        ]);
    }

    /**
     * Crea una nueva cotización.
     */
    public function store(GuardarCotizacionPeticion $peticion): JsonResponse
    {
        $cotizacion = $this->servicioCotizacion->crear($peticion->validated());

        return response()->json([
            'mensaje' => 'Cotización generada correctamente.',
            'data' => new CotizacionRecurso($cotizacion),
        ], 201);
    }

    /**
     * Muestra el detalle de una cotización.
     */
    public function show(string $uuid): JsonResponse
    {
        $cotizacion = $this->servicioCotizacion->obtenerPorUuid($uuid);

        return response()->json([
            'mensaje' => 'Cotización encontrada.',
            'data' => new CotizacionRecurso($cotizacion),
        ]);
    }

    /**
     * Confirma la contratación.
     */
    public function contratar(string $uuid): JsonResponse
    {
        try {
            $cotizacion = $this->servicioContratar->contratar($uuid);

            return response()->json([
                'mensaje' => 'Seguro contratado correctamente.',
                'data' => new CotizacionRecurso($cotizacion),
            ]);
        } catch (ExcepcionCotizacionNoContratable $excepcion) {
            return response()->json([
                'mensaje' => $excepcion->getMessage(),
                'data' => null,
            ], 409);
        }
    }

    /**
     * Descarga el PDF de la cotización.
     */
    public function pdf(string $uuid): Response
    {
        $cotizacion = $this->servicioCotizacion->obtenerPorUuid($uuid);

        return $this->servicioPdf->descargar($cotizacion);
    }
}
