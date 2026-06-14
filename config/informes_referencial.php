<?php

return [

    // ── COMPRAS ──────────────────────────────────────────────────────────────

    'proveedor' => [
        'grupo'     => 'compras',
        'titulo'    => 'Proveedores',
        'estado_col'=> 'prov_estado',
        'tipo_col'  => null,
        'columnas'  => [
            ['key' => 'id',       'label' => 'Código'],
            ['key' => 'nombre',   'label' => 'Razón Social'],
            ['key' => 'ruc',      'label' => 'RUC'],
            ['key' => 'telefono', 'label' => 'Teléfono'],
            ['key' => 'correo',   'label' => 'Correo'],
            ['key' => 'estado',   'label' => 'Estado'],
        ],
    ],

    'items' => [
        'grupo'     => 'compras',
        'titulo'    => 'Ítems / Productos',
        'estado_col'=> 'item_estado',
        'tipo_col'  => null,
        'columnas'  => [
            ['key' => 'id',       'label' => 'Código'],
            ['key' => 'nombre',   'label' => 'Descripción'],
            ['key' => 'costo',    'label' => 'Costo'],
            ['key' => 'precio',   'label' => 'Precio'],
            ['key' => 'tipo',     'label' => 'Tipo Ítem'],
            ['key' => 'impuesto', 'label' => 'Tipo Impuesto'],
            ['key' => 'marcas',   'label' => 'Marcas'],
            ['key' => 'modelos',  'label' => 'Modelos'],
            ['key' => 'estado',   'label' => 'Estado'],
        ],
    ],

    'motivo_ajuste' => [
        'grupo'     => 'compras',
        'titulo'    => 'Motivos de Ajuste',
        'estado_col'=> 'estado',
        'tipo_col'  => null,
        'columnas'  => [
            ['key' => 'id',     'label' => 'Código'],
            ['key' => 'nombre', 'label' => 'Descripción'],
            ['key' => 'estado', 'label' => 'Estado'],
        ],
    ],

    'tipo_item' => [
        'grupo'     => 'compras',
        'titulo'    => 'Tipos de Ítem',
        'estado_col'=> 'tipo_estado',
        'tipo_col'  => null,
        'columnas'  => [
            ['key' => 'id',     'label' => 'Código'],
            ['key' => 'nombre', 'label' => 'Nombre'],
            ['key' => 'estado', 'label' => 'Estado'],
        ],
    ],

    'tipo_impuesto' => [
        'grupo'     => 'compras',
        'titulo'    => 'Tipos de Impuesto',
        'estado_col'=> 'tip_imp_estado',
        'tipo_col'  => null,
        'columnas'  => [
            ['key' => 'id',     'label' => 'Código'],
            ['key' => 'nombre', 'label' => 'Nombre'],
            ['key' => 'estado', 'label' => 'Estado'],
        ],
    ],

    'marca' => [
        'grupo'     => 'compras',
        'titulo'    => 'Marcas',
        'estado_col'=> 'marc_estado',
        'tipo_col'  => null,
        'columnas'  => [
            ['key' => 'id',     'label' => 'Código'],
            ['key' => 'nombre', 'label' => 'Nombre'],
            ['key' => 'estado', 'label' => 'Estado'],
        ],
    ],

    'modelo' => [
        'grupo'     => 'compras',
        'titulo'    => 'Modelos',
        'estado_col'=> 'modelo_estado',
        'tipo_col'  => 'modelo_tipo',
        'columnas'  => [
            ['key' => 'id',     'label' => 'Código'],
            ['key' => 'nombre', 'label' => 'Nombre'],
            ['key' => 'tipo',   'label' => 'Tipo'],
            ['key' => 'marca',  'label' => 'Marca'],
            ['key' => 'estado', 'label' => 'Estado'],
        ],
    ],

    // ── SERVICIO ─────────────────────────────────────────────────────────────

    'tipo_servicio' => [
        'grupo'     => 'servicio',
        'titulo'    => 'Tipos de Servicio',
        'estado_col'=> 'tipo_serv_estado',
        'tipo_col'  => null,
        'columnas'  => [
            ['key' => 'id',     'label' => 'Código'],
            ['key' => 'nombre', 'label' => 'Nombre'],
            ['key' => 'estado', 'label' => 'Estado'],
        ],
    ],

    'tipo_promociones' => [
        'grupo'     => 'servicio',
        'titulo'    => 'Tipos de Promociones',
        'estado_col'=> 'tipo_prom_estado',
        'tipo_col'  => null,
        'columnas'  => [
            ['key' => 'id',           'label' => 'Código'],
            ['key' => 'nombre',       'label' => 'Nombre'],
            ['key' => 'modo',         'label' => 'Modo'],
            ['key' => 'valor',        'label' => 'Valor'],
            ['key' => 'fecha_inicio', 'label' => 'Desde'],
            ['key' => 'fecha_fin',    'label' => 'Hasta'],
            ['key' => 'estado',       'label' => 'Estado'],
        ],
    ],

    'tipo_descuentos' => [
        'grupo'     => 'servicio',
        'titulo'    => 'Tipos de Descuentos',
        'estado_col'=> 'tipo_desc_estado',
        'tipo_col'  => null,
        'columnas'  => [
            ['key' => 'id',           'label' => 'Código'],
            ['key' => 'nombre',       'label' => 'Nombre'],
            ['key' => 'descripcion',  'label' => 'Descripción'],
            ['key' => 'fecha_inicio', 'label' => 'Desde'],
            ['key' => 'fecha_fin',    'label' => 'Hasta'],
            ['key' => 'estado',       'label' => 'Estado'],
        ],
    ],

    'tipo_diagnostico' => [
        'grupo'     => 'servicio',
        'titulo'    => 'Tipos de Diagnóstico',
        'estado_col'=> 'tipo_diag_estado',
        'tipo_col'  => null,
        'columnas'  => [
            ['key' => 'id',          'label' => 'Código'],
            ['key' => 'nombre',      'label' => 'Nombre'],
            ['key' => 'descripcion', 'label' => 'Descripción'],
            ['key' => 'estado',      'label' => 'Estado'],
        ],
    ],

    'equipo_trabajo' => [
        'grupo'     => 'servicio',
        'titulo'    => 'Equipos de Trabajo',
        'estado_col'=> 'equipo_estado',
        'tipo_col'  => null,
        'columnas'  => [
            ['key' => 'id',          'label' => 'Código'],
            ['key' => 'nombre',      'label' => 'Nombre'],
            ['key' => 'descripcion', 'label' => 'Descripción'],
            ['key' => 'categoria',   'label' => 'Categoría'],
            ['key' => 'estado',      'label' => 'Estado'],
        ],
    ],

    'tipo_vehiculo' => [
        'grupo'     => 'servicio',
        'titulo'    => 'Tipos de Vehículo',
        'estado_col'=> 'tip_veh_estado',
        'tipo_col'  => null,
        'columnas'  => [
            ['key' => 'id',     'label' => 'Código'],
            ['key' => 'nombre', 'label' => 'Nombre'],
            ['key' => 'anio',   'label' => 'Año'],
            ['key' => 'color',  'label' => 'Color'],
            ['key' => 'marca',  'label' => 'Marca'],
            ['key' => 'modelo', 'label' => 'Modelo'],
            ['key' => 'estado', 'label' => 'Estado'],
        ],
    ],

    'tipo_contrato' => [
        'grupo'     => 'servicio',
        'titulo'    => 'Tipos de Contrato',
        'estado_col'=> 'tip_con_estado',
        'tipo_col'  => null,
        'columnas'  => [
            ['key' => 'id',     'label' => 'Código'],
            ['key' => 'nombre', 'label' => 'Nombre'],
            ['key' => 'objeto', 'label' => 'Objeto'],
            ['key' => 'estado', 'label' => 'Estado'],
        ],
    ],

    // ── VENTAS / COBROS ───────────────────────────────────────────────────────

    'clientes' => [
        'grupo'     => 'ventas',
        'titulo'    => 'Clientes',
        'estado_col'=> 'cli_estado',
        'tipo_col'  => null,
        'columnas'  => [
            ['key' => 'id',           'label' => 'Código'],
            ['key' => 'nombre',       'label' => 'Nombre Completo'],
            ['key' => 'ruc',          'label' => 'RUC'],
            ['key' => 'tipo_persona', 'label' => 'Tipo Persona'],
            ['key' => 'telefono',     'label' => 'Teléfono'],
            ['key' => 'correo',       'label' => 'Correo'],
            ['key' => 'estado',       'label' => 'Estado'],
        ],
    ],

    'caja' => [
        'grupo'     => 'ventas',
        'titulo'    => 'Cajas',
        'estado_col'=> 'caja_estado',
        'tipo_col'  => null,
        'columnas'  => [
            ['key' => 'id',       'label' => 'Código'],
            ['key' => 'nombre',   'label' => 'Descripción'],
            ['key' => 'sucursal', 'label' => 'Sucursal'],
            ['key' => 'estado',   'label' => 'Estado'],
        ],
    ],

    'entidad_emisora' => [
        'grupo'     => 'ventas',
        'titulo'    => 'Entidades Emisoras',
        'estado_col'=> 'ent_emis_estado',
        'tipo_col'  => null,
        'columnas'  => [
            ['key' => 'id',       'label' => 'Código'],
            ['key' => 'nombre',   'label' => 'Nombre'],
            ['key' => 'telefono', 'label' => 'Teléfono'],
            ['key' => 'correo',   'label' => 'Correo'],
            ['key' => 'estado',   'label' => 'Estado'],
        ],
    ],

    'marca_tarjeta' => [
        'grupo'     => 'ventas',
        'titulo'    => 'Marcas de Tarjeta',
        'estado_col'=> 'marca_estado',
        'tipo_col'  => null,
        'columnas'  => [
            ['key' => 'id',     'label' => 'Código'],
            ['key' => 'nombre', 'label' => 'Nombre'],
            ['key' => 'estado', 'label' => 'Estado'],
        ],
    ],

    'forma_cobro' => [
        'grupo'     => 'ventas',
        'titulo'    => 'Formas de Cobro',
        'estado_col'=> 'for_cob_estado',
        'tipo_col'  => null,
        'columnas'  => [
            ['key' => 'id',     'label' => 'Código'],
            ['key' => 'nombre', 'label' => 'Descripción'],
            ['key' => 'estado', 'label' => 'Estado'],
        ],
    ],

    'entidad_adherida' => [
        'grupo'     => 'ventas',
        'titulo'    => 'Entidades Adheridas',
        'estado_col'=> 'ent_adh_estado',
        'tipo_col'  => null,
        'columnas'  => [
            ['key' => 'id',      'label' => 'Código'],
            ['key' => 'nombre',  'label' => 'Nombre'],
            ['key' => 'emisora', 'label' => 'Entidad Emisora'],
            ['key' => 'tarjeta', 'label' => 'Marca Tarjeta'],
            ['key' => 'estado',  'label' => 'Estado'],
        ],
    ],

    'tipo_comprobante' => [
        'grupo'     => 'ventas',
        'titulo'    => 'Tipos de Comprobante',
        'estado_col'=> null,
        'tipo_col'  => null,
        'columnas'  => [
            ['key' => 'id',     'label' => 'Código'],
            ['key' => 'nombre', 'label' => 'Nombre'],
        ],
    ],

    'timbrado' => [
        'grupo'     => 'ventas',
        'titulo'    => 'Timbrados',
        'estado_col'=> 'tim_estado',
        'tipo_col'  => null,
        'columnas'  => [
            ['key' => 'id',               'label' => 'Código'],
            ['key' => 'numero',           'label' => 'Nro. Timbrado'],
            ['key' => 'establecimiento',  'label' => 'Establecimiento'],
            ['key' => 'punto_expedicion', 'label' => 'Pto. Expedición'],
            ['key' => 'fecha_inicio',     'label' => 'Fecha Inicio'],
            ['key' => 'fecha_fin',        'label' => 'Fecha Fin'],
            ['key' => 'nro_actual',       'label' => 'Nro. Actual'],
            ['key' => 'nro_hasta',        'label' => 'Nro. Hasta'],
            ['key' => 'empresa',          'label' => 'Empresa'],
            ['key' => 'sucursal',         'label' => 'Sucursal'],
            ['key' => 'estado',           'label' => 'Estado'],
        ],
    ],

    // ── VARIOS ────────────────────────────────────────────────────────────────

    'funcionario' => [
        'grupo'     => 'varios',
        'titulo'    => 'Funcionarios',
        'estado_col'=> 'fun_estado',
        'tipo_col'  => null,
        'columnas'  => [
            ['key' => 'id',       'label' => 'Código'],
            ['key' => 'nombre',   'label' => 'Nombre Completo'],
            ['key' => 'ci',       'label' => 'Cédula'],
            ['key' => 'correo',   'label' => 'Correo'],
            ['key' => 'telefono', 'label' => 'Teléfono'],
            ['key' => 'estado',   'label' => 'Estado'],
        ],
    ],

    'ciudades' => [
        'grupo'     => 'varios',
        'titulo'    => 'Ciudades',
        'estado_col'=> null,
        'tipo_col'  => null,
        'columnas'  => [
            ['key' => 'id',     'label' => 'Código'],
            ['key' => 'nombre', 'label' => 'Ciudad'],
            ['key' => 'pais',   'label' => 'País'],
        ],
    ],

    'paises' => [
        'grupo'     => 'varios',
        'titulo'    => 'Países',
        'estado_col'=> null,
        'tipo_col'  => null,
        'columnas'  => [
            ['key' => 'id',     'label' => 'Código'],
            ['key' => 'nombre', 'label' => 'País'],
        ],
    ],

    'nacionalidad' => [
        'grupo'     => 'varios',
        'titulo'    => 'Nacionalidades',
        'estado_col'=> null,
        'tipo_col'  => null,
        'columnas'  => [
            ['key' => 'id',     'label' => 'Código'],
            ['key' => 'nombre', 'label' => 'Nacionalidad'],
        ],
    ],

    'empresa' => [
        'grupo'     => 'varios',
        'titulo'    => 'Empresas',
        'estado_col'=> 'emp_estado',
        'tipo_col'  => null,
        'columnas'  => [
            ['key' => 'id',     'label' => 'Código'],
            ['key' => 'nombre', 'label' => 'Razón Social'],
            ['key' => 'ruc',    'label' => 'RUC'],
            ['key' => 'estado', 'label' => 'Estado'],
        ],
    ],

    'sucursal' => [
        'grupo'     => 'varios',
        'titulo'    => 'Sucursales',
        'estado_col'=> 'suc_estado',
        'tipo_col'  => null,
        'columnas'  => [
            ['key' => 'id',      'label' => 'Código'],
            ['key' => 'nombre',  'label' => 'Razón Social'],
            ['key' => 'empresa', 'label' => 'Empresa'],
            ['key' => 'estado',  'label' => 'Estado'],
        ],
    ],

    'deposito' => [
        'grupo'     => 'varios',
        'titulo'    => 'Depósitos',
        'estado_col'=> 'dep_estado',
        'tipo_col'  => null,
        'columnas'  => [
            ['key' => 'id',       'label' => 'Código'],
            ['key' => 'nombre',   'label' => 'Descripción'],
            ['key' => 'sucursal', 'label' => 'Sucursal'],
            ['key' => 'estado',   'label' => 'Estado'],
        ],
    ],

    // ── SEGURIDAD ─────────────────────────────────────────────────────────────

    'usuarios' => [
        'grupo'     => 'seguridad',
        'titulo'    => 'Usuarios del Sistema',
        'estado_col'=> null,
        'tipo_col'  => null,
        'columnas'  => [
            ['key' => 'id',      'label' => 'Código'],
            ['key' => 'nombre',  'label' => 'Nombre'],
            ['key' => 'login',   'label' => 'Login'],
            ['key' => 'email',   'label' => 'Correo'],
            ['key' => 'perfil',  'label' => 'Perfil'],
        ],
    ],

    'roles' => [
        'grupo'     => 'seguridad',
        'titulo'    => 'Perfiles / Roles',
        'estado_col'=> null,
        'tipo_col'  => null,
        'columnas'  => [
            ['key' => 'id',          'label' => 'Código'],
            ['key' => 'nombre',      'label' => 'Descripción'],
            ['key' => 'abreviatura', 'label' => 'Abreviatura'],
        ],
    ],

    'accesos' => [
        'grupo'     => 'seguridad',
        'titulo'    => 'Accesos (Perfil × Permiso × Módulo)',
        'estado_col'=> 'acc_estado',
        'tipo_col'  => null,
        'columnas'  => [
            ['key' => 'id',      'label' => 'Código'],
            ['key' => 'perfil',  'label' => 'Perfil'],
            ['key' => 'permiso', 'label' => 'Permiso'],
            ['key' => 'modulo',  'label' => 'Módulo'],
            ['key' => 'estado',  'label' => 'Estado'],
        ],
    ],

    'permisos' => [
        'grupo'     => 'seguridad',
        'titulo'    => 'Permisos',
        'estado_col'=> null,
        'tipo_col'  => null,
        'columnas'  => [
            ['key' => 'id',          'label' => 'Código'],
            ['key' => 'nombre',      'label' => 'Permiso'],
            ['key' => 'descripcion', 'label' => 'Descripción'],
        ],
    ],

    'modulos' => [
        'grupo'     => 'seguridad',
        'titulo'    => 'Módulos del Sistema',
        'estado_col'=> 'mod_estado',
        'tipo_col'  => null,
        'columnas'  => [
            ['key' => 'id',          'label' => 'Código'],
            ['key' => 'nombre',      'label' => 'Módulo'],
            ['key' => 'descripcion', 'label' => 'Descripción'],
            ['key' => 'estado',      'label' => 'Estado'],
        ],
    ],

];
