<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo mensaje de contacto - Parroquia Santo Domingo</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5efe7;
        }
        .header {
            background: linear-gradient(135deg, #b38e2c, #d4af37);
            color: #111;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0 0;
            font-size: 14px;
            opacity: 0.8;
        }
        .content {
            background: #fff;
            padding: 25px;
            border: 1px solid #e0d5c5;
            border-top: none;
        }
        .field {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f0ebe3;
        }
        .field:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        .label {
            font-weight: 600;
            color: #b38e2c;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }
        .value {
            font-size: 16px;
            color: #333;
        }
        .message-box {
            background: #faf8f5;
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid #d4af37;
        }
        .footer {
            background: #111112;
            color: #f5efe7;
            padding: 15px;
            text-align: center;
            font-size: 12px;
            border-radius: 0 0 8px 8px;
        }
        .footer a {
            color: #d4af37;
            text-decoration: none;
        }
        .badge {
            display: inline-block;
            background: #d4af37;
            color: #111;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>✝ Parroquia Santo Domingo</h1>
        <p>Basílica de Nuestra Señora del Rosario</p>
    </div>
    
    <div class="content">
        <p style="margin-top: 0; color: #666;">Se ha recibido un nuevo mensaje desde el formulario de contacto del sitio web:</p>
        
        <div class="field">
            <div class="label">Asunto</div>
            <div class="value"><span class="badge">{{ $asunto_display }}</span></div>
        </div>
        
        <div class="field">
            <div class="label">Nombre</div>
            <div class="value">{{ $nombre }}</div>
        </div>
        
        <div class="field">
            <div class="label">Correo electrónico</div>
            <div class="value"><a href="mailto:{{ $email }}" style="color: #b38e2c;">{{ $email }}</a></div>
        </div>
        
        @if(!empty($telefono))
        <div class="field">
            <div class="label">Teléfono</div>
            <div class="value"><a href="tel:{{ $telefono }}" style="color: #b38e2c;">{{ $telefono }}</a></div>
        </div>
        @endif
        
        <div class="field">
            <div class="label">Mensaje</div>
            <div class="message-box">
                {!! nl2br(e($mensaje)) !!}
            </div>
        </div>
    </div>
    
    <div class="footer">
        <p style="margin: 0;">Este mensaje fue enviado desde <a href="{{ config('app.url') }}">{{ config('app.url') }}</a></p>
        <p style="margin: 5px 0 0; opacity: 0.7;">Orden de Predicadores • Ciudad de Guatemala</p>
    </div>
</body>
</html>
