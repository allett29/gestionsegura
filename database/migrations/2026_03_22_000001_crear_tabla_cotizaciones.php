<?php

use App\Enumeraciones\EstadoCotizacion;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Crea la tabla de cotizaciones / contrataciones de seguro de viaje.
 */
return new class extends Migration
{
    /**
     * Ejecuta la migración.
     */
    public function up(): void
    {
        Schema::create('cotizaciones', function (Blueprint $tabla) {
            $tabla->id();
            $tabla->uuid('uuid')->unique();
            $tabla->string('nombres', 100);
            $tabla->string('apellidos', 100);
            $tabla->string('numero_identificacion', 30)->index();
            $tabla->string('correo_electronico', 150);
            $tabla->date('fecha_nacimiento');
            $tabla->string('pais_destino', 120);
            $tabla->string('codigo_iso_destino', 2);
            $tabla->string('region_destino', 50);
            $tabla->date('fecha_salida');
            $tabla->date('fecha_regreso');
            $tabla->unsignedInteger('cantidad_dias');
            $tabla->decimal('tarifa_base', 10, 2);
            $tabla->decimal('porcentaje_recargo', 5, 2);
            $tabla->decimal('valor_total', 10, 2);
            $tabla->string('moneda', 3)->default('USD');
            $tabla->string('estado', 20)->default(EstadoCotizacion::Cotizado->value)->index();
            $tabla->timestamp('fecha_contratacion')->nullable();
            $tabla->timestamps();
        });
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('cotizaciones');
    }
};
