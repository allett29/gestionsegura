<?php

namespace App\Excepciones;

use Exception;
use Throwable;

/**
 * Se lanza cuando una cotización no puede pasar a estado contratado.
 */
class ExcepcionCotizacionNoContratable extends Exception
{
    public function __construct(
        string $mensaje = 'La cotización no puede ser contratada.',
        int $codigo = 409,
        ?Throwable $anterior = null,
    ) {
        parent::__construct($mensaje, $codigo, $anterior);
    }
}
