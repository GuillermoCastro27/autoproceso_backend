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
