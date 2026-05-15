<?php

return [

    'pedidos' => [
        'titulo'    => 'Listado de Pedidos',
        'cache_ttl' => 300,
        'columnas'  => [
            ['key' => 'id',            'label' => 'Código'],
            ['key' => 'empresa',       'label' => 'Empresa'],
            ['key' => 'sucursal',      'label' => 'Sucursal'],
            ['key' => 'fecha',         'label' => 'Fecha'],
            ['key' => 'entrega',       'label' => 'Entrega'],
            ['key' => 'observaciones', 'label' => 'Observaciones'],
            ['key' => 'funcionario',   'label' => 'Encargado'],
            ['key' => 'estado',        'label' => 'Estado'],
        ],
        'totales' => [],
    ],

    'presupuestos' => [
        'titulo'    => 'Listado de Presupuestos',
        'cache_ttl' => 300,
        'columnas'  => [
            ['key' => 'id',            'label' => 'Código'],
            ['key' => 'empresa',       'label' => 'Empresa'],
            ['key' => 'sucursal',      'label' => 'Sucursal'],
            ['key' => 'fecha',         'label' => 'Fecha'],
            ['key' => 'entrega',       'label' => 'Plazo de Entrega'],
            ['key' => 'observaciones', 'label' => 'Observaciones'],
            ['key' => 'proveedor',     'label' => 'Proveedor'],
            ['key' => 'ruc',           'label' => 'RUC'],
            ['key' => 'funcionario',   'label' => 'Encargado'],
            ['key' => 'estado',        'label' => 'Estado'],
            ['key' => 'pedidos',       'label' => 'Pedidos'],
        ],
        'totales' => [],
    ],

    'ordenes_compras' => [
        'titulo'    => 'Listado de Órdenes de Compra',
        'cache_ttl' => 300,
        'columnas'  => [
            ['key' => 'id',             'label' => 'Código'],
            ['key' => 'fecha',          'label' => 'Fecha'],
            ['key' => 'proveedor',      'label' => 'Proveedor'],
            ['key' => 'ruc',            'label' => 'RUC'],
            ['key' => 'entrega',        'label' => 'Vencimiento'],
            ['key' => 'estado',         'label' => 'Estado'],
            ['key' => 'condicion_pago', 'label' => 'Condición'],
            ['key' => 'cuotas',         'label' => 'Cuotas'],
            ['key' => 'funcionario',    'label' => 'Encargado'],
            ['key' => 'empresa',        'label' => 'Empresa'],
            ['key' => 'sucursal',       'label' => 'Sucursal'],
            ['key' => 'presupuesto',    'label' => 'Presupuesto'],
        ],
        'totales' => [],
    ],

    'compras' => [
        'titulo'    => 'Listado de Compras',
        'cache_ttl' => 600,
        'columnas'  => [
            ['key' => 'id',             'label' => 'Código'],
            ['key' => 'empresa',        'label' => 'Empresa'],
            ['key' => 'sucursal',       'label' => 'Sucursal'],
            ['key' => 'fecha',          'label' => 'Fecha'],
            ['key' => 'entrega',        'label' => 'Vencimiento'],
            ['key' => 'proveedor',      'label' => 'Proveedor'],
            ['key' => 'ruc',            'label' => 'RUC'],
            ['key' => 'condicion_pago', 'label' => 'Condición'],
            ['key' => 'cuotas',         'label' => 'Cuotas'],
            ['key' => 'funcionario',    'label' => 'Encargado'],
            ['key' => 'estado',         'label' => 'Estado'],
            ['key' => 'ordencompra',    'label' => 'Orden de Compra'],
        ],
        'totales' => [],
    ],

    'libro_compras' => [
        'titulo'    => 'Libro de Compras',
        'cache_ttl' => 900,
        'columnas'  => [
            ['key' => 'id',             'label' => 'Código'],
            ['key' => 'fecha',          'label' => 'Fecha'],
            ['key' => 'tipo_nota',      'label' => 'Tipo Nota'],
            ['key' => 'proveedor',      'label' => 'Proveedor'],
            ['key' => 'ruc',            'label' => 'RUC'],
            ['key' => 'condicion_pago', 'label' => 'Condición'],
            ['key' => 'impuesto',       'label' => 'Tipo de Impuesto'],
            ['key' => 'monto',          'label' => 'Monto'],
            ['key' => 'cuota',          'label' => 'Cuota'],
        ],
        'totales' => [
            ['label' => 'total_monto', 'display' => 'Total Monto'],
            ['label' => 'registros',   'display' => 'Registros'],
        ],
    ],

    'nota_remi_comp' => [
        'titulo'    => 'Listado de Notas de Remisión',
        'cache_ttl' => 300,
        'columnas'  => [
            ['key' => 'id',            'label' => 'Código'],
            ['key' => 'fecha',         'label' => 'Fecha'],
            ['key' => 'observaciones', 'label' => 'Observaciones'],
            ['key' => 'estado',        'label' => 'Estado'],
            ['key' => 'funcionario',   'label' => 'Encargado'],
            ['key' => 'empresa',       'label' => 'Empresa'],
            ['key' => 'sucursal',      'label' => 'Sucursal'],
        ],
        'totales' => [],
    ],

    'ajuste_inventario' => [
        'titulo'    => 'Listado de Ajustes de Inventario',
        'cache_ttl' => 300,
        'columnas'  => [
            ['key' => 'id',          'label' => 'Código'],
            ['key' => 'fecha',       'label' => 'Fecha'],
            ['key' => 'motivo',      'label' => 'Motivo'],
            ['key' => 'tipo',        'label' => 'Tipo'],
            ['key' => 'estado',      'label' => 'Estado'],
            ['key' => 'funcionario', 'label' => 'Encargado'],
            ['key' => 'empresa',     'label' => 'Empresa'],
            ['key' => 'sucursal',    'label' => 'Sucursal'],
        ],
        'totales' => [],
    ],

    'notas_compra' => [
        'titulo'    => 'Listado de Notas de Compra',
        'cache_ttl' => 300,
        'columnas'  => [
            ['key' => 'id',             'label' => 'Código'],
            ['key' => 'empresa',        'label' => 'Empresa'],
            ['key' => 'sucursal',       'label' => 'Sucursal'],
            ['key' => 'fecha',          'label' => 'Fecha'],
            ['key' => 'entrega',        'label' => 'Vencimiento'],
            ['key' => 'tipo',           'label' => 'Tipo'],
            ['key' => 'proveedor',      'label' => 'Proveedor'],
            ['key' => 'ruc',            'label' => 'RUC'],
            ['key' => 'condicion_pago', 'label' => 'Condición'],
            ['key' => 'cuotas',         'label' => 'Cuotas'],
            ['key' => 'funcionario',    'label' => 'Encargado'],
            ['key' => 'estado',         'label' => 'Estado'],
            ['key' => 'compra',         'label' => 'Compra'],
        ],
        'totales' => [],
    ],

];
