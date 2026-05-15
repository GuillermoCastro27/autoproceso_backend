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
        .header p { margin: 6px 0 0; color: #bdc3c7; font-size: 13px; }
        .badge { display: inline-block; margin: 24px auto 0; padding: 8px 24px; border-radius: 20px; font-size: 15px; font-weight: bold; letter-spacing: 1px; }
        .badge-pendiente  { background: #f39c12; color: #fff; }
        .badge-en-proceso { background: #2980b9; color: #fff; }
        .badge-resuelto   { background: #27ae60; color: #fff; }
        .badge-anulado    { background: #c0392b; color: #fff; }
        .body { padding: 28px 32px; }
        .body p { margin: 0 0 12px; font-size: 15px; line-height: 1.6; }
        .info-box { background: #f8f9fa; border-left: 4px solid #2c3e50; border-radius: 4px; padding: 16px 20px; margin: 20px 0; }
        .info-box table { width: 100%; border-collapse: collapse; }
        .info-box td { padding: 5px 0; font-size: 14px; }
        .info-box td:first-child { color: #666; width: 140px; }
        .info-box td:last-child { font-weight: bold; color: #2c3e50; }
        .btn { display: block; width: fit-content; margin: 24px auto; padding: 12px 32px; background: #2c3e50; color: #fff !important; text-decoration: none; border-radius: 6px; font-size: 15px; font-weight: bold; text-align: center; }
        .footer { background: #ecf0f1; padding: 16px 32px; text-align: center; font-size: 12px; color: #7f8c8d; }
    </style>
</head>
<body>
<div class="wrapper">

    {{-- CABECERA --}}
    <div class="header">
        <h1>AutoProcesos</h1>
        <p>Sistema de Gestión de Talleres</p>
        <div style="text-align:center;">
            @php
                $badgeClass = match($datos['estado']) {
                    'PENDIENTE'  => 'badge-pendiente',
                    'EN PROCESO' => 'badge-en-proceso',
                    'RESUELTO'   => 'badge-resuelto',
                    'ANULADO'    => 'badge-anulado',
                    default      => 'badge-pendiente',
                };
                $mensaje = match($datos['estado']) {
                    'PENDIENTE'  => 'Hemos recibido su reclamo y está siendo revisado.',
                    'EN PROCESO' => 'Su reclamo ya está siendo atendido por nuestro equipo.',
                    'RESUELTO'   => '¡Su reclamo fue resuelto! Esperamos haber cumplido sus expectativas.',
                    'ANULADO'    => 'Su reclamo fue anulado. Si tiene dudas, contáctenos.',
                    default      => '',
                };
            @endphp
            <span class="badge {{ $badgeClass }}">{{ $datos['estado'] }}</span>
        </div>
    </div>

    {{-- CUERPO --}}
    <div class="body">
        <p>Estimado/a <strong>{{ $datos['cli_nombre'] }} {{ $datos['cli_apellido'] }}</strong>,</p>
        <p>{{ $mensaje }}</p>

        <div class="info-box">
            <table>
                <tr>
                    <td>N° de Reclamo:</td>
                    <td>#{{ str_pad($datos['id'], 7, '0', STR_PAD_LEFT) }}</td>
                </tr>
                <tr>
                    <td>Estado actual:</td>
                    <td>{{ $datos['estado'] }}</td>
                </tr>
                <tr>
                    <td>Prioridad:</td>
                    <td>{{ $datos['prioridad'] }}</td>
                </tr>
                <tr>
                    <td>Fecha registro:</td>
                    <td>{{ $datos['fecha'] }}</td>
                </tr>
                @if(!empty($datos['observacion']))
                <tr>
                    <td>Observación:</td>
                    <td>{{ $datos['observacion'] }}</td>
                </tr>
                @endif
            </table>
        </div>

        <p>Puede consultar el estado de su reclamo en cualquier momento haciendo clic en el botón:</p>

        <a href="{{ $datos['portal_url'] }}" class="btn">Ver estado de mi reclamo</a>

        <p style="font-size:13px; color:#666;">
            Si el botón no funciona, copie y pegue este enlace en su navegador:<br>
            <span style="color:#2980b9; word-break:break-all;">{{ $datos['portal_url'] }}</span>
        </p>
    </div>

    {{-- PIE --}}
    <div class="footer">
        <p>Este correo fue generado automáticamente. Por favor no responda a este mensaje.</p>
        <p>&copy; {{ date('Y') }} AutoProcesos — Todos los derechos reservados.</p>
    </div>

</div>
</body>
</html>
