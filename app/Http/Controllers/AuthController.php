<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Validator;
use App\Models\User;
use App\Models\Perfil;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | REGISTRO
    |--------------------------------------------------------------------------
    */
    public function register(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'login' => 'required|string|unique:users,login',
        'password' => [
            'required',
            'string',
            'min:8',
            'regex:/[a-z]/',
            'regex:/[A-Z]/',
            'regex:/[0-9]/',
            'regex:/[*\-\/@$!%#?&]/'
        ],
        // 🔐 ROL (PERFIL)
        'perfil_id' => 'required|exists:perfil,id'
    ], [
        'perfil_id.required' => 'Debe asignar un rol al usuario'
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 422);
    }

    $user = User::create([
        'name'      => $request->name,
        'email'     => $request->email,
        'login'     => $request->login,
        'password'  => Hash::make($request->password),
        'perfil_id' => $request->perfil_id,
        'intentos'  => 0
    ]);

    return response()->json([
        'mensaje' => 'Usuario registrado correctamente',
        'tipo'    => 'success',
        'user'    => $user
    ]);
}


    /*
    |--------------------------------------------------------------------------
    | LOGIN CON 2FA
    |--------------------------------------------------------------------------
    */
    public function login(Request $request)
    {
        $request->validate([
            'login'    => 'required',
            'password' => 'required'
        ]);

        $user = User::where('login', $request->login)->first();

        // Verificar si el usuario está bloqueado temporalmente
        if ($user && $user->bloqueado_hasta && now()->lt($user->bloqueado_hasta)) {
            $minutos = now()->diffInMinutes($user->bloqueado_hasta) + 1;
            return response()->json([
                'message' => "Usuario bloqueado por intentos fallidos. Intente nuevamente en {$minutos} minuto(s)."
            ], 401);
        }

        // Credenciales incorrectas
        if (!$user || !Hash::check($request->password, $user->password)) {
            if ($user) {
                $user->intentos++;
                if ($user->intentos >= 3) {
                    $user->bloqueado_hasta = now()->addMinutes(30);
                    $user->intentos        = 0;
                }
                $user->save();
            }

            return response()->json([
                'message' => 'USUARIO O CONTRASEÑA INCORRECTA'
            ], 401);
        }

        $user   = User::where('login', $request->login)->firstOrFail();
        $perfil = Perfil::where('id', $user->perfil_id)->firstOrFail();

        // Desbloquear y resetear intentos al ingresar correctamente
        $user->intentos       = 0;
        $user->bloqueado_hasta = null;
        $user->save();

        // Verificar que el usuario tenga un funcionario asignado
        if (!$user->funcionario_id) {
            return response()->json([
                'message' => 'El usuario no tiene un funcionario asignado. Contacte al administrador.'
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | 🔐 2FA POR CORREO
        |--------------------------------------------------------------------------
        */
        if ($user->two_factor_enabled) {

            app(\App\Http\Controllers\Seguridad\TwoFactorController::class)
                ->enviarCodigoEmail($user);

            return response()->json([
                'tipo'    => '2fa',
                'email'   => $user->email,
                'message' => 'Se envió un código de verificación a su correo'
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | SIN 2FA HABILITADO → ACCESO DENEGADO
        |--------------------------------------------------------------------------
        */
        return response()->json([
            'tipo'    => 'sin_2fa',
            'message' => 'El usuario no tiene habilitado el doble factor de autenticación. Contacte al administrador.'
        ], 403);
    }

    /*
    |--------------------------------------------------------------------------
    | LOGOUT
    |--------------------------------------------------------------------------
    */
    public function logout()
    {
        auth()->user()->tokens()->delete();

        return response()->json([
            'message' => 'Usted se ha desconectado satisfactoriamente'
        ]);
    }
}
