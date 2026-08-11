<?php

namespace App\Http\Peticiones;

use App\Enumeraciones\EstadoCotizacion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validación de filtros para el listado de cotizaciones.
 */
class ListarCotizacionesPeticion extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'buscar' => ['nullable', 'string', 'max:100'],
            'estado' => ['nullable', 'string', Rule::in(array_column(EstadoCotizacion::cases(), 'value'))],
            'por_pagina' => ['nullable', 'integer', 'min:5', 'max:50'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
