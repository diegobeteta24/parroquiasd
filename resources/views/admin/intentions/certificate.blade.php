@php
    /** ===================  TOGGLES / PALETA =================== **/
    $ACCENT = '#c4b28b';        // “oro” impreso
    $ACCENT_DARK = '#9b8a64';
    $SHOW_SIGNATURES = false;    // ← cámbialo a true si quieres mostrar firmas
    $SHOW_WATERMARK  = true;     // marca de agua con el escudo
    $SHOW_QR         = false;    // desactivado por defecto para dejar más espacio

    /** ===================  DATOS =================== **/
    $mass      = $intention->mass;
    $date      = $mass->starts_at->translatedFormat('d \\d\\e F \\d\\e Y');
    $time      = $mass->starts_at->format('H:i');
    $typeLabel = [
        'rosario' => 'Rosario',
        'rezada'  => 'Misa Rezada',
        'cantada' => 'Misa Cantada',
    ][$intention->type] ?? ucfirst($intention->type);

    $donor      = $intention->donor_name ?: '—';
    $text       = $intention->public_text ?: 'Por intenciones especiales';
    $constancia = 'SD-' . $mass->starts_at->format('Ymd') . '-' . $intention->id;
    $emitida    = now()->translatedFormat('d \\d\\e F \\d\\e Y');
    $parish     = 'Parroquia Santo Domingo — Ciudad de Guatemala';

    $logo = public_path('images/logo.jpg');
    // Prefer the specific gallery 5 image
    $preferred = public_path('images/galeria 5.jpg');
    if (file_exists($preferred)) {
        $hero = $preferred;
    } else {
        $hero = public_path('images/banner.jpg');
        if (!file_exists($hero)) {
            foreach (range(1,12) as $i) {
                $cand = public_path("images/galeria {$i}.jpg");
                if (file_exists($cand)) { $hero = $cand; break; }
            }
        }
    }

    // URL de verificación (ajusta la ruta a la tuya)
    $verifyUrl = url('/constancias/verificar/' . $intention->code);

    // QR opcional (no rompe si no existe la librería)
    $qrSvg = null;
    if ($SHOW_QR && class_exists(\SimpleSoftwareIO\QrCode\Facades\QrCode::class)) {
        try {
            $qrSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                ->size(96)->margin(0)->generate($verifyUrl);
        } catch (\Throwable $e) {
            $qrSvg = null;
        }
    }
