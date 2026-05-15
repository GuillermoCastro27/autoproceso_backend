<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Perfil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function read()
    {
        return User::with(['perfil:id,pref_descripcion', 'funcionario:id,fun_nom,fun_apellido,fun_ci'])
            ->orderBy('id', 'desc')
            ->get();
    }
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'login'    => 'required|string|max:255|unique:users,login',
            'email'    => 'required|email|max:255|unique:users,email',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[*\-\/@$!%#?&]/'
            ],
            'perfil_id'          => 'required|integer|exists:perfiles,id',
            'funcionario_id'     => 'nullable|integer|exists:funcionario,id',
            'two_factor_enabled' => 'nullable|boolean'
        ], [
            'password.min'   => 'La contraseña debe tener al menos 8 caracteres',
            'password.regex' => 'La contraseña debe incluir mayúscula, minúscula, número y carácter especial'
        ]);

        $user = User::create([
            'name'               => $request->name,
            'login'              => $request->login,
            'email'              => $request->email,
            'password'           => Hash::make($request->password),
            'perfil_id'          => $request->perfil_id,
            'funcionario_id'     => $request->funcionario_id,
            'two_factor_enabled' => $request->two_factor_enabled ?? false,
            'intentos'           => 0
        ]);

        return response()->json([
            'mensaje'  => 'Usuario creado con éxito',
            'tipo'     => 'success',
            'registro' => $user
        ]);
    }
    public function update(Request $request, $id)
{
    $user = User::find($id);

    if (!$user) {
        return response()->json([
            'mensaje' => 'Usuario no encontrado',
            'tipo'    => 'error'
        ], 404);
    }

    $request->validate([
        'name'  => 'required|string|max:255',
        'login' => [
            'required','string','max:255',
            Rule::unique('users','login')->ignore($user->id)
        ],
        'email' => [
            'required','email','max:255',
            Rule::unique('users','email')->ignore($user->id)
        ],
        'perfil_id'          => 'required|integer|exists:perfiles,id',
        'funcionario_id'     => 'nullable|integer|exists:funcionario,id',
        'two_factor_enabled' => 'nullable|boolean',

        // password opcional al editar
        'password' => [
            'nullable',
            'string',
            'min:8',
            'regex:/[a-z]/',
            'regex:/[A-Z]/',
            'regex:/[0-9]/',
            'regex:/[*\-\/@$!%#?&]/'
        ],
    ], [
        'password.min'   => 'La contraseña debe tener al menos 8 caracteres',
        'password.regex' => 'La contraseña debe incluir mayúscula, minúscula, número y carácter especial'
    ]);

    $user->name           = $request->name;
    $user->login          = $request->login;
    $user->email          = $request->email;
    $user->perfil_id      = $request->perfil_id;
    $user->funcionario_id = $request->funcionario_id ?? null;

    // 🔐 SOLO si viene explícitamente en el request
    if ($request->has('two_factor_enabled')) {
        $user->two_factor_enabled = (bool) $request->two_factor_enabled;
    }

    // Password opcional
    if (!empty($request->password)) {
        $user->password = Hash::make($request->password);
    }

    $user->save();

    return response()->json([
        'mensaje'  => 'Usuario actualizado con éxito',
        'tipo'     => 'success',
        'registro' => $user
    ]);
}


    public function destroy($id)
    {
        $user = User::find($id);

        if(!$user){
            return response()->json([
                'mensaje' => 'Usuario no encontrado',
                'tipo'    => 'error'
            ], 404);
        }

        // Evitar eliminarte a vos mismo si ya estás autenticado
        $authUser = auth()->user();
        if($authUser && $authUser->id == $user->id){
            return response()->json([
                'mensaje' => 'No podés eliminar tu propio usuario estando logueado',
                'tipo'    => 'warning'
            ], 409);
        }

        // También podrías validar si tiene dependencias (ventas, compras, etc.)
        $user->delete();

        return response()->json([
            'mensaje' => 'Usuario eliminado con éxito',
            'tipo'    => 'success'
        ]);
    }
    public function resetPassword(Request $request, $id)
    {
        $user = User::find($id);

        if(!$user){
            return response()->json([
                'mensaje' => 'Usuario no encontrado',
                'tipo'    => 'error'
            ], 404);
        }

        $request->validate([
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[*\-\/@$!%#?&]/'
            ],
        ], [
            'password.min'   => 'La contraseña debe tener al menos 8 caracteres',
            'password.regex' => 'La contraseña debe incluir mayúscula, minúscula, número y carácter especial'
        ]);

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'mensaje' => 'Contraseña actualizada con éxito',
            'tipo'    => 'success'
        ]);
    }
}
