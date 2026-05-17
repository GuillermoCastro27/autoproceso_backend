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
        .info-box { background:#f8f9fa; border-left:4px solid #2c3e50; border-radius:4px; padding:12px 16px; margin-bottom:0; }
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
        .medios { margin:12px 0; }
        .medio { display:inline-block; border:1px solid #ddd; border-radius:4px; padding:8px 14px; margin:4px 6px 4px 0; min-width:120px; font-size:13px; }
        .medio strong { display:block; font-size:10px; text-transform:uppercase; letter-spacing:1px; color:#666; margin-bottom:4px; }
        .total-line { font-size:16px; font-weight:bold; color:#2c3e50; margin-top:8px; }
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

    {{-- CABECERA --}}
    <div class="header">
        <h1>{{ $datos['emp_razon_social'] ?? 'AutoProcesos' }}</h1>
        <p>Sistema de Gestión de Talleres</p>
        <div>
            <span class="doc-num">RECIBO N° {{ str_pad($datos['id'], 7, '0', STR_PAD_LEFT) }}</span>
        </div>
    </div>

    <div class="body">
        <p>Estimado/a <strong>{{ $datos['cli_nombre'] }} {{ $datos['cli_apellido'] }}</strong>,</p>
        <p>Le enviamos el comprobante de su cobro. A continuación encontrará el detalle.</p>

        {{-- COBRO + CLIENTE --}}
        <div class="two-col">
            <div class="col">
                <div class="info-box">
                    <h3>Datos del Cobro</h3>
                    <table>
                        <tr><td>Fecha:</td><td>{{ $datos['cobro_fecha'] }}</td></tr>
                        <tr><td>Estado:</td>
                            <td>
                                @php
                                    $bc = match($datos['cobro_estado']) {
                                        'CONFIRMADO' => 'badge-confirmado',
                                        'ANULADO'    => 'badge-anulado',
                                        default      => 'badge-pendiente',
                                    };
                                @endphp
                                <span class="badge {{ $bc }}">{{ $datos['cobro_estado'] }}</span>
                            </td>
                        </tr>
                        <tr><td>Forma de cobro:</td><td>{{ $datos['forma_cobro'] }}</td></tr>
                        <tr><td>Caja:</td><td>{{ $datos['caja'] }}</td></tr>
                        <tr><td>Sucursal:</td><td>{{ $datos['suc_razon_social'] }}</td></tr>
                    </table>
                </div>
            </div>
            <div class="col">
                <div class="info-box">
                    <h3>Cliente</h3>
                    <table>
                        <tr><td>Nombre:</td><td>{{ $datos['cli_nombre'] }} {{ $datos['cli_apellido'] }}</td></tr>
                        <tr><td>RUC:</td><td>{{ $datos['cli_ruc'] }}</td></tr>
                        <tr><td>Teléfono:</td><td>{{ $datos['cli_telefono'] ?? '—' }}</td></tr>
                        <tr><td>Dirección:</td><td>{{ $datos['cli_direccion'] ?? '—' }}</td></tr>
                    </table>
                </div>
            </div>
        </div>

        {{-- CUOTAS --}}
        @if(!empty($datos['cuotas']) && count($datos['cuotas']) > 0)
        <div class="section">
            <h3>Cuotas Cobradas</h3>
            <table class="det">
                <thead>
                    <tr><th>Venta</th><th>Cuota N°</th><th>Vencimiento</th><th class="tr">Monto</th></tr>
                </thead>
                <tbody>
                    @foreach($datos['cuotas'] as $c)
                    <tr>
                        <td>{{ $c->venta_nro ?? '' }}</td>
                        <td>{{ $c->nro_cuota ?? '' }}</td>
                        <td>{{ $c->fecha_vencimiento ?? '' }}</td>
                        <td class="tr">{{ number_format($c->monto_cobrado, 2, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- DETALLE PRODUCTOS --}}
        @if(!empty($datos['detalles']) && count($datos['detalles']) > 0)
        <div class="section">
            <h3>Detalle de Productos</h3>
            @php
                $totalGral = 0; $totalIva = 0;
            @endphp
            <table class="det">
                <thead>
                    <tr><th>Producto</th><th class="tr">Cant.</th><th class="tr">Precio</th><th>Imp.</th><th class="tr">Subtotal</th><th class="tr">IVA</th></tr>
                </thead>
                <tbody>
                    @foreach($datos['detalles'] as $d)
                    @php
                        $sub = $d->subtotal ?? ($d->cantidad * $d->precio);
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
                        <td class="tr">{{ number_format($d->precio, 2, ',', '.') }}</td>
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

        {{-- MEDIOS DE PAGO --}}
        <div class="section">
            <h3>Medios de Pago</h3>
            <div class="medios">
                @if(($datos['monto_efectivo'] ?? 0) > 0)
                <div class="medio">
                    <strong>Efectivo</strong>
                    {{ number_format($datos['monto_efectivo'], 2, ',', '.') }}
                </div>
                @endif
                @if(!empty($datos['tarjeta']))
                <div class="medio">
                    <strong>Tarjeta</strong>
                    {{ number_format($datos['tarjeta']->monto_tarjeta ?? 0, 2, ',', '.') }}
                    @if(!empty($datos['tarjeta']->nro_tarjeta))
                    <br><small style="color:#888;">N° {{ $datos['tarjeta']->nro_tarjeta }}</small>
                    @endif
                </div>
                @endif
                @if(!empty($datos['cheque']))
                <div class="medio">
                    <strong>Cheque</strong>
                    {{ number_format($datos['cheque']->monto_cheque ?? 0, 2, ',', '.') }}
                    @if(!empty($datos['cheque']->nro_cheque))
                    <br><small style="color:#888;">N° {{ $datos['cheque']->nro_cheque }}</small>
                    @endif
                </div>
                @endif
            </div>
            <p class="total-line">Total cobrado: {{ number_format($datos['cobro_importe'], 2, ',', '.') }}</p>
        </div>

        @if(!empty($datos['cobro_observacion']) && trim($datos['cobro_observacion']) !== '')
        <div class="section">
            <h3>Observación</h3>
            <p style="font-style:italic;color:#555;">{{ $datos['cobro_observacion'] }}</p>
        </div>
        @endif

        <div class="notice">
            Este correo fue generado automáticamente como comprobante de su cobro.
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
