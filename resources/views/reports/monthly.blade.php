<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Mensual de Intenciones</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #111; }
        h1, h2 { margin: 0 0 8px; }
        .muted { color: #666; }
        .section { margin: 16px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        th { background: #f3f4f6; }
        .right { text-align: right; }
        .small { font-size: 11px; }
    </style>
</head>
<body>
    <h1>Reporte Mensual de Intenciones</h1>
    <div class="muted small">
        Periodo: {{ sprintf('%04d-%02d', $stats['period']['year'], $stats['period']['month']) }}
        &middot; {{ $stats['period']['from']->isoFormat('D MMM YYYY') }} — {{ $stats['period']['to']->isoFormat('D MMM YYYY') }}
    </div>

    <div class="section">
        <h2>Totales</h2>
        <table>
            <tbody>
                <tr>
                    <th>Intenciones creadas</th>
                    <td class="right">{{ number_format($stats['totals']['intentions']) }}</td>
                </tr>
                <tr>
                    <th>Intenciones pagadas/celebradas</th>
                    <td class="right">{{ number_format($stats['totals']['paid_count']) }}</td>
                </tr>
                <tr>
                    <th>Ingreso total (pagado)</th>
                    <td class="right">Q {{ number_format($stats['totals']['paid_amount'], 2) }}</td>
                </tr>
                <tr>
                    <th>Monto promedio (todas)</th>
                    <td class="right">Q {{ number_format($stats['totals']['average_amount'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Por estado</h2>
        <table>
            <thead>
                <tr>
                    <th>Estado</th>
                    <th class="right">Cantidad</th>
                </tr>
            </thead>
            <tbody>
            @foreach($stats['by_status'] as $status => $count)
                <tr>
                    <td>{{ ucfirst($status) }}</td>
                    <td class="right">{{ number_format($count) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Por método de pago</h2>
        <table>
            <thead>
                <tr>
                    <th>Método</th>
                    <th class="right">Cantidad</th>
                    <th class="right">Total</th>
                </tr>
            </thead>
            <tbody>
            @foreach($stats['by_payment_method'] as $method => $row)
                <tr>
                    <td>{{ strtoupper($method) }}</td>
                    <td class="right">{{ number_format($row['count']) }}</td>
                    <td class="right">Q {{ number_format($row['total'], 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Detalle Recurrente</h2>
        <table>
            <thead>
                <tr>
                    <th>Indicador</th>
                    <th class="right">Valor</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Intenciones recibidas</td>
                    <td class="right">{{ number_format($stats['recurrente']['count'] ?? 0) }}</td>
                </tr>
                <tr>
                    <td>Monto liquidado</td>
                    <td class="right">Q {{ number_format($stats['recurrente']['total'] ?? 0, 2) }}</td>
                </tr>
            </tbody>
        </table>
        <p class="small muted">Se muestran las intenciones con <code>payment_method = recurrente</code>.</p>
    </div>

    <div class="section">
        <h2>Por tipo de intención (ingresos)</h2>
        <table>
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th class="right">Cantidad</th>
                    <th class="right">Total</th>
                </tr>
            </thead>
            <tbody>
            @foreach(($stats['by_type_income'] ?? []) as $type => $row)
                <tr>
                    <td>{{ ucfirst(str_replace('_',' ', $type)) }}</td>
                    <td class="right">{{ number_format($row['count']) }}</td>
                    <td class="right">Q {{ number_format($row['total'], 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="section small muted">
        Generado el {{ now()->isoFormat('D MMM YYYY HH:mm') }}
    </div>
</body>
</html>
