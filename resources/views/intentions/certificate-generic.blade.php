@php
    /** ===================  TOGGLES / PALETA =================== **/
    $ACCENT = '#c4b28b';        // "oro" impreso
    $ACCENT_DARK = '#9b8a64';
    $SHOW_SIGNATURES = false;
    $SHOW_WATERMARK  = true;
    $SHOW_QR         = false;

    /** ===================  DATOS GENÉRICOS =================== **/
    $logo = public_path('images/logo.jpg');
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
    .content { margin: 12mm auto 0; width: 76%; text-align:center; page-break-inside:avoid; max-height: 65mm; overflow: hidden; }
    .line { margin:10px 0; font-size:16px; line-height:1.4; }
    .highlight { font-weight:700; color:#111827; }
    .script { font-family:"Times New Roman", serif; font-style:italic; font-size:18px; color:#374151; }

    /* ===== Marca de agua ===== */
    .watermark { position:absolute; inset:0; display:flex; align-items:center; justify-content:center;
                 opacity:.05; z-index:0; }
    .watermark img { max-height:80mm; max-width:70%; pointer-events:none; }

    .art { margin: 8mm auto 10mm; text-align:center; width: 76%; }
    .art-img { max-height: 22mm; max-width: 100%; width:auto; display:block; margin:0 auto; border:1px solid #e5e7eb; border-radius:6px; }
    .quote { margin-top:5px; font-size:10.5px; color:#6b7280; font-style:italic; line-height:1.35; }
    .quote .by { display:block; margin-top:2px; font-style:normal; color:#9ca3af; font-size:10px; }

    /* ===== Pie ===== */
    .footer { position:fixed; left:0; right:0; bottom:8mm; text-align:center; font-size:9.5px; color:#9ca3af; }
    .micro { letter-spacing:.4px; }

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
        <div class="title">Constancia de Intención de Misa</div>
        <div class="subtitle">Agradecimiento por tu generosidad y fe</div>
        <div class="meta">Emitida el <strong>{{ $emitida }}</strong></div>
    </div>

    <div class="content layer">
        <div class="line" style="margin-top: 20px;">
            La <span class="highlight">Parroquia Santo Domingo</span> agradece tu intención de misa.
        </div>
        
        <div class="line" style="margin-top: 18px;">
            Tu oración será incluida en nuestras próximas celebraciones litúrgicas.
        </div>
        
        <div class="line" style="margin-top: 18px;">
            Recibirás un correo electrónico con la confirmación detallada<br>
            que incluirá la fecha, hora y tipo de celebración.
        </div>

        <div class="line script" style="margin-top: 22px; color: {{ $ACCENT_DARK }};">
            "Que el Señor bendiga tu generosidad y escuche tu intención"
        </div>
    </div>

    @if (file_exists($hero))
        <div class="art layer">
            <img src="{{ $hero }}" alt="Parroquia Santo Domingo" class="art-img">
            <div class="quote">"Ármate de oración en lugar de espada; vístete de humildad en lugar de ropas lujosas."<br><span class="by">— Santo Domingo de Guzmán</span></div>
        </div>
    @endif

    <div class="footer layer">
        Templo de Santo Domingo — 10a. Avenida y 12 calle, Zona 1, Ciudad de Guatemala •
        <span class="micro">Este documento es una constancia simbólica sin valor fiscal</span>
    </div>
</div>
</body>
</html>
