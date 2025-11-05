<?php

namespace App\Http\Controllers;

use App\Models\ReclamoCliCab;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ReclamoCliCabController extends Controller
{
    public function read(){
        return DB::select("SELECT 
            rcc.id,
            TO_CHAR(rcc.rec_cli_cab_fecha, 'dd/mm/yyyy HH24:mi:ss') AS rec_cli_cab_fecha,
            TO_CHAR(rcc.rec_cli_cab_fecha_inicio, 'dd/mm/yyyy HH24:mi:ss') AS rec_cli_cab_fecha_inicio,
            TO_CHAR(rcc.rec_cli_cab_fecha_fin, 'dd/mm/yyyy HH24:mi:ss') AS rec_cli_cab_fecha_fin,
            rcc.rec_cli_cab_observacion,
            rcc.rec_cli_cab_prioridad,
            rcc.rec_cli_cab_estado,

            rcc.sucursal_id,
            s.suc_razon_social AS suc_razon_social,

            rcc.empresa_id,
            e.emp_razon_social AS emp_razon_social,

            rcc.clientes_id,
            c.cli_nombre,
            c.cli_apellido,
            c.cli_ruc,
            c.cli_direccion,
            c.cli_telefono,
            c.cli_correo,

            rcc.user_id,
            u.name,
            u.login,

            rcc.created_at,
            rcc.updated_at

        FROM reclamo_cli_cab rcc
        JOIN sucursal s ON s.empresa_id = rcc.sucursal_id
        JOIN empresa e ON e.id = rcc.empresa_id
        JOIN clientes c ON c.id = rcc.clientes_id
        JOIN users u ON u.id = rcc.user_id
        ORDER BY rcc.id DESC");
    }
    public function store(Request $r){
        $datosValidados = $r->validate([
            'rec_cli_cab_observacion'=>'required',
            'rec_cli_cab_fecha'=>'required',
            'rec_cli_cab_fecha_inicio'=>'required',
            'rec_cli_cab_fecha_fin'=>'required',
            'rec_cli_cab_prioridad'=>'required',
            'rec_cli_cab_estado'=>'required',
            'clientes_id'=>'required',
            'user_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required'
        ]);
        $reclamoclicab = ReclamoCliCab::create($datosValidados);
        $reclamoclicab->save();
        return response()->json([
            'mensaje'=>'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $reclamoclicab
        ],200);
    }
    public function update(Request $r, $id){
        $reclamoclicab = ReclamoCliCab::find($id);
        if(!$reclamoclicab){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'rec_cli_cab_observacion'=>'required',
            'rec_cli_cab_fecha'=>'required',
            'rec_cli_cab_fecha_inicio'=>'required',
            'rec_cli_cab_fecha_fin'=>'required',
            'rec_cli_cab_prioridad'=>'required',
            'rec_cli_cab_estado'=>'required',
            'clientes_id'=>'required',
            'user_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required'
        ]);
        $reclamoclicab->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $reclamoclicab
        ],200);
    }
    public function anular(Request $r, $id){
        $reclamoclicab = ReclamoCliCab::find($id);
        if(!$reclamoclicab){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'rec_cli_cab_observacion'=>'required',
            'rec_cli_cab_fecha'=>'required',
            'rec_cli_cab_fecha_inicio'=>'required',
            'rec_cli_cab_fecha_fin'=>'required',
            'rec_cli_cab_prioridad'=>'required',
            'rec_cli_cab_estado'=>'required',
            'clientes_id'=>'required',
            'user_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required'
        ]);
        $reclamoclicab->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro anulado con exito',
            'tipo'=>'success',
            'registro'=> $reclamoclicab
        ],200);
    }
    public function confirmar(Request $r, $id){
        $reclamoclicab = ReclamoCliCab::find($id);
        if(!$reclamoclicab){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'rec_cli_cab_observacion'=>'required',
            'rec_cli_cab_fecha'=>'required',
            'rec_cli_cab_fecha_inicio'=>'required',
            'rec_cli_cab_fecha_fin'=>'required',
            'rec_cli_cab_prioridad'=>'required',
            'rec_cli_cab_estado'=>'required',
            'clientes_id'=>'required',
            'user_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required'
        ]);
        $reclamoclicab->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro confirmado con exito',
            'tipo'=>'success',
            'registro'=> $reclamoclicab
        ],200);
    }
}
