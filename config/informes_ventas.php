<?php

return [

    'ventas' => [
        'titulo'    => 'Listado de Ventas',
        'cache_ttl' => 600,
        'columnas'  => [
            ['key' => 'id',             'label' => 'Código'],
            ['key' => 'fecha',          'label' => 'Fecha'],
            ['key' => 'vence',          'label' => 'Vencimiento'],
            ['key' => 'cliente',        'label' => 'Cliente'],
            ['key' => 'ruc',            'label' => 'RUC'],
            ['key' => 'condicion_pago', 'label' => 'Condición'],
            ['key' => 'cuotas',         'label' => 'Cuotas'],
            ['key' => 'funcionario',    'label' => 'Encargado'],
            ['key' => 'empresa',        'label' => 'Empresa'],
            ['key' => 'sucursal',       'label' => 'Sucursal'],
            ['key' => 'estado',         'label' => 'Estado'],
        ],
        'totales' => [],
    ],

    'pedido_ventas' => [
        'titulo'    => 'Listado de Pedidos de Ventas',
        'cache_ttl' => 300,
        'columnas'  => [
            ['key' => 'id',            'label' => 'Código'],
            ['key' => 'fecha',         'label' => 'Fecha'],
            ['key' => 'entrega',       'label' => 'Entrega'],
            ['key' => 'cliente',       'label' => 'Cliente'],
            ['key' => 'observaciones', 'label' => 'Observaciones'],
            ['key' => 'funcionario',   'label' => 'Encargado'],
            ['key' => 'empresa',       'label' => 'Empresa'],
            ['key' => 'sucursal',      'label' => 'Sucursal'],
            ['key' => 'estado',        'label' => 'Estado'],
        ],
        'totales' => [],
    ],

    'nota_remi_vent' => [
        'titulo'    => 'Listado de Notas de Remisión (Ventas)',
        'cache_ttl' => 300,
        'columnas'  => [
            ['key' => 'id',            'label' => 'Código'],
            ['key' => 'fecha',         'label' => 'Fecha'],
            ['key' => 'cliente',       'label' => 'Cliente'],
            ['key' => 'observaciones', 'label' => 'Observaciones'],
            ['key' => 'funcionario',   'label' => 'Encargado'],
            ['key' => 'empresa',       'label' => 'Empresa'],
            ['key' => 'sucursal',      'label' => 'Sucursal'],
            ['key' => 'estado',        'label' => 'Estado'],
        ],
        'totales' => [],
    ],

    'notas_vent' => [
        'titulo'    => 'Listado de Notas de Venta',
        'cache_ttl' => 300,
        'columnas'  => [
            ['key' => 'id',             'label' => 'Código'],
            ['key' => 'fecha',          'label' => 'Fecha'],
            ['key' => 'vence',          'label' => 'Vencimiento'],
            ['key' => 'tipo',           'label' => 'Tipo'],
            ['key' => 'cliente',        'label' => 'Cliente'],
            ['key' => 'condicion_pago', 'label' => 'Condición'],
            ['key' => 'cuotas',         'label' => 'Cuotas'],
            ['key' => 'funcionario',    'label' => 'Encargado'],
            ['key' => 'empresa',        'label' => 'Empresa'],
            ['key' => 'sucursal',       'label' => 'Sucursal'],
            ['key' => 'estado',         'label' => 'Estado'],
        ],
        'totales' => [],
    ],

    'cobros' => [
        'titulo'    => 'Listado de Cobros',
        'cache_ttl' => 300,
        'columnas'  => [
            ['key' => 'id',          'label' => 'Código'],
            ['key' => 'fecha',       'label' => 'Fecha'],
            ['key' => 'cliente',     'label' => 'Cliente'],
            ['key' => 'importe',     'label' => 'Importe'],
            ['key' => 'forma_cobro', 'label' => 'Forma de Cobro'],
            ['key' => 'funcionario', 'label' => 'Encargado'],
            ['key' => 'empresa',     'label' => 'Empresa'],
            ['key' => 'sucursal',    'label' => 'Sucursal'],
            ['key' => 'estado',      'label' => 'Estado'],
        ],
        'totales' => [
            ['label' => 'total_cobrado', 'display' => 'Total Cobrado'],
            ['label' => 'registros',     'display' => 'Registros'],
        ],
    ],

    'apertura_cierre_caja' => [
        'titulo'    => 'Listado de Aperturas y Cierres de Caja',
        'cache_ttl' => 300,
        'columnas'  => [
            ['key' => 'id',                      'label' => 'Código'],
            ['key' => 'fecha_apertura',           'label' => 'Fecha Apertura'],
            ['key' => 'fecha_cierre',             'label' => 'Fecha Cierre'],
            ['key' => 'caja',                     'label' => 'Caja'],
            ['key' => 'monto_apertura',           'label' => 'Monto Apertura'],
            ['key' => 'monto_efectivo_cierre',    'label' => 'Cierre Efectivo'],
            ['key' => 'monto_tarjeta_cierre',     'label' => 'Cierre Tarjeta'],
            ['key' => 'monto_cheque_cierre',      'label' => 'Cierre Cheque'],
            ['key' => 'funcionario',              'label' => 'Encargado'],
            ['key' => 'empresa',                  'label' => 'Empresa'],
            ['key' => 'sucursal',                 'label' => 'Sucursal'],
            ['key' => 'estado',                   'label' => 'Estado'],
        ],
        'totales' => [],
    ],

    'arqueo_caja' => [
        'titulo'    => 'Listado de Arqueos de Caja',
        'cache_ttl' => 300,
        'columnas'  => [
            ['key' => 'id',          'label' => 'Código'],
            ['key' => 'fecha',       'label' => 'Fecha'],
            ['key' => 'tipo_arqueo', 'label' => 'Tipo Arqueo'],
            ['key' => 'caja',        'label' => 'Caja'],
            ['key' => 'funcionario', 'label' => 'Encargado'],
            ['key' => 'empresa',     'label' => 'Empresa'],
            ['key' => 'sucursal',    'label' => 'Sucursal'],
            ['key' => 'estado',      'label' => 'Estado'],
        ],
        'totales' => [],
    ],

    'recaudaciones' => [
        'titulo'    => 'Listado de Recaudaciones a Depositar',
        'cache_ttl' => 300,
        'columnas'  => [
            ['key' => 'id',          'label' => 'Código'],
            ['key' => 'fecha',       'label' => 'Fecha'],
            ['key' => 'met_pago',    'label' => 'Método de Pago'],
            ['key' => 'caja',        'label' => 'Caja'],
            ['key' => 'funcionario', 'label' => 'Encargado'],
            ['key' => 'empresa',     'label' => 'Empresa'],
            ['key' => 'sucursal',    'label' => 'Sucursal'],
            ['key' => 'estado',      'label' => 'Estado'],
        ],
        'totales' => [],
    ],

    'libro_ventas' => [
        'titulo'    => 'Libro de Ventas',
        'cache_ttl' => 900,
        'columnas'  => [
            ['key' => 'id',             'label' => 'Código'],
            ['key' => 'fecha',          'label' => 'Fecha'],
            ['key' => 'cliente',        'label' => 'Cliente'],
            ['key' => 'ruc',            'label' => 'RUC'],
            ['key' => 'condicion_pago', 'label' => 'Condición'],
            ['key' => 'impuesto',       'label' => 'Tipo Impuesto'],
            ['key' => 'monto',          'label' => 'Monto'],
            ['key' => 'cuota',          'label' => 'Cuota'],
        ],
        'totales' => [
            ['label' => 'total_monto', 'display' => 'Total Monto'],
            ['label' => 'registros',   'display' => 'Registros'],
        ],
    ],

];
