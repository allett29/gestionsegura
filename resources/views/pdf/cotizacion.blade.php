<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Cotización {{ $cotizacion->uuid }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; }
        h1 { font-size: 20px; margin-bottom: 4px; }
        h2 { font-size: 14px; margin-top: 24px; border-bottom: 1px solid #d1d5db; padding-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { text-align: left; padding: 6px 4px; vertical-align: top; }
        th { width: 40%; color: #4b5563; }
        .total { font-size: 16px; font-weight: bold; }
        .meta { color: #6b7280; margin-bottom: 20px; }
    </style>
</head>
<body>
    <h1>Cotización de Seguro de Viaje</h1>
    <p class="meta">
        Referencia: {{ $cotizacion->uuid }}<br>
        Estado: {{ $cotizacion->estado->etiqueta() }}<br>
        Emitida: {{ optional($cotizacion->created_at)->format('d/m/Y H:i') }}
    </p>

    <h2>Datos del asegurado</h2>
    <table>
        <tr><th>Nombres</th><td>{{ $cotizacion->nombres }}</td></tr>
        <tr><th>Apellidos</th><td>{{ $cotizacion->apellidos }}</td></tr>
        <tr><th>Identificación</th><td>{{ $cotizacion->numero_identificacion }}</td></tr>
        <tr><th>Correo</th><td>{{ $cotizacion->correo_electronico }}</td></tr>
        <tr><th>Fecha de nacimiento</th><td>{{ $cotizacion->fecha_nacimiento->format('d/m/Y') }}</td></tr>
    </table>

    <h2>Datos del viaje</h2>
    <table>
        <tr><th>Destino</th><td>{{ $cotizacion->pais_destino }} ({{ $cotizacion->codigo_iso_destino }})</td></tr>
        <tr><th>Región</th><td>{{ $cotizacion->region_destino }}</td></tr>
        <tr><th>Fecha de salida</th><td>{{ $cotizacion->fecha_salida->format('d/m/Y') }}</td></tr>
        <tr><th>Fecha de regreso</th><td>{{ $cotizacion->fecha_regreso->format('d/m/Y') }}</td></tr>
        <tr><th>Cantidad de días</th><td>{{ $cotizacion->cantidad_dias }}</td></tr>
    </table>

    <h2>Desglose de tarifa</h2>
    <table>
        <tr><th>Tarifa base</th><td>USD {{ number_format((float) $cotizacion->tarifa_base, 2) }}</td></tr>
        <tr><th>Recargo ({{ number_format((float) $cotizacion->porcentaje_recargo, 2) }}%)</th>
            <td>USD {{ number_format(((float) $cotizacion->tarifa_base) * ((float) $cotizacion->porcentaje_recargo) / 100, 2) }}</td>
        </tr>
        <tr>
            <th class="total">Total</th>
            <td class="total">USD {{ number_format((float) $cotizacion->valor_total, 2) }}</td>
        </tr>
    </table>

    @if ($cotizacion->fecha_contratacion)
        <p style="margin-top: 24px;">Fecha de contratación: {{ $cotizacion->fecha_contratacion->format('d/m/Y H:i') }}</p>
    @endif
</body>
</html>