@endphp
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Constancia de intención</title>
<style>
    /* ===== Página ===== */
    @page { size: A4 landscape; margin: 10mm; }
    html, body { margin:0; padding:0; }
    * { box-sizing:border-box; }
    body { font-family: DejaVu Sans, sans-serif; color:#1f2937; }

    /* ===== Lienzo ===== */
    .sheet { position:relative; width:100%; page-break-inside:avoid; padding-bottom: 18mm; }
    .border-outer { position:absolute; inset:6mm; border:4px double {{ $ACCENT }}; border-radius:10px; }
    .border-inner { position:absolute; inset:11mm; border:1px solid #e5e7eb; border-radius:8px; }

    /* sello superior */
    .ribbon { position:absolute; top:0; right:16mm; background:{{ $ACCENT_DARK }};
        color:#fff; padding:7px 14px; border-bottom-left-radius:10px; font-size:12px; letter-spacing:.5px; }

    /* ===== Cabecera ===== */
    .header { text-align:center; margin-top:12mm; padding:0 18mm; }
    .logo { width:64px; height:64px; object-fit:contain; display:inline-block; }
    .brand { font-size:12px; letter-spacing:1.1px; color:#6b7280; margin-top:3px; }
    .title { font-size:28px; margin:6px 0 2px; font-weight:800; color:#3f3f46; }
    .subtitle { font-size:11px; color:#6b7280; }
    .meta { margin-top:6px; font-size:10px; color:#6b7280; }

    /* ===== Contenido ===== */
    .content { margin: 10mm auto 0; width: 76%; text-align:center; page-break-inside:avoid; max-height: 60mm; overflow: hidden; }
    .line { margin:8px 0; font-size:16px; line-height:1.3; }
    .highlight { font-weight:700; color:#111827; }
    .script { font-family:"Times New Roman", serif; font-style:italic; font-size:18px; color:#374151;
              word-break:break-word; }
    .badges { margin-top:10px; }
    .badge { display:inline-block; padding:4px 9px; background:#f5f5f4; color:#6b7280;
             border:1px solid #e7e5e4; border-radius:9999px; font-size:11px; margin:0 4px; }

    /* dedicatarios (envuelve bonito si hay muchos) */
    .dedic-label { margin-top:10px; font-size:14px; color:#6b7280; }
    .dedic-list  { font-size:14px; line-height:1.25; max-width:90%; margin:0 auto; word-break:break-word; }

    /* ===== Marca de agua y arte ===== */
    .watermark { position:absolute; inset:0; display:flex; align-items:center; justify-content:center;
                 opacity:.05; z-index:0; }
    .watermark img { max-height:80mm; max-width:70%; pointer-events:none; }

    .art { margin: 5mm auto 10mm; text-align:center; width: 76%; }
    .art-img { max-height: 20mm; max-width: 100%; width:auto; display:block; margin:0 auto; border:1px solid #e5e7eb; border-radius:6px; }
    .quote { margin-top:4px; font-size:10.5px; color:#6b7280; font-style:italic; line-height:1.35; }
    .quote .by { display:block; margin-top:2px; font-style:normal; color:#9ca3af; font-size:10px; }

    /* ===== Firmas (opcionales) ===== */
    .signs { position:absolute; left:16mm; right:16mm; bottom: 64mm; display:flex; gap:24mm; justify-content:center; }
    .sign { width:38%; text-align:center; }
    .sign .line { border-bottom:1px solid #d1d5db; height:32px; margin-bottom:6px; }
    .sign .role { font-size:11px; color:#6b7280; }

    /* ===== Pie ===== */
    .footer { position:fixed; left:0; right:0; bottom:8mm; text-align:center; font-size:9.5px; color:#9ca3af; }
    .micro { letter-spacing:.4px; }

    /* ===== QR / Verificación (opcional) ===== */
    .verify { margin-top:14px; font-size:10.5px; color:#6b7280; }
    .qr-wrap { margin-top:8px; display:flex; justify-content:center; gap:10px; align-items:center; }
    .qr { width:96px; height:96px; }
    .verify small { font-size:10px; }

    /* asegurar superposición correcta de textos encima de watermark */
    .layer { position:relative; z-index:1; }
</style>
</head>
<body>
<div class="sheet">
    <div class="border-outer"></div>
    <div class="border-inner"></div>
    <div class="ribbon">Constancia</div>

    @if($SHOW_WATERMARK && file_exists($logo))
        <div class="watermark">
            <img src="{{ $logo }}" alt="Escudo">
        </div>
    @endif

    <div class="header layer">
        @if (file_exists($logo))
            <img src="{{ $logo }}" class="logo" alt="Logo">
        @endif
        <div class="brand">{{ $parish }}</div>
        <div class="title">Constancia de Intención</div>
        <div class="subtitle">Se deja constancia de que, en la celebración indicada, se oró por la siguiente intención:</div>
        <div class="meta">Constancia No. <strong>{{ $constancia }}</strong> • Emitida el <strong>{{ $emitida }}</strong></div>
    </div>

    <div class="content layer">
        <div class="line">En la <span class="highlight">{{ $typeLabel }}</span> del día <span class="highlight">{{ $date }}</span> a las <span class="highlight">{{ $time }}</span>,</div>
        <div class="line">se ofreció por: <span class="highlight script">“{{ $text }}”</span></div>
        <div class="line">a petición de <span class="highlight">{{ $donor }}</span>.</div>

        @php $d = ($intention->relationLoaded('dedicatee') ? $intention->dedicatee : $intention->dedicatees->first()); @endphp
        @if($d)
            <div class="dedic-label">Dedicatario:</div>
            <div class="dedic-list">{{ $d->name ?? ( ($d->first_name ?? '') . ' ' . ($d->last_name ?? '') ) }}</div>
        @endif

        <div class="badges">
            <span class="badge">Código: {{ $intention->code }}</span>
            <span class="badge">{{ ucfirst($intention->status) }}</span>
        </div>

        @if($qrSvg)
            <div class="qr-wrap">
                <div class="qr">{!! $qrSvg !!}</div>
                <div class="verify">
                    Verifica esta constancia en<br>
                    <strong>{{ $verifyUrl }}</strong><br>
                    <small>Escanea el QR o ingresa el código <strong>{{ $intention->code }}</strong></small>
                </div>
            </div>
        @endif
    </div>

    @if($SHOW_SIGNATURES)
        <div class="signs layer">
            <div class="sign">
                <div class="line"></div>
                <div class="role">Párroco</div>
            </div>
            <div class="sign">
                <div class="line"></div>
                <div class="role">Secretaría Parroquial</div>
            </div>
        </div>
    @endif

    @if (file_exists($hero))
        <div class="art layer">
            <img src="{{ $hero }}" alt="Parroquia Santo Domingo" class="art-img">
            <div class="quote">“Ármate de oración en lugar de espada; vístete de humildad en lugar de ropas lujosas.”<br><span class="by">— Santo Domingo de Guzmán</span></div>
        </div>
    @endif

    <div class="footer layer">
        Templo de Santo Domingo — 10a. Avenida y 12 calle, Zona 1, Ciudad de Guatemala •
        <span class="micro">Este documento es una constancia simbólica sin valor fiscal</span>
    </div>
</div>
</body>
</html>
