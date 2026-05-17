<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background:#f4f4f4; margin:0; padding:20px; }
        .container { max-width:520px; margin:0 auto; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.1); }
        .header { background:#c0392b; padding:24px 32px; }
        .header h1 { color:#fff; margin:0; font-size:20px; }
        .body { padding:28px 32px; color:#333; font-size:14px; line-height:1.6; }
        .alert-box { background:#fdf2f2; border-left:4px solid #c0392b; padding:14px 18px; border-radius:4px; margin:18px 0; }
        .field { margin:8px 0; }
        .field strong { display:inline-block; width:120px; color:#555; }
        .footer { background:#f9f9f9; padding:16px 32px; font-size:12px; color:#999; border-top:1px solid #eee; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>⚠️ Alerta de Seguridad — AutoProcesos</h1>
    </div>
    <div class="body">
        <p>Se ha <strong>bloqueado automáticamente</strong> una cuenta de usuario tras 3 intentos fallidos de inicio de sesión.</p>

        <div class="alert-box">
            <div class="field"><strong>Usuario:</strong> {{ $loginUsuario }}</div>
            <div class="field"><strong>IP:</strong> {{ $ip }}</div>
            <div class="field"><strong>Fecha/Hora:</strong> {{ $fechaHora }}</div>
            <div class="field"><strong>Bloqueo:</strong> 30 minutos</div>
        </div>

        <p>Si este intento fue legítimo, el usuario puede recuperar su acceso a través de la opción <em>"¿Olvidé mi contraseña?"</em>.</p>
        <p>Si considera que es un intento de acceso no autorizado, revise los registros de auditoría de login en el sistema.</p>
    </div>
    <div class="footer">
        Este mensaje fue generado automáticamente por AutoProcesos. No responda este correo.
    </div>
</div>
</body>
</html>
