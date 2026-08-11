<?php

namespace App\Excepciones;

use Exception;
use Throwable;

/**
 * Error controlado al consultar el servicio externo de países.
 */
class ExcepcionServicioPaises extends Exception
{
    public function __construct(
        string $mensaje = 'No fue posible obtener la lista de países.',
        int $codigo = 503,
        ?Throwable $anterior = null,
    ) {
        parent::__construct($mensaje, $codigo, $anterior);
    }
}
