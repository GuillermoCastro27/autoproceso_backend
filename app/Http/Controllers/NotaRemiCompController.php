<?php

namespace App\Http\Controllers;

use App\Models\NotaRemiComp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotaRemiCompController extends Controller
{
    public function read(){
        return DB::select("select 
        nrc.id ,
        TO_CHAR(nrc.nota_remi_fecha, 'dd/mm/yyyy HH24:mi:ss') AS nota_remi_fecha,
        nrc.nota_remi_observaciones ,
        nrc.nota_remi_estado ,
        nrc.sucursal_id,
        s.suc_razon_social AS suc_razon_social,
        nrc.empresa_id,
        e.emp_razon_social AS emp_razon_social,
        nrc.user_id ,
        nrc.created_at ,
        nrc.updated_at ,
        u.name, 
        u.login  
        from nota_remi_comp nrc 
        JOIN 
        sucursal s ON s.empresa_id = nrc.sucursal_id
        JOIN 
        empresa e ON e.id = nrc.empresa_id 
        join users u on u.id = nrc.user_id;");
    }
    public function store(Request $r){
        $datosValidados = $r->validate([
            'nota_remi_fecha'=>'required',
            'nota_remi_observaciones'=>'required',
            'nota_remi_estado'=>'required',
            'user_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required'
        ]);
        $notaremicomp = NotaRemiComp::create($datosValidados);
        $notaremicomp->save();
        return response()->json([
            'mensaje'=>'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $notaremicomp
        ],200);
    }
    public function update(Request $r, $id){
        $notaremicomp = NotaRemiComp::find($id);
        if(!$notaremicomp){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'nota_remi_fecha'=>'required',
            'nota_remi_observaciones'=>'required',
            'nota_remi_estado'=>'required',
            'user_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required'
        ]);
        $notaremicomp->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro modificado con exito',
            'tipo'=>'success',
            'registro'=> $notaremicomp
        ],200);
    }
    public function anular(Request $r, $id){
        $notaremicomp = NotaRemiComp::find($id);
        if(!$notaremicomp){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'nota_remi_fecha'=>'required',
            'nota_remi_observaciones'=>'required',
            'nota_remi_estado'=>'required',
            'user_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required'
        ]);
        $notaremicomp->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro anulado con exito',
            'tipo'=>'success',
            'registro'=> $notaremicomp
        ],200);
    }
    public function confirmar(Request $r, $id){
        $notaremicomp = NotaRemiComp::find($id);
        if(!$notaremicomp){
            return response()->json([
                'mensaje'=>'Registro no encontrado',
                'tipo'=>'error'
            ],404);
        }
        $datosValidados = $r->validate([
            'nota_remi_fecha'=>'required',
            'nota_remi_observaciones'=>'required',
            'nota_remi_estado'=>'required',
            'user_id'=>'required',
            'empresa_id'=>'required',
            'sucursal_id'=>'required'
        ]);
        $notaremicomp->update($datosValidados);
        return response()->json([
            'mensaje'=>'Registro confirmado con exito',
            'tipo'=>'success',
            'registro'=> $notaremicomp
        ],200);
    }
}
