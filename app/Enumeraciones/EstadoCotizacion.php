<?php

namespace App\Enumeraciones;

/**
 * Estados mínimos de una cotización/seguro.
 */
enum EstadoCotizacion: string
{
    case Cotizado = 'cotizado';
    case Contratado = 'contratado';

    /**
     * Etiqueta legible para la interfaz.
     */
    public function etiqueta(): string
    {
        return match ($this) {
            self::Cotizado => 'Cotizado',
            self::Contratado => 'Contratado',
        };
    }
}
