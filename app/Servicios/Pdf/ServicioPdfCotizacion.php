<?php

namespace App\Servicios\Pdf;

use App\Modelos\Cotizacion;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Genera el PDF de una cotización.
 */
class ServicioPdfCotizacion
{
    /**
     * Descarga el PDF de la cotización.
     */
    public function descargar(Cotizacion $cotizacion): SymfonyResponse
    {
        $pdf = Pdf::loadView('pdf.cotizacion', [
            'cotizacion' => $cotizacion,
        ])->setPaper('a4');

        $nombreArchivo = "cotizacion-{$cotizacion->uuid}.pdf";

        return $pdf->download($nombreArchivo);
    }
}
