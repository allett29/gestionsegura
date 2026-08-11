<?php

namespace App\Http\Peticiones;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validación para crear una cotización.
 */
class GuardarCotizacionPeticion extends FormRequest
{
    /**
     * Autoriza la petición.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $edadMinima = (int) config('seguro.edad_minima', 18);
        $fechaMaximaNacimiento = now()->subYears($edadMinima)->toDateString();

        return [
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'numero_identificacion' => ['required', 'string', 'max:30', 'regex:/^[A-Za-z0-9\-]+$/'],
            'correo_electronico' => ['required', 'email:rfc', 'max:150'],
            'fecha_nacimiento' => [
                'required',
                'date',
                'before:today',
                'before_or_equal:'.$fechaMaximaNacimiento,
            ],
            'codigo_iso_destino' => ['required', 'string', 'size:2'],
            'fecha_salida' => ['required', 'date', 'after_or_equal:today'],
            'fecha_regreso' => [
                'required',
                'date',
                'after_or_equal:fecha_salida',
                function (string $atributo, mixed $valor, \Closure $fallar): void {
                    $salida = $this->input('fecha_salida');
                    if (! is_string($salida) || ! is_string($valor)) {
                        return;
                    }

                    $dias = \Carbon\Carbon::parse($salida)->startOfDay()
                        ->diffInDays(\Carbon\Carbon::parse($valor)->startOfDay()) + 1;

                    $maximo = (int) config('seguro.dias_maximos_viaje', 180);
                    if ($dias > $maximo) {
                        $fallar("El viaje no puede superar {$maximo} días.");
                    }
                },
            ],
        ];
    }

    /**
     * Mensajes en español.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nombres.required' => 'Los nombres son obligatorios.',
            'apellidos.required' => 'Los apellidos son obligatorios.',
            'numero_identificacion.required' => 'El número de identificación es obligatorio.',
            'numero_identificacion.regex' => 'La identificación solo admite letras, números y guiones.',
            'correo_electronico.required' => 'El correo electrónico es obligatorio.',
            'correo_electronico.email' => 'El correo electrónico no es válido.',
            'fecha_nacimiento.required' => 'La fecha de nacimiento es obligatoria.',
            'fecha_nacimiento.before_or_equal' => 'El asegurado debe ser mayor de edad.',
            'codigo_iso_destino.required' => 'Debe seleccionar un país de destino.',
            'codigo_iso_destino.size' => 'El código de país no es válido.',
            'fecha_salida.required' => 'La fecha de salida es obligatoria.',
            'fecha_salida.after_or_equal' => 'La fecha de salida no puede ser anterior a hoy.',
            'fecha_regreso.required' => 'La fecha de regreso es obligatoria.',
            'fecha_regreso.after_or_equal' => 'La fecha de regreso debe ser igual o posterior a la salida.',
        ];
    }

    /**
     * Nombres de atributos en español.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nombres' => 'nombres',
            'apellidos' => 'apellidos',
            'numero_identificacion' => 'número de identificación',
            'correo_electronico' => 'correo electrónico',
            'fecha_nacimiento' => 'fecha de nacimiento',
            'codigo_iso_destino' => 'país de destino',
            'fecha_salida' => 'fecha de salida',
            'fecha_regreso' => 'fecha de regreso',
        ];
    }
}
