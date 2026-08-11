<?php

namespace App\Soporte;

/**
 * Resuelve el porcentaje de recargo según la región del destino.
 */
class RecargoPorRegion
{
    /**
     * Obtiene el porcentaje de recargo para una región.
     */
    public function obtenerPorcentaje(?string $region): float
    {
        $recargos = config('seguro.recargos_por_region', []);
        $regionNormalizada = $this->normalizarRegion($region);

        if ($regionNormalizada !== null && array_key_exists($regionNormalizada, $recargos)) {
            return (float) $recargos[$regionNormalizada];
        }

        return (float) config('seguro.recargo_region_desconocida', 25);
    }

    /**
     * Normaliza alias comunes de regiones hacia las claves del enunciado.
     */
    public function normalizarRegion(?string $region): ?string
    {
        if ($region === null || trim($region) === '') {
            return null;
        }

        $mapa = [
            'south america' => 'South America',
            'north america' => 'North America',
            'central america' => 'North America',
            'caribbean' => 'North America',
            'europe' => 'Europe',
            'asia' => 'Asia',
            'africa' => 'Africa',
            'oceania' => 'Oceania',
            'australia and new zealand' => 'Oceania',
            'antarctic' => 'Oceania',
            // "Americas" sin subregión: se trata como desconocida → recargo por defecto
        ];

        $clave = strtolower(trim($region));

        if (array_key_exists($clave, $mapa)) {
            return $mapa[$clave];
        }

        // Si ya viene exactamente como en la tabla de negocio, se respeta.
        $valoresValidos = array_keys(config('seguro.recargos_por_region', []));
        foreach ($valoresValidos as $valorValido) {
            if (strcasecmp($valorValido, $region) === 0) {
                return $valorValido;
            }
        }

        // Subregiones europeas/asiáticas/etc. se mapean por continente padre en el cliente.
        if (str_contains($clave, 'europe')) {
            return 'Europe';
        }
        if (str_contains($clave, 'asia')) {
            return 'Asia';
        }
        if (str_contains($clave, 'africa')) {
            return 'Africa';
        }
        if (str_contains($clave, 'polynesia') || str_contains($clave, 'melanesia') || str_contains($clave, 'micronesia')) {
            return 'Oceania';
        }

        return $region;
    }
}
