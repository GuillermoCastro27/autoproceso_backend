<?php

namespace App\Http\Controllers;

use App\Models\Accesos;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class AccesosController extends Controller
{
    public function read()
    {
        return DB::select("
            SELECT
                a.id,
                a.perfil_id,
                a.permiso_id,
                a.mod_id,
                a.acc_estado,
                a.acc_fecha,
                p.per_nombre,
                pf.pref_descripcion AS perfil_desc,
                m.mod_nombre
            FROM accesos a
            JOIN permisos  p  ON p.id  = a.permiso_id
            JOIN perfiles  pf ON pf.id = a.perfil_id
            LEFT JOIN modulos m ON m.id = a.mod_id
            ORDER BY a.id
        ");
    }

    public function store(Request $r)
    {
        $r->validate([
            'perfil_id'  => 'required|integer|exists:perfiles,id',
            'permiso_id' => 'required|integer|exists:permisos,id',
            'mod_id'     => 'required|integer|exists:modulos,id',
            'acc_estado' => 'required|string',
        ]);

        $acceso = Accesos::create([
            'perfil_id'  => $r->perfil_id,
            'permiso_id' => $r->permiso_id,
            'mod_id'     => $r->mod_id,
            'acc_estado' => $r->acc_estado,
            'acc_fecha'  => now(),
        ]);

        return response()->json([
            'mensaje'   => 'Registro creado con éxito',
            'tipo'      => 'success',
            'registro'  => $acceso
        ]);
    }

    public function update(Request $r, $id)
    {
        $acceso = Accesos::find($id);

        if (!$acceso) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo'    => 'error'
            ], 404);
        }

        $r->validate([
            'perfil_id'  => 'required|integer|exists:perfiles,id',
            'permiso_id' => 'required|integer|exists:permisos,id',
            'mod_id'     => 'required|integer|exists:modulos,id',
            'acc_estado' => 'required|string',
        ]);

        $acceso->update([
            'perfil_id'  => $r->perfil_id,
            'permiso_id' => $r->permiso_id,
            'mod_id'     => $r->mod_id,
            'acc_estado' => $r->acc_estado,
        ]);

        return response()->json([
            'mensaje'  => 'Registro modificado con éxito',
            'tipo'     => 'success',
            'registro' => $acceso
        ]);
    }

    public function desactivar($id)
    {
        $acceso = Accesos::find($id);

        if (!$acceso) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo'    => 'error'
            ], 404);
        }

        $acceso->acc_estado = 'INACTIVO';
        $acceso->acc_fecha  = now();
        $acceso->save();

        return response()->json([
            'mensaje' => 'Acceso desactivado con éxito',
            'tipo'    => 'success'
        ]);
    }

    public function storeMasivo(Request $r)
    {
        $r->validate([
            'perfil_id'     => 'required|integer|exists:perfiles,id',
            'mod_id'        => 'required|integer|exists:modulos,id',
            'permiso_ids'   => 'required|array|min:1',
            'permiso_ids.*' => 'integer|exists:permisos,id',
        ]);

        $creados = 0;
        foreach ($r->permiso_ids as $permiso_id) {
            $existe = Accesos::where('perfil_id', $r->perfil_id)
                ->where('permiso_id', $permiso_id)
                ->exists();

            if (!$existe) {
                Accesos::create([
                    'perfil_id'  => $r->perfil_id,
                    'permiso_id' => $permiso_id,
                    'mod_id'     => $r->mod_id,
                    'acc_estado' => 'ACTIVO',
                    'acc_fecha'  => now(),
                ]);
                $creados++;
            }
        }

        $omitidos = count($r->permiso_ids) - $creados;
        $msg = $creados > 0
            ? "{$creados} acceso(s) creado(s) con éxito" . ($omitidos > 0 ? " ({$omitidos} ya existían)" : "")
            : "Todos los accesos seleccionados ya existen para este perfil";

        return response()->json([
            'mensaje' => $msg,
            'tipo'    => $creados > 0 ? 'success' : 'warning',
        ]);
    }

    public function activar($id)
    {
        $acceso = Accesos::find($id);

        if (!$acceso) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo'    => 'error'
            ], 404);
        }

        $acceso->acc_estado = 'ACTIVO';
        $acceso->acc_fecha  = now();
        $acceso->save();

        return response()->json([
            'mensaje' => 'Acceso activado con éxito',
            'tipo'    => 'success'
        ]);
    }
}
