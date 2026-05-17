<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { margin: 0; padding: 0; background: #f4f6f8; font-family: Arial, sans-serif; color: #333; }
        .wrapper { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .header { background: #2c3e50; padding: 28px 32px; text-align: center; }
        .header h1 { margin: 0; color: #fff; font-size: 22px; letter-spacing: 1px; }
        .header p  { margin: 6px 0 0; color: #bdc3c7; font-size: 13px; }
        .body { padding: 28px 32px; }
        .body p { margin: 0 0 14px; font-size: 15px; line-height: 1.6; }
        .alert-box { background: #fff3cd; border-left: 4px solid #f39c12; border-radius: 4px; padding: 16px 20px; margin: 20px 0; }
        .alert-box p { margin: 0; font-size: 14px; }
        .badge-ok { display: inline-block; background: #27ae60; color: #fff; padding: 8px 24px; border-radius: 20px; font-size: 14px; font-weight: bold; margin: 10px 0 20px; }
        .footer { background: #f8f9fa; padding: 18px 32px; text-align: center; }
        .footer p { margin: 0; color: #999; font-size: 12px; line-height: 1.7; }
    </style>
</head>
<body>
<div class="wrapper">

    <div class="header">
        <h1>AutoProcesos</h1>
        <p>Notificación de seguridad</p>
    </div>

    <div class="body">
        <p>Hola, <strong>{{ $nombre }}</strong>.</p>
        <p>Te informamos que la contraseña de tu cuenta en <strong>AutoProcesos</strong> fue cambiada exitosamente.</p>

        <div style="text-align:center;">
            <span class="badge-ok">✔ Contraseña actualizada</span>
        </div>

        <p>Como medida de seguridad, <strong>todas tus sesiones activas fueron cerradas</strong>. Deberás iniciar sesión nuevamente con tu nueva contraseña.</p>

        <div class="alert-box">
            <p>
                <strong>⚠ ¿No fuiste vos quien realizó este cambio?</strong><br>
                Si no solicitaste este cambio de contraseña, contactá de inmediato al administrador del sistema, ya que alguien más podría haber accedido a tu cuenta.
            </p>
        </div>

        <p>Este correo fue enviado automáticamente el {{ now()->format('d/m/Y') }} a las {{ now()->format('H:i') }}.</p>
    </div>

    <div class="footer">
        <p>AutoProcesos — Sistema de Gestión de Compras, Servicios y Ventas<br>
        Este es un mensaje automático, por favor no respondas este correo.</p>
    </div>

</div>
</body>
</html>
