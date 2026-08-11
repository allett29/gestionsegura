<?php

namespace App\Modelos;

use App\Enumeraciones\EstadoCotizacion;
use Database\Factories\CotizacionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Cotización o contratación de un seguro de viaje.
 *
 * @property int $id
 * @property string $uuid
 * @property string $nombres
 * @property string $apellidos
 * @property string $numero_identificacion
 * @property string $correo_electronico
 * @property \Illuminate\Support\Carbon $fecha_nacimiento
 * @property string $pais_destino
 * @property string $codigo_iso_destino
 * @property string $region_destino
 * @property \Illuminate\Support\Carbon $fecha_salida
 * @property \Illuminate\Support\Carbon $fecha_regreso
 * @property int $cantidad_dias
 * @property string $tarifa_base
 * @property string $porcentaje_recargo
 * @property string $valor_total
 * @property string $moneda
 * @property EstadoCotizacion $estado
 * @property \Illuminate\Support\Carbon|null $fecha_contratacion
 */
class Cotizacion extends Model
{
    /** @use HasFactory<CotizacionFactory> */
    use HasFactory;

    /**
     * Nombre de la tabla.
     */
    protected $table = 'cotizaciones';

    /**
     * Atributos asignables masivamente.
     *
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'nombres',
        'apellidos',
        'numero_identificacion',
        'correo_electronico',
        'fecha_nacimiento',
        'pais_destino',
        'codigo_iso_destino',
        'region_destino',
        'fecha_salida',
        'fecha_regreso',
        'cantidad_dias',
        'tarifa_base',
        'porcentaje_recargo',
        'valor_total',
        'moneda',
        'estado',
        'fecha_contratacion',
    ];

    /**
     * Conversiones de tipos.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
            'fecha_salida' => 'date',
            'fecha_regreso' => 'date',
            'fecha_contratacion' => 'datetime',
            'cantidad_dias' => 'integer',
            'tarifa_base' => 'decimal:2',
            'porcentaje_recargo' => 'decimal:2',
            'valor_total' => 'decimal:2',
            'estado' => EstadoCotizacion::class,
        ];
    }

    /**
     * Inicializa UUID al crear el modelo.
     */
    protected static function booted(): void
    {
        static::creating(function (Cotizacion $cotizacion): void {
            if (empty($cotizacion->uuid)) {
                $cotizacion->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Factory asociada (modelo fuera de App\Models).
     */
    protected static function newFactory(): CotizacionFactory
    {
        return CotizacionFactory::new();
    }

    /**
     * Nombre completo del asegurado.
     */
    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombres} {$this->apellidos}");
    }

    /**
     * Scope: solo cotizadas.
     *
     * @param  Builder<Cotizacion>  $consulta
     * @return Builder<Cotizacion>
     */
    public function scopeCotizadas(Builder $consulta): Builder
    {
        return $consulta->where('estado', EstadoCotizacion::Cotizado);
    }

    /**
     * Scope: solo contratadas.
     *
     * @param  Builder<Cotizacion>  $consulta
     * @return Builder<Cotizacion>
     */
    public function scopeContratadas(Builder $consulta): Builder
    {
        return $consulta->where('estado', EstadoCotizacion::Contratado);
    }

    /**
     * Indica si puede contratarse.
     */
    public function puedeContratar(): bool
    {
        return $this->estado === EstadoCotizacion::Cotizado;
    }
}
