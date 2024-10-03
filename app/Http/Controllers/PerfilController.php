<?php

namespace App\Http\Controllers;

use App\Models\Perfil;
use App\Models\Tipo;
use Illuminate\Http\Request;

class PerfilController extends Controller
{
    public function read(){
        return Perfil::all();
    }
    public function store(Request $r){
        $perfil = Perfil::create($r->all());
        return response()->json([
            'mensaje'=>'Registro creado con exito',
            'tipo'=>'success',
            'registro'=>$perfil
        ]);
    }
}
