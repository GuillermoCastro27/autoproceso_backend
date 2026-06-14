<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InformeGerencialReferencialController extends Controller
{
    private array $tableMap = [
        'proveedor'        => 'proveedores',
        'items'            => 'items',
        'motivo_ajuste'    => 'motivo_ajuste',
        'tipo_item'        => 'tipos',
        'tipo_impuesto'    => 'tipo_impuesto',
        'marca'            => 'marca',
        'modelo'           => 'modelo',
        'tipo_servicio'    => 'tipo_servicio',
        'tipo_promociones' => 'tipo_promociones',
        'tipo_descuentos'  => 'tipo_descuentos',
        'tipo_diagnostico' => 'tipo_diagnostico',
        'equipo_trabajo'   => 'equipo_trabajo',
        'tipo_vehiculo'    => 'tipo_vehiculo',
        'tipo_contrato'    => 'tipo_contrato',
        'clientes'         => 'clientes',
        'caja'             => 'caja',
        'entidad_emisora'  => 'entidad_emisora',
        'marca_tarjeta'    => 'marca_tarjeta',
        'forma_cobro'      => 'forma_cobro',
        'entidad_adherida' => 'entidad_adherida',
        'tipo_comprobante' => 'tipo_comprobante',
        'timbrado'         => 'timbrado',
        'funcionario'      => 'funcionario',
        'ciudades'         => 'ciudades',
        'paises'           => 'paises',
        'nacionalidad'     => 'nacionalidad',
        'empresa'          => 'empresa',
        'sucursal'         => 'sucursal',
        'deposito'         => 'deposito',
        'usuarios'         => 'users',
        'roles'            => 'perfiles',
        'accesos'          => 'accesos',
        'permisos'         => 'permisos',
        'modulos'          => 'modulos',
    ];

    // ── LISTADO ───────────────────────────────────────────────────────────────

    public function index(Request $r)
    {
        $r->validate(['tipo' => 'required|string']);
        $tipo   = $r->input('tipo');
        $config = config("informes_referencial.{$tipo}");

        if (!$config) {
            abort(422, "Tipo referencial no válido: {$tipo}");
        }

        $start    = max(0, (int)($r->input('start', 0)));
        $length   = min(500, max(1, (int)($r->input('length', 500))));
        $estado   = trim($r->input('estado', ''));
        $tipoFilt = trim($r->input('tipo_filtro', ''));

        $baseSql = $this->getSqlData($tipo);
        $params  = [];
        $conds   = [];

        if ($estado !== '' && ($config['estado_col'] ?? null)) {
            $conds[]          = "t.estado = :estado";
            $params['estado'] = $estado;
        }
        if ($tipoFilt !== '' && ($config['tipo_col'] ?? null)) {
            $conds[]               = "LOWER(t.tipo::text) LIKE LOWER(:tipo_filtro)";
            $params['tipo_filtro'] = '%' . $tipoFilt . '%';
        }

        if ($conds) {
            $where    = 'WHERE ' . implode(' AND ', $conds);
            $dataSql  = "SELECT * FROM ({$baseSql}) t {$where} LIMIT {$length} OFFSET {$start}";
            $countSql = "SELECT COUNT(*) AS total FROM ({$baseSql}) t {$where}";
        } else {
            $dataSql  = "{$baseSql} LIMIT {$length} OFFSET {$start}";
            $countSql = $this->getSqlCount($tipo);
        }

        $data  = DB::select($dataSql, $params);
        $total = (int)(DB::selectOne($countSql, $params)->total ?? 0);

        return response()->json([
            'draw'            => (int)($r->input('draw', 1)),
            'recordsTotal'    => $total,
            'recordsFiltered' => $total,
            'data'            => $data,
            'columnas'        => $config['columnas'],
            'titulo'          => $config['titulo'],
            'estado_col'      => $config['estado_col'] ?? null,
            'tipo_col'        => $config['tipo_col'] ?? null,
        ]);
    }

    // ── ESTADÍSTICAS ──────────────────────────────────────────────────────────

    public function estadisticas(Request $r)
    {
        $r->validate(['tipo' => 'required|string']);
        $tipo = $r->input('tipo');

        if (!config("informes_referencial.{$tipo}")) {
            abort(422, "Tipo referencial no válido: {$tipo}");
        }

        $secciones = match ($tipo) {
            'proveedor'        => $this->statsProveedor(),
            'items'            => $this->statsItems(),
            'marca'            => $this->statsMarca(),
            'tipo_promociones' => $this->statsTipoPromociones(),
            'tipo_descuentos'  => $this->statsTipoDescuentos(),
            'tipo_diagnostico' => $this->statsTipoDiagnostico(),
            'equipo_trabajo'   => $this->statsEquipoTrabajo(),
            'tipo_contrato'    => $this->statsTipoContrato(),
            'tipo_vehiculo'    => $this->statsTipoVehiculo(),
            'clientes'         => $this->statsClientes(),
            'caja'             => $this->statsCaja(),
            'timbrado'         => $this->statsTimbrado(),
            'funcionario'      => $this->statsFuncionario(),
            'sucursal'         => $this->statsSucursal(),
            'modulos'          => $this->statsModulos(),
            'usuarios'         => $this->statsUsuarios(),
            'accesos'          => $this->statsAccesos(),
            default            => $this->statsGenerico($tipo),
        };

        return response()->json(['secciones' => $secciones, 'catalogos' => []]);
    }

    // ── SQL DE DATOS ──────────────────────────────────────────────────────────

    private function getSqlData(string $tipo): string
    {
        return match ($tipo) {

            'proveedor' => "
                SELECT id, prov_razonsocial AS nombre, prov_ruc AS ruc,
                       COALESCE(prov_telefono, '') AS telefono,
                       COALESCE(prov_correo, '') AS correo,
                       prov_estado AS estado
                FROM proveedores WHERE deleted_at IS NULL
                ORDER BY prov_razonsocial",

            'items' => "
                SELECT i.id,
                       i.item_decripcion AS nombre,
                       COALESCE(i.item_costo::varchar, '0') AS costo,
                       COALESCE(i.item_precio::varchar, '0') AS precio,
                       COALESCE(ti.tipo_descripcion, 'Sin tipo') AS tipo,
                       COALESCE(timp.tip_imp_nom, 'Sin impuesto') AS impuesto,
                       COALESCE(STRING_AGG(DISTINCT ma.marc_nom,    ', '), 'Sin marca') AS marcas,
                       COALESCE(STRING_AGG(DISTINCT mo.modelo_nom, ', '), 'Sin modelo') AS modelos,
                       i.item_estado AS estado
                FROM items i
                LEFT JOIN tipos ti           ON ti.id   = i.tipo_id
                LEFT JOIN tipo_impuesto timp  ON timp.id = i.tipo_impuesto_id
                LEFT JOIN item_marca  im     ON im.item_id  = i.id
                LEFT JOIN marca       ma     ON ma.id       = im.marca_id
                LEFT JOIN item_modelo imo    ON imo.item_id = i.id
                LEFT JOIN modelo      mo     ON mo.id       = imo.modelo_id
                WHERE i.deleted_at IS NULL
                GROUP BY i.id, i.item_decripcion, i.item_costo, i.item_precio,
                         ti.tipo_descripcion, timp.tip_imp_nom, i.item_estado
                ORDER BY i.item_decripcion",

            'motivo_ajuste' => "
                SELECT id, descripcion AS nombre, estado
                FROM motivo_ajuste ORDER BY descripcion",

            'tipo_item' => "
                SELECT id, tipo_descripcion AS nombre, tipo_estado AS estado
                FROM tipos ORDER BY tipo_descripcion",

            'tipo_impuesto' => "
                SELECT id, tip_imp_nom AS nombre, tip_imp_estado AS estado
                FROM tipo_impuesto ORDER BY tip_imp_nom",

            'marca' => "
                SELECT id, marc_nom AS nombre, marc_estado AS estado
                FROM marca ORDER BY marc_nom",

            'modelo' => "
                SELECT mo.id,
                       mo.modelo_nom  AS nombre,
                       COALESCE(mo.modelo_tipo, 'Sin tipo') AS tipo,
                       COALESCE(ma.marc_nom, 'Sin marca')   AS marca,
                       mo.modelo_estado                     AS estado
                FROM modelo mo
                LEFT JOIN marca ma ON ma.id = mo.marca_id
                ORDER BY mo.modelo_nom",

            'tipo_servicio' => "
                SELECT id, tipo_ser_nombre AS nombre, tipo_serv_estado AS estado
                FROM tipo_servicio ORDER BY tipo_ser_nombre",

            'tipo_promociones' => "
                SELECT id, tipo_prom_nombre AS nombre,
                       COALESCE(tipo_prom_modo, '') AS modo,
                       COALESCE(tipo_prom_valor::varchar, '') AS valor,
                       COALESCE(TO_CHAR(tipo_prom_fechaInicio, 'dd/mm/yyyy'), '') AS fecha_inicio,
                       COALESCE(TO_CHAR(tipo_prom_fechaFin, 'dd/mm/yyyy'), '') AS fecha_fin,
                       tipo_prom_estado AS estado
                FROM tipo_promociones ORDER BY tipo_prom_nombre",

            'tipo_descuentos' => "
                SELECT id, tipo_desc_nombre AS nombre,
                       COALESCE(tipo_desc_descrip, '') AS descripcion,
                       COALESCE(TO_CHAR(tipo_desc_fechaInicio, 'dd/mm/yyyy'), '') AS fecha_inicio,
                       COALESCE(TO_CHAR(tipo_desc_fechaFin, 'dd/mm/yyyy'), '') AS fecha_fin,
                       tipo_desc_estado AS estado
                FROM tipo_descuentos ORDER BY tipo_desc_nombre",

            'tipo_diagnostico' => "
                SELECT id, tipo_diag_nombre AS nombre,
                       COALESCE(tipo_diag_descrip, '') AS descripcion,
                       tipo_diag_estado AS estado
                FROM tipo_diagnostico ORDER BY tipo_diag_nombre",

            'equipo_trabajo' => "
                SELECT id, equipo_nombre AS nombre,
                       COALESCE(equipo_descripcion, '') AS descripcion,
                       COALESCE(equipo_categoria, '') AS categoria,
                       equipo_estado AS estado
                FROM equipo_trabajo ORDER BY equipo_nombre",

            'tipo_vehiculo' => "
                SELECT tv.id, tv.tip_veh_nombre AS nombre,
                       COALESCE(tv.tv_anio::varchar, '') AS anio,
                       COALESCE(tv.tv_color, '') AS color,
                       COALESCE(ma.marc_nom, 'Sin marca')    AS marca,
                       COALESCE(mo.modelo_nom, 'Sin modelo') AS modelo,
                       tv.tip_veh_estado                     AS estado
                FROM tipo_vehiculo tv
                LEFT JOIN marca ma  ON ma.id = tv.marca_id
                LEFT JOIN modelo mo ON mo.id = tv.modelo_id
                ORDER BY tv.tip_veh_nombre",

            'tipo_contrato' => "
                SELECT id, tip_con_nombre AS nombre,
                       COALESCE(tip_con_objeto, '') AS objeto,
                       tip_con_estado AS estado
                FROM tipo_contrato ORDER BY tip_con_nombre",

            'clientes' => "
                SELECT id,
                       cli_nombre || ' ' || cli_apellido AS nombre,
                       COALESCE(cli_ruc, 'S/RUC') AS ruc,
                       COALESCE(cli_tipo_persona, '') AS tipo_persona,
                       COALESCE(cli_telefono, '') AS telefono,
                       COALESCE(cli_correo, '') AS correo,
                       cli_estado AS estado
                FROM clientes WHERE deleted_at IS NULL
                ORDER BY cli_nombre",

            'caja' => "
                SELECT c.id, c.caja_descripcion AS nombre,
                       COALESCE(s.suc_razon_social, 'Sin sucursal') AS sucursal,
                       c.caja_estado AS estado
                FROM caja c
                LEFT JOIN sucursal s ON s.id = c.sucursal_id
                ORDER BY c.caja_descripcion",

            'entidad_emisora' => "
                SELECT id, ent_emis_nombre AS nombre,
                       COALESCE(ent_emis_telefono, '') AS telefono,
                       COALESCE(ent_emis_email, '') AS correo,
                       ent_emis_estado AS estado
                FROM entidad_emisora ORDER BY ent_emis_nombre",

            'marca_tarjeta' => "
                SELECT id, marca_nombre AS nombre, marca_estado AS estado
                FROM marca_tarjeta ORDER BY marca_nombre",

            'forma_cobro' => "
                SELECT id, for_cob_descripcion AS nombre, for_cob_estado AS estado
                FROM forma_cobro ORDER BY for_cob_descripcion",

            'entidad_adherida' => "
                SELECT ea.id,
                       ea.ent_adh_nombre                             AS nombre,
                       COALESCE(ee.ent_emis_nombre, 'Sin emisora')   AS emisora,
                       COALESCE(mt.marca_nombre, 'Sin tarjeta')      AS tarjeta,
                       ea.ent_adh_estado                             AS estado
                FROM entidad_adherida ea
                LEFT JOIN entidad_emisora ee ON ee.id = ea.entidad_emisora_id
                LEFT JOIN marca_tarjeta   mt ON mt.id = ea.marca_tarjeta_id
                ORDER BY ea.ent_adh_nombre",

            'tipo_comprobante' => "
                SELECT id, tip_comp_nombre AS nombre
                FROM tipo_comprobante ORDER BY tip_comp_nombre",

            'timbrado' => "
                SELECT tb.id, tb.tim_numero AS numero,
                       tb.tim_establecimiento AS establecimiento,
                       tb.tim_punto_expedicion AS punto_expedicion,
                       TO_CHAR(tb.tim_fecha_inicio, 'dd/mm/yyyy') AS fecha_inicio,
                       TO_CHAR(tb.tim_fecha_fin, 'dd/mm/yyyy') AS fecha_fin,
                       tb.tim_nro_actual AS nro_actual,
                       tb.tim_nro_hasta  AS nro_hasta,
                       COALESCE(e.emp_razon_social, 'Sin empresa')   AS empresa,
                       COALESCE(s.suc_razon_social, 'Sin sucursal')  AS sucursal,
                       tb.tim_estado AS estado
                FROM timbrado tb
                LEFT JOIN empresa  e ON e.id = tb.empresa_id
                LEFT JOIN sucursal s ON s.id = tb.sucursal_id
                ORDER BY tb.tim_fecha_inicio DESC",

            'funcionario' => "
                SELECT id, fun_nom || ' ' || fun_apellido AS nombre,
                       fun_ci AS ci,
                       COALESCE(fun_correo, '') AS correo,
                       COALESCE(fun_telefono, '') AS telefono,
                       fun_estado AS estado
                FROM funcionario WHERE deleted_at IS NULL
                ORDER BY fun_nom",

            'ciudades' => "
                SELECT c.id, c.ciu_descripcion AS nombre,
                       COALESCE(p.pais_descrpcion, 'Sin país') AS pais
                FROM ciudades c
                LEFT JOIN paises p ON p.id = c.pais_id
                ORDER BY c.ciu_descripcion",

            'paises' => "
                SELECT id, pais_descrpcion AS nombre
                FROM paises ORDER BY pais_descrpcion",

            'nacionalidad' => "
                SELECT id, nac_nombre AS nombre
                FROM nacionalidad ORDER BY nac_nombre",

            'empresa' => "
                SELECT id, emp_razon_social AS nombre,
                       emp_ruc AS ruc,
                       emp_estado AS estado
                FROM empresa ORDER BY emp_razon_social",

            'sucursal' => "
                SELECT s.id, s.suc_razon_social AS nombre,
                       COALESCE(e.emp_razon_social, 'Sin empresa') AS empresa,
                       s.suc_estado AS estado
                FROM sucursal s
                LEFT JOIN empresa e ON e.id = s.empresa_id
                ORDER BY s.suc_razon_social",

            'deposito' => "
                SELECT d.id, d.dep_descripcion AS nombre,
                       COALESCE(s.suc_razon_social, 'Sin sucursal') AS sucursal,
                       d.dep_estado AS estado
                FROM deposito d
                LEFT JOIN sucursal s ON s.id = d.sucursal_id
                ORDER BY d.dep_descripcion",

            'usuarios' => "
                SELECT u.id, u.name AS nombre, u.login,
                       COALESCE(u.email, '') AS email,
                       COALESCE(pf.pref_descripcion, 'Sin perfil') AS perfil
                FROM users u
                LEFT JOIN perfiles pf ON pf.id = u.perfil_id
                ORDER BY u.name",

            'roles' => "
                SELECT id, pref_descripcion AS nombre,
                       COALESCE(pref_abreviatura, '') AS abreviatura
                FROM perfiles ORDER BY pref_descripcion",

            'accesos' => "
                SELECT a.id,
                       COALESCE(pf.pref_descripcion, 'Sin perfil') AS perfil,
                       COALESCE(p.per_nombre, 'Sin permiso') AS permiso,
                       COALESCE(m.mod_nombre, 'Sin módulo') AS modulo,
                       a.acc_estado AS estado
                FROM accesos a
                JOIN permisos p  ON p.id  = a.permiso_id
                JOIN perfiles pf ON pf.id = a.perfil_id
                LEFT JOIN modulos m ON m.id = a.mod_id
                ORDER BY pf.pref_descripcion, p.per_nombre",

            'permisos' => "
                SELECT id, per_nombre AS nombre,
                       COALESCE(per_descripcion, '') AS descripcion
                FROM permisos ORDER BY per_nombre",

            'modulos' => "
                SELECT id, mod_nombre AS nombre,
                       COALESCE(mod_descripcion, '') AS descripcion,
                       mod_estado AS estado
                FROM modulos ORDER BY mod_nombre",

            default => throw new \InvalidArgumentException("SQL no definido para: {$tipo}"),
        };
    }

    private function getSqlCount(string $tipo): string
    {
        $counts = [
            'proveedor'        => "SELECT COUNT(*) AS total FROM proveedores WHERE deleted_at IS NULL",
            'items'            => "SELECT COUNT(*) AS total FROM items WHERE deleted_at IS NULL",
            'motivo_ajuste'    => "SELECT COUNT(*) AS total FROM motivo_ajuste",
            'tipo_item'        => "SELECT COUNT(*) AS total FROM tipos",
            'tipo_impuesto'    => "SELECT COUNT(*) AS total FROM tipo_impuesto",
            'marca'            => "SELECT COUNT(*) AS total FROM marca",
            'modelo'           => "SELECT COUNT(*) AS total FROM modelo",
            'tipo_servicio'    => "SELECT COUNT(*) AS total FROM tipo_servicio",
            'tipo_promociones' => "SELECT COUNT(*) AS total FROM tipo_promociones",
            'tipo_descuentos'  => "SELECT COUNT(*) AS total FROM tipo_descuentos",
            'tipo_diagnostico' => "SELECT COUNT(*) AS total FROM tipo_diagnostico",
            'equipo_trabajo'   => "SELECT COUNT(*) AS total FROM equipo_trabajo",
            'tipo_vehiculo'    => "SELECT COUNT(*) AS total FROM tipo_vehiculo",
            'tipo_contrato'    => "SELECT COUNT(*) AS total FROM tipo_contrato",
            'clientes'         => "SELECT COUNT(*) AS total FROM clientes WHERE deleted_at IS NULL",
            'caja'             => "SELECT COUNT(*) AS total FROM caja",
            'entidad_emisora'  => "SELECT COUNT(*) AS total FROM entidad_emisora",
            'marca_tarjeta'    => "SELECT COUNT(*) AS total FROM marca_tarjeta",
            'forma_cobro'      => "SELECT COUNT(*) AS total FROM forma_cobro",
            'entidad_adherida' => "SELECT COUNT(*) AS total FROM entidad_adherida",
            'tipo_comprobante' => "SELECT COUNT(*) AS total FROM tipo_comprobante",
            'timbrado'         => "SELECT COUNT(*) AS total FROM timbrado",
            'funcionario'      => "SELECT COUNT(*) AS total FROM funcionario WHERE deleted_at IS NULL",
            'ciudades'         => "SELECT COUNT(*) AS total FROM ciudades",
            'paises'           => "SELECT COUNT(*) AS total FROM paises",
            'nacionalidad'     => "SELECT COUNT(*) AS total FROM nacionalidad",
            'empresa'          => "SELECT COUNT(*) AS total FROM empresa",
            'sucursal'         => "SELECT COUNT(*) AS total FROM sucursal",
            'deposito'         => "SELECT COUNT(*) AS total FROM deposito",
            'usuarios'         => "SELECT COUNT(*) AS total FROM users",
            'roles'            => "SELECT COUNT(*) AS total FROM perfiles",
            'accesos'          => "SELECT COUNT(*) AS total FROM accesos",
            'permisos'         => "SELECT COUNT(*) AS total FROM permisos",
            'modulos'          => "SELECT COUNT(*) AS total FROM modulos",
        ];

        return $counts[$tipo] ?? "SELECT 0 AS total";
    }

    // ── ESTADÍSTICAS — helpers ────────────────────────────────────────────────

    private function estadoPorTabla(string $tabla, string $colEstado, string $titulo): array
    {
        $data = DB::select("
            SELECT COALESCE({$colEstado}, 'Sin estado') AS estado, COUNT(*) AS cantidad
            FROM {$tabla}
            GROUP BY {$colEstado} ORDER BY cantidad DESC
        ");

        return [
            'id'           => 'est_' . $tabla,
            'titulo'       => $titulo,
            'tipo_grafico' => 'doughnut',
            'labels'       => array_map(fn($r) => $r->estado, $data),
            'datasets'     => [['label' => 'Cantidad', 'data' => array_map(fn($r) => (int)$r->cantidad, $data)]],
            'columnas'     => [['key' => 'estado', 'label' => 'Estado'], ['key' => 'cantidad', 'label' => 'Cantidad']],
            'tabla'        => $data,
        ];
    }

    private function totalRegistros(string $tipo, string $titulo): array
    {
        $tabla = $this->tableMap[$tipo] ?? $tipo;
        $total = (int)(DB::selectOne("SELECT COUNT(*) AS total FROM {$tabla}")->total ?? 0);

        return [
            'id'       => 'total_' . $tipo,
            'titulo'   => $titulo,
            'columnas' => [['key' => 'concepto', 'label' => 'Métrica'], ['key' => 'valor', 'label' => 'Cantidad']],
            'tabla'    => [(object)['concepto' => 'Total de registros', 'valor' => $total]],
        ];
    }

    private function statsGenerico(string $tipo): array
    {
        $config = config("informes_referencial.{$tipo}");
        return [$this->totalRegistros($tipo, 'Resumen: ' . ($config['titulo'] ?? $tipo))];
    }

    // ── ESTADÍSTICAS — por tipo ───────────────────────────────────────────────

    private function statsProveedor(): array
    {
        $porEstado = $this->estadoPorTabla('proveedores', 'prov_estado', 'Proveedores por Estado');

        $porPais = DB::select("
            SELECT COALESCE(p.pais_descrpcion, 'Sin país') AS pais, COUNT(*) AS cantidad
            FROM proveedores prov
            LEFT JOIN paises p ON p.id = prov.pais_id
            WHERE prov.deleted_at IS NULL
            GROUP BY p.pais_descrpcion ORDER BY cantidad DESC LIMIT 10
        ");

        return [
            $porEstado,
            [
                'id'           => 'prov_pais',
                'titulo'       => 'Proveedores por País',
                'tipo_grafico' => 'bar',
                'opciones'     => ['indexAxis' => 'y'],
                'labels'       => array_map(fn($r) => $r->pais, $porPais),
                'datasets'     => [['label' => 'Proveedores', 'data' => array_map(fn($r) => (int)$r->cantidad, $porPais), 'color' => '#2980b9']],
                'columnas'     => [['key' => 'pais', 'label' => 'País'], ['key' => 'cantidad', 'label' => 'Cantidad']],
                'tabla'        => $porPais,
            ],
        ];
    }

    private function statsItems(): array
    {
        $porEstado = $this->estadoPorTabla('items', 'item_estado', 'Ítems por Estado');

        $porTipo = DB::select("
            SELECT COALESCE(ti.tipo_descripcion, 'Sin tipo') AS tipo, COUNT(*) AS cantidad
            FROM items i
            LEFT JOIN tipos ti ON ti.id = i.tipo_id
            WHERE i.deleted_at IS NULL
            GROUP BY ti.tipo_descripcion ORDER BY cantidad DESC
        ");

        $porMarca = DB::select("
            SELECT COALESCE(ma.marc_nom, 'Sin marca') AS marca, COUNT(DISTINCT i.id) AS cantidad
            FROM items i
            LEFT JOIN item_marca im ON im.item_id = i.id
            LEFT JOIN marca ma      ON ma.id = im.marca_id
            WHERE i.deleted_at IS NULL
            GROUP BY ma.marc_nom ORDER BY cantidad DESC LIMIT 15
        ");

        return [
            $porEstado,
            [
                'id'           => 'items_tipo',
                'titulo'       => 'Ítems por Tipo',
                'tipo_grafico' => 'bar',
                'labels'       => array_map(fn($r) => $r->tipo, $porTipo),
                'datasets'     => [['label' => 'Ítems', 'data' => array_map(fn($r) => (int)$r->cantidad, $porTipo), 'color' => '#27ae60']],
                'columnas'     => [['key' => 'tipo', 'label' => 'Tipo'], ['key' => 'cantidad', 'label' => 'Cantidad']],
                'tabla'        => $porTipo,
            ],
            [
                'id'           => 'items_marca',
                'titulo'       => 'Ítems por Marca (Top 15)',
                'tipo_grafico' => 'horizontalBar',
                'labels'       => array_map(fn($r) => $r->marca, $porMarca),
                'datasets'     => [['label' => 'Ítems', 'data' => array_map(fn($r) => (int)$r->cantidad, $porMarca), 'color' => '#8e44ad']],
                'columnas'     => [['key' => 'marca', 'label' => 'Marca'], ['key' => 'cantidad', 'label' => 'Cantidad']],
                'tabla'        => $porMarca,
            ],
        ];
    }

    private function statsMarca(): array
    {
        $porModelo = DB::select("
            SELECT COALESCE(mo.modelo_nom, 'Sin modelo') AS modelo, COUNT(*) AS cantidad
            FROM modelo mo GROUP BY mo.modelo_nom ORDER BY cantidad DESC LIMIT 15
        ");

        return [
            $this->estadoPorTabla('marca', 'marc_estado', 'Marcas por Estado'),
            [
                'id'           => 'marca_modelos',
                'titulo'       => 'Modelos registrados (Top 15)',
                'tipo_grafico' => 'horizontalBar',
                'labels'       => array_map(fn($r) => $r->modelo, $porModelo),
                'datasets'     => [['label' => 'Modelos', 'data' => array_map(fn($r) => (int)$r->cantidad, $porModelo), 'color' => '#8e44ad']],
                'columnas'     => [['key' => 'modelo', 'label' => 'Modelo'], ['key' => 'cantidad', 'label' => 'Cantidad']],
                'tabla'        => $porModelo,
            ],
        ];
    }

    private function statsTipoPromociones(): array
    {
        $porEstado = $this->estadoPorTabla('tipo_promociones', 'tipo_prom_estado', 'Promociones por Estado');

        $porModo = DB::select("
            SELECT COALESCE(tipo_prom_modo, 'Sin modo') AS modo, COUNT(*) AS cantidad
            FROM tipo_promociones GROUP BY tipo_prom_modo ORDER BY cantidad DESC
        ");

        return [
            $porEstado,
            [
                'id'           => 'prom_modo',
                'titulo'       => 'Promociones por Modo',
                'tipo_grafico' => 'bar',
                'labels'       => array_map(fn($r) => $r->modo, $porModo),
                'datasets'     => [['label' => 'Promociones', 'data' => array_map(fn($r) => (int)$r->cantidad, $porModo), 'color' => '#e74c3c']],
                'columnas'     => [['key' => 'modo', 'label' => 'Modo'], ['key' => 'cantidad', 'label' => 'Cantidad']],
                'tabla'        => $porModo,
            ],
        ];
    }

    private function statsTipoDescuentos(): array
    {
        return [$this->estadoPorTabla('tipo_descuentos', 'tipo_desc_estado', 'Descuentos por Estado')];
    }

    private function statsTipoDiagnostico(): array
    {
        return [$this->estadoPorTabla('tipo_diagnostico', 'tipo_diag_estado', 'Diagnósticos por Estado')];
    }

    private function statsEquipoTrabajo(): array
    {
        $porCategoria = DB::select("
            SELECT COALESCE(equipo_categoria, 'Sin categoría') AS categoria, COUNT(*) AS cantidad
            FROM equipo_trabajo GROUP BY equipo_categoria ORDER BY cantidad DESC
        ");

        return [
            $this->estadoPorTabla('equipo_trabajo', 'equipo_estado', 'Equipos por Estado'),
            [
                'id'           => 'equipo_cat',
                'titulo'       => 'Equipos por Categoría',
                'tipo_grafico' => 'bar',
                'labels'       => array_map(fn($r) => $r->categoria, $porCategoria),
                'datasets'     => [['label' => 'Equipos', 'data' => array_map(fn($r) => (int)$r->cantidad, $porCategoria), 'color' => '#9b59b6']],
                'columnas'     => [['key' => 'categoria', 'label' => 'Categoría'], ['key' => 'cantidad', 'label' => 'Cantidad']],
                'tabla'        => $porCategoria,
            ],
        ];
    }

    private function statsTipoContrato(): array
    {
        return [$this->estadoPorTabla('tipo_contrato', 'tip_con_estado', 'Contratos por Estado')];
    }

    private function statsTipoVehiculo(): array
    {
        $porMarca = DB::select("
            SELECT COALESCE(m.marc_nom, 'Sin marca') AS marca, COUNT(*) AS cantidad
            FROM tipo_vehiculo tv
            LEFT JOIN marca m ON m.id = tv.marca_id
            GROUP BY m.marc_nom ORDER BY cantidad DESC LIMIT 10
        ");

        $porAnio = DB::select("
            SELECT COALESCE(tv_anio::varchar, 'Sin año') AS anio, COUNT(*) AS cantidad
            FROM tipo_vehiculo GROUP BY tv_anio ORDER BY tv_anio DESC LIMIT 10
        ");

        return [
            [
                'id'           => 'tveh_marca',
                'titulo'       => 'Vehículos por Marca',
                'tipo_grafico' => 'bar',
                'opciones'     => ['indexAxis' => 'y'],
                'labels'       => array_map(fn($r) => $r->marca, $porMarca),
                'datasets'     => [['label' => 'Vehículos', 'data' => array_map(fn($r) => (int)$r->cantidad, $porMarca), 'color' => '#1abc9c']],
                'columnas'     => [['key' => 'marca', 'label' => 'Marca'], ['key' => 'cantidad', 'label' => 'Cantidad']],
                'tabla'        => $porMarca,
            ],
            [
                'id'           => 'tveh_anio',
                'titulo'       => 'Vehículos por Año',
                'tipo_grafico' => 'bar',
                'labels'       => array_map(fn($r) => $r->anio, $porAnio),
                'datasets'     => [['label' => 'Vehículos', 'data' => array_map(fn($r) => (int)$r->cantidad, $porAnio), 'color' => '#d35400']],
                'columnas'     => [['key' => 'anio', 'label' => 'Año'], ['key' => 'cantidad', 'label' => 'Cantidad']],
                'tabla'        => $porAnio,
            ],
        ];
    }

    private function statsClientes(): array
    {
        $porTipo = DB::select("
            SELECT COALESCE(cli_tipo_persona, 'Sin tipo') AS tipo_persona, COUNT(*) AS cantidad
            FROM clientes WHERE deleted_at IS NULL
            GROUP BY cli_tipo_persona ORDER BY cantidad DESC
        ");

        return [
            $this->estadoPorTabla('clientes', 'cli_estado', 'Clientes por Estado'),
            [
                'id'           => 'cli_tipo',
                'titulo'       => 'Clientes por Tipo de Persona',
                'tipo_grafico' => 'doughnut',
                'labels'       => array_map(fn($r) => $r->tipo_persona, $porTipo),
                'datasets'     => [['label' => 'Clientes', 'data' => array_map(fn($r) => (int)$r->cantidad, $porTipo)]],
                'columnas'     => [['key' => 'tipo_persona', 'label' => 'Tipo Persona'], ['key' => 'cantidad', 'label' => 'Cantidad']],
                'tabla'        => $porTipo,
            ],
        ];
    }

    private function statsCaja(): array
    {
        $porSucursal = DB::select("
            SELECT COALESCE(s.suc_razon_social, 'Sin sucursal') AS sucursal, COUNT(*) AS cantidad
            FROM caja c
            LEFT JOIN sucursal s ON s.id = c.sucursal_id
            GROUP BY s.suc_razon_social ORDER BY cantidad DESC
        ");

        return [
            $this->estadoPorTabla('caja', 'caja_estado', 'Cajas por Estado'),
            [
                'id'           => 'caja_suc',
                'titulo'       => 'Cajas por Sucursal',
                'tipo_grafico' => 'bar',
                'labels'       => array_map(fn($r) => $r->sucursal, $porSucursal),
                'datasets'     => [['label' => 'Cajas', 'data' => array_map(fn($r) => (int)$r->cantidad, $porSucursal), 'color' => '#2980b9']],
                'columnas'     => [['key' => 'sucursal', 'label' => 'Sucursal'], ['key' => 'cantidad', 'label' => 'Cajas']],
                'tabla'        => $porSucursal,
            ],
        ];
    }

    private function statsTimbrado(): array
    {
        $porEstado = $this->estadoPorTabla('timbrado', 'tim_estado', 'Timbrados por Estado');

        $porEmpresa = DB::select("
            SELECT COALESCE(e.emp_razon_social, 'Sin empresa') AS empresa, COUNT(*) AS cantidad
            FROM timbrado t
            LEFT JOIN empresa e ON e.id = t.empresa_id
            GROUP BY e.emp_razon_social ORDER BY cantidad DESC
        ");

        return [
            $porEstado,
            [
                'id'           => 'tim_empresa',
                'titulo'       => 'Timbrados por Empresa',
                'tipo_grafico' => 'bar',
                'labels'       => array_map(fn($r) => $r->empresa, $porEmpresa),
                'datasets'     => [['label' => 'Timbrados', 'data' => array_map(fn($r) => (int)$r->cantidad, $porEmpresa), 'color' => '#16a085']],
                'columnas'     => [['key' => 'empresa', 'label' => 'Empresa'], ['key' => 'cantidad', 'label' => 'Timbrados']],
                'tabla'        => $porEmpresa,
            ],
        ];
    }

    private function statsFuncionario(): array
    {
        return [$this->estadoPorTabla('funcionario', 'fun_estado', 'Funcionarios por Estado')];
    }

    private function statsSucursal(): array
    {
        $porEmpresa = DB::select("
            SELECT COALESCE(e.emp_razon_social, 'Sin empresa') AS empresa, COUNT(*) AS cantidad
            FROM sucursal s
            LEFT JOIN empresa e ON e.id = s.empresa_id
            GROUP BY e.emp_razon_social ORDER BY cantidad DESC
        ");

        return [
            [
                'id'           => 'suc_empresa',
                'titulo'       => 'Sucursales por Empresa',
                'tipo_grafico' => 'bar',
                'labels'       => array_map(fn($r) => $r->empresa, $porEmpresa),
                'datasets'     => [['label' => 'Sucursales', 'data' => array_map(fn($r) => (int)$r->cantidad, $porEmpresa), 'color' => '#8e44ad']],
                'columnas'     => [['key' => 'empresa', 'label' => 'Empresa'], ['key' => 'cantidad', 'label' => 'Sucursales']],
                'tabla'        => $porEmpresa,
            ],
        ];
    }

    private function statsModulos(): array
    {
        return [$this->estadoPorTabla('modulos', 'mod_estado', 'Módulos por Estado')];
    }

    private function statsUsuarios(): array
    {
        $porPerfil = DB::select("
            SELECT COALESCE(pf.pref_descripcion, 'Sin perfil') AS perfil, COUNT(*) AS cantidad
            FROM users u
            LEFT JOIN perfiles pf ON pf.id = u.perfil_id
            GROUP BY pf.pref_descripcion ORDER BY cantidad DESC
        ");

        return [
            [
                'id'           => 'usr_perfil',
                'titulo'       => 'Usuarios por Perfil',
                'tipo_grafico' => 'bar',
                'labels'       => array_map(fn($r) => $r->perfil, $porPerfil),
                'datasets'     => [['label' => 'Usuarios', 'data' => array_map(fn($r) => (int)$r->cantidad, $porPerfil), 'color' => '#2980b9']],
                'columnas'     => [['key' => 'perfil', 'label' => 'Perfil'], ['key' => 'cantidad', 'label' => 'Usuarios']],
                'tabla'        => $porPerfil,
            ],
        ];
    }

    private function statsAccesos(): array
    {
        $porPerfil = DB::select("
            SELECT COALESCE(pf.pref_descripcion, 'Sin perfil') AS perfil, COUNT(*) AS cantidad
            FROM accesos a
            JOIN perfiles pf ON pf.id = a.perfil_id
            GROUP BY pf.pref_descripcion ORDER BY cantidad DESC
        ");

        return [
            $this->estadoPorTabla('accesos', 'acc_estado', 'Accesos por Estado'),
            [
                'id'           => 'acc_perfil',
                'titulo'       => 'Accesos por Perfil',
                'tipo_grafico' => 'bar',
                'opciones'     => ['indexAxis' => 'y'],
                'labels'       => array_map(fn($r) => $r->perfil, $porPerfil),
                'datasets'     => [['label' => 'Accesos', 'data' => array_map(fn($r) => (int)$r->cantidad, $porPerfil), 'color' => '#8e44ad']],
                'columnas'     => [['key' => 'perfil', 'label' => 'Perfil'], ['key' => 'cantidad', 'label' => 'Accesos']],
                'tabla'        => $porPerfil,
            ],
        ];
    }
}
