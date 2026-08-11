<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seeder principal de la aplicación.
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Ejecuta los seeders de la aplicación.
     */
    public function run(): void
    {
        $this->call([
            CotizacionSeeder::class,
        ]);
    }
}
