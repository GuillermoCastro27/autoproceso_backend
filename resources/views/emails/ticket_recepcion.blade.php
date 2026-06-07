<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { margin:0; padding:0; background:#f4f6f8; font-family:Arial,sans-serif; color:#333; }
        .wrapper { max-width:620px; margin:30px auto; background:#fff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.1); }
        .header { background:#2c3e50; padding:24px 32px; text-align:center; }
        .header h1 { margin:0; color:#fff; font-size:20px; letter-spacing:1px; }
        .header p  { margin:4px 0 0; color:#bdc3c7; font-size:12px; }
        .doc-num   { display:inline-block; margin-top:14px; padding:6px 24px; background:#fff; color:#2c3e50; border-radius:20px; font-weight:bold; font-size:14px; letter-spacing:1px; }
        .body { padding:24px 32px; }
        .body p { margin:0 0 10px; font-size:14px; line-height:1.6; }
        .two-col { display:table; width:100%; border-collapse:collapse; margin:16px 0; }
        .two-col .col { display:table-cell; width:50%; vertical-align:top; padding:0 8px 0 0; }
        .two-col .col:last-child { padding:0 0 0 8px; }
        .info-box { background:#f8f9fa; border-left:4px solid #2c3e50; border-radius:4px; padding:12px 16px; }
        .info-box h3 { margin:0 0 8px; font-size:11px; text-transform:uppercase; letter-spacing:1px; color:#666; border-bottom:1px solid #ddd; padding-bottom:4px; }
        .info-box table { width:100%; border-collapse:collapse; }
        .info-box td { padding:3px 0; font-size:13px; vertical-align:top; }
        .info-box td:first-child { color:#666; width:140px; }
        .info-box td:last-child  { font-weight:bold; color:#2c3e50; }
        .section { margin:20px 0; }
        .section h3 { font-size:11px; text-transform:uppercase; letter-spacing:1px; color:#666; border-bottom:1px solid #ddd; padding-bottom:4px; margin-bottom:10px; }
        table.det { width:100%; border-collapse:collapse; font-size:13px; }
        table.det thead th { background:#2c3e50; color:#fff; padding:7px 8px; text-align:left; font-size:11px; text-transform:uppercase; }
        table.det tbody tr:nth-child(even) { background:#f8f8f8; }
        table.det td { padding:6px 8px; border-bottom:1px solid #eee; }
        table.det tfoot td { font-weight:bold; border-top:2px solid #2c3e50; padding:7px 8px; }
        .tr { text-align:right; }
        .badge { display:inline-block; padding:3px 14px; border-radius:12px; font-size:12px; font-weight:bold; }
        .badge-pendiente  { background:#f39c12; color:#fff; }
        .badge-confirmado { background:#27ae60; color:#fff; }
        .badge-anulado    { background:#c0392b; color:#fff; }
        .notice { font-size:11px; color:#888; background:#f0f0f0; border-radius:4px; padding:10px 14px; margin-top:16px; }
        .footer { background:#ecf0f1; padding:14px 32px; text-align:center; font-size:11px; color:#7f8c8d; }
    </style>
</head>
<body>
<div class="wrapper">

    <div class="header">
        <h1>{{ $datos['emp_razon_social'] ?? 'AutoProcesos' }}</h1>
        <p>Sistema de Gestión de Talleres</p>
        <div>
            <span class="doc-num">RECEPCIÓN N° {{ str_pad($datos['id'], 7, '0', STR_PAD_LEFT) }}</span>
        </div>
    </div>

    <div class="body">
        <p>Estimado/a <strong>{{ $datos['cli_nombre'] }} {{ $datos['cli_apellido'] }}</strong>,</p>
        <p>Le enviamos el comprobante de recepción de su vehículo. A continuación encontrará el detalle.</p>

        <div class="two-col">
            <div class="col">
                <div class="info-box">
                    <h3>Datos de Recepción</h3>
                    <table>
                        <tr><td>Fecha:</td><td>{{ $datos['recep_cab_fecha'] }}</td></tr>
                        <tr><td>Fecha Estimada:</td><td>{{ $datos['recep_cab_fecha_estimada'] }}</td></tr>
                        <tr><td>Prioridad:</td><td>{{ $datos['recep_cab_prioridad'] }}</td></tr>
                        <tr><td>Estado:</td>
                            <td>
                                @php
                                    $bc = match($datos['recep_cab_estado']) {
                                        'CONFIRMADO' => 'badge-confirmado',
                                        'ANULADO'    => 'badge-anulado',
                                        default      => 'badge-pendiente',
                                    };
                                @endphp
                                <span class="badge {{ $bc }}">{{ $datos['recep_cab_estado'] }}</span>
                            </td>
                        </tr>
                        <tr><td>Kilometraje:</td><td>{{ $datos['recep_cab_kilometraje'] }} km</td></tr>
                        <tr><td>Combustible:</td><td>{{ $datos['recep_cab_nivel_combustible'] }}</td></tr>
                        <tr><td>Sucursal:</td><td>{{ $datos['suc_razon_social'] }}</td></tr>
                        <tr><td>Encargado:</td><td>{{ $datos['encargado'] }}</td></tr>
                    </table>
                </div>
            </div>
            <div class="col">
                <div class="info-box">
                    <h3>Cliente</h3>
                    <table>
                        <tr><td>Nombre:</td><td>{{ $datos['cli_nombre'] }} {{ $datos['cli_apellido'] }}</td></tr>
                        <tr><td>RUC:</td><td>{{ $datos['cli_ruc'] ?? '—' }}</td></tr>
                        <tr><td>Teléfono:</td><td>{{ $datos['cli_telefono'] ?? '—' }}</td></tr>
                        <tr><td>Dirección:</td><td>{{ $datos['cli_direccion'] ?? '—' }}</td></tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="section">
            <h3>Vehículo</h3>
            <div class="info-box">
                <table>
                    <tr><td>Vehículo:</td><td>{{ $datos['vehiculo_info'] }}</td></tr>
                    @if(!empty($datos['recep_cab_num_chasis']))
                    <tr><td>Chasis:</td><td>{{ $datos['recep_cab_num_chasis'] }}</td></tr>
                    @endif
                    @if(!empty($datos['tipo_serv_nombre']))
                    <tr><td>Tipo Servicio:</td><td>{{ $datos['tipo_serv_nombre'] }}</td></tr>
                    @endif
                    @if(!empty($datos['recep_cab_observaciones']))
                    <tr><td>Observaciones:</td><td>{{ $datos['recep_cab_observaciones'] }}</td></tr>
                    @endif
                </table>
            </div>
        </div>

        @if(!empty($datos['detalles']) && count($datos['detalles']) > 0)
        <div class="section">
            <h3>Repuestos / Materiales</h3>
            @php $totalGral = 0; $totalIva = 0; @endphp
            <table class="det">
                <thead>
                    <tr><th>Producto</th><th class="tr">Cant.</th><th class="tr">Precio</th><th>Imp.</th><th class="tr">Subtotal</th><th class="tr">IVA</th></tr>
                </thead>
                <tbody>
                    @foreach($datos['detalles'] as $d)
                    @php
                        $sub = (float)($d->cantidad ?? 0) * (float)($d->precio ?? 0);
                        $iva = match($d->tip_imp_nom ?? '') {
                            'IVA10' => $sub / 11,
                            'IVA5'  => $sub / 21,
                            default => 0,
                        };
                        $totalGral += $sub;
                        $totalIva  += $iva;
                    @endphp
                    <tr>
                        <td>{{ $d->item_decripcion }}</td>
                        <td class="tr">{{ $d->cantidad }}</td>
                        <td class="tr">{{ number_format((float)$d->precio, 2, ',', '.') }}</td>
                        <td>{{ $d->tip_imp_nom }}</td>
                        <td class="tr">{{ number_format($sub, 2, ',', '.') }}</td>
                        <td class="tr">{{ number_format($iva, 2, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="tr">Total Comprobante</td>
                        <td class="tr">{{ number_format($totalGral, 2, ',', '.') }}</td>
                        <td class="tr">{{ number_format($totalIva, 2, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif

        <div class="notice">
            Este correo fue generado automáticamente como comprobante de recepción de su vehículo.
            Si tiene dudas comuníquese directamente con la sucursal.
        </div>
    </div>

    <div class="footer">
        <p>Este correo fue generado automáticamente. Por favor no responda a este mensaje.</p>
        <p>&copy; {{ date('Y') }} AutoProcesos — Todos los derechos reservados.</p>
    </div>

</div>
</body>
</html>
