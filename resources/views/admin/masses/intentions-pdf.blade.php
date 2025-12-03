<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 28px; }
        body { font-family: DejaVu Sans, sans-serif; color: #111827; font-size: 12px; }
        .brand { display:flex; align-items:center; gap:12px; margin-bottom:8px; }
        .brand img { width:36px; height:36px; object-fit: contain; }
        .brand .name { font-weight:800; font-size: 14px; }
        .title { font-size: 18px; font-weight: 800; margin-bottom: 8px; }
        .muted { color:#6B7280; }
        .chips { margin-top: 2px; }
        .chip { display:inline-block; border-radius:9999px; padding:2px 8px; font-size:11px; color:#fff; margin-right:6px; }
        .chip.orange { background:#F59E0B; }
        .chip.green { background:#10B981; }
        table { width:100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 7px 8px; border-bottom: 1px solid #E5E7EB; vertical-align: top; }
        th { font-size: 11px; text-transform: uppercase; letter-spacing: .05em; color:#6B7280; text-align:left; background:#F9FAFB; }
        .small { color:#6B7280; font-size: 11px; }
        .footer { margin-top: 12px; text-align:right; color:#6B7280; font-size: 11px; }
    </style>
</head>
<body>
    <div class="brand">
        <img src="{{ public_path('images/logo.jpg') }}" alt="Logo">
        <div class="name">Parroquia Santo Domingo — Basílica del Rosario</div>
    </div>
    <div class="title">Intenciones — {{ $mass->starts_at->format('d/m/Y H:i') }}</div>
    <div class="muted">
        Estado: <strong>{{ ucfirst($mass->status) }}</strong>
        @if($mass->priest)
            — Sacerdote: <strong>{{ $mass->priest->name }}</strong>
        @endif
    </div>
    

    <table>
        <thead>
            <tr>
                <th style="width:12%">Tipo</th>
                <th>Texto público</th>
                <th style="width:20%">Donante</th>
                <th style="width:36%">Dedicatario</th>
                <th style="width:12%">Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($mass->intentions as $i)
                <tr>
                    <td>
                        @if($i->category)
                            {{ str_replace('_',' ', ucfirst($i->category)) }}
                        @else
                            {{ ucfirst($i->type) }}
                        @endif
                    </td>
                    <td>{{ $i->public_text }}</td>
                    <td>{{ $i->donor_name }}</td>
                    <td>
                        @php $d = $i->dedicatee ?? $i->dedicatees->first(); @endphp
                        @if(!$d)
                            <span class="small">—</span>
                        @else
                            <div>{{ $d->name ?? ($d->first_name.' '.$d->last_name) }}</div>
                        @endif
                    </td>
                    <td>{{ ucfirst($i->status) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="small">No hay intenciones registradas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Generado el {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
