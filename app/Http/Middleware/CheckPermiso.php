<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckPermiso
{
    // Mapeo: primer segmento del URI → nombre de entidad
    private const ENTIDAD_MAP = [
        // compras
        'pedidos'               => 'pedidos',
        'pedidos-detalles'      => 'pedidos',
        'presupuesto'           => 'presupuestos',
        'presupuestos'          => 'presupuestos',
        'presupuestos-detalles' => 'presupuestos',
        'presupuesto-pedidos'   => 'presupuestos',
        'ordencompracab'        => 'orden_compra',
        'ordencompradet'        => 'orden_compra',
        'ordenes_compras'       => 'orden_compra',
        'compras'               => 'registro',
        'compradet'             => 'registro',
        'notaremicomp'          => 'nota_remision',
        'notaremicomdet'        => 'nota_remision',
        'ajus_cab'              => 'ajuste',
        'ajus_det'              => 'ajuste',
        'notacompcab'           => 'nota_compra',
        'notacompdet'           => 'nota_compra',
        'libro_compras'         => 'pedidos',
        // ventas
        'pedido_ventas'         => 'pedidos',
        'pedido_ventas_det'     => 'pedidos',
        'ventas_cab'            => 'ventas',
        'ventas_det'            => 'ventas',
        'notaremivent'          => 'nota_remision',
        'notaremiventdet'       => 'nota_remision',
        'notaventcab'           => 'nota_venta',
        'notaventdet'           => 'nota_venta',
        'notavemtcab'           => 'nota_venta',
        // servicios
        'solicitudcad'          => 'solicitud',
        'solicitud_det'         => 'solicitud',
        'recepcab'              => 'recepcion',
        'recepcion_det'         => 'recepcion',
        'diagnosticocab'        => 'diagnostico',
        'diagnostico_det'       => 'diagnostico',
        'presupuestoservcab'    => 'presupuesto',
        'presupuesto_serv_det'  => 'presupuesto',
        'ordenserviciocab'      => 'orden_servicio',
        'ordenservicodet'       => 'orden_servicio',
        'ordenserviciodet'      => 'orden_servicio',
        'contratoservcab'       => 'contrato',
        'contratoservdet'       => 'contrato',
        'ordenservventa'        => 'orden_venta',
        'reclamoclicab'         => 'reclamo',
        'reclamoclidet'         => 'reclamo',
        'promocionescab'        => 'promocion',
        'promociones_det'       => 'promocion',
        'descuentoscab'         => 'descuento',
        'descuentos_det'        => 'descuento',
        // cobros
        'cobros_cab'            => 'cobro',
        'cobros_det'            => 'cobro',
        'cobros_tarjeta'        => 'cobro',
        'cobros_cheque'         => 'cobro',
        'ctas_cobrar'           => 'cobro',
        'apertura_cierre_caja'  => 'caja',
        'arqueo_caja'           => 'arqueo',
        // seguridad
        'users'                 => 'usuarios',
        'permisos'              => 'permisos',
        'accesos'               => 'accesos',
        'modulos'               => 'modulos',
        'perfiles'              => 'perfiles',
    ];

    public function handle(Request $request, Closure $next, string $modulo)
    {
        $user = $request->user();

        if (!$user->perfil_id) {
            return response()->json([
                'mensaje' => 'El usuario no tiene un perfil asignado',
                'tipo'    => 'error'
            ], 403);
        }

        // Superadmin tiene acceso total sin revisar accesos
        $esSuperadmin = DB::table('perfiles')
            ->where('id', $user->perfil_id)
            ->value('pref_superadmin');

        if ($esSuperadmin) {
            return $next($request);
        }

        // Una sola query: todos los permisos activos del perfil para este módulo
        $permisosActivos = DB::table('accesos as a')
            ->join('permisos as p', 'p.id', '=', 'a.permiso_id')
            ->join('modulos as m',  'm.id', '=', 'a.mod_id')
            ->where('a.perfil_id', $user->perfil_id)
            ->where('a.acc_estado', 'ACTIVO')
            ->where('m.mod_nombre', $modulo)
            ->pluck('p.per_nombre')
            ->toArray();

        if (empty($permisosActivos)) {
            return response()->json([
                'mensaje' => 'No tiene permiso para acceder a este módulo',
                'tipo'    => 'error'
            ], 403);
        }

        // Clasificar por nivel de granularidad
        $tieneNivel3 = !empty(array_filter($permisosActivos, fn($p) => substr_count($p, '.') === 2));
        $tieneNivel2 = !empty(array_filter($permisosActivos, fn($p) => substr_count($p, '.') === 1));

        if ($tieneNivel3) {
            // Verificar permiso modulo.entidad.accion
            $entidad = $this->detectarEntidad($request);
            $accion  = $this->detectarAccion($request);

            if ($entidad && !in_array("$modulo.$entidad.$accion", $permisosActivos)) {
                return response()->json([
                    'mensaje' => 'No tiene permiso para realizar esta acción',
                    'tipo'    => 'error'
                ], 403);
            }
        } elseif ($tieneNivel2) {
            // Verificar permiso modulo.accion (nivel anterior)
            $accion = $this->detectarAccion($request);
            if (!in_array("$modulo.$accion", $permisosActivos)) {
                return response()->json([
                    'mensaje' => 'No tiene permiso para realizar esta acción',
                    'tipo'    => 'error'
                ], 403);
            }
        }
        // Sin puntos = acceso legacy completo al módulo → permite todo

        return $next($request);
    }

    private function detectarEntidad(Request $request): ?string
    {
        // segment(2) porque segment(1) = prefijo "Proyecto_tp"
        $segmento = $request->segment(2);
        return self::ENTIDAD_MAP[$segmento] ?? null;
    }

    private function detectarAccion(Request $request): string
    {
        $method = $request->method();
        $path   = $request->path();

        if ($method === 'GET') return 'ver';

        if ($method === 'POST') {
            if (preg_match('/(buscar|informe|buscarItem|buscarPor|buscarVehiculo|buscarMarca)/', $path)) {
                return 'ver';
            }
            return 'crear';
        }

        if ($method === 'DELETE') return 'eliminar';

        if ($method === 'PUT') {
            if (str_contains($path, '/anular/'))    return 'anular';
            if (str_contains($path, '/confirmar/')) return 'confirmar';
            if (str_contains($path, '/aprobar/'))   return 'aprobar';
            if (str_contains($path, '/rechazar/'))  return 'rechazar';
            if (str_contains($path, '/procesar/'))  return 'procesar';
            if (str_contains($path, '/resolver/'))  return 'resolver';
            if (str_contains($path, 'cerrarCaja'))  return 'confirmar';
            return 'modificar';
        }

        return 'ver';
    }
}
