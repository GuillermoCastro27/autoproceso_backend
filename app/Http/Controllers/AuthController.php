<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Validator;
use App\Models\User;
use App\Models\LoginIntento;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\UsuarioBloqueadoMail;

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
            $this->registrarIntento($request, 'bloqueado');
            return response()->json([
                'tipo'    => 'bloqueado',
                'message' => "Cuenta bloqueada por intentos fallidos. Intente nuevamente en {$minutos} minuto(s) o recupere su contraseña."
            ], 401);
        }

        // Usuario no existe
        if (!$user) {
            $this->registrarIntento($request, 'usuario_no_existe');
            return response()->json([
                'tipo'    => 'usuario_no_existe',
                'message' => 'El usuario ingresado no existe.'
            ], 401);
        }

        // Contraseña incorrecta
        if (!Hash::check($request->password, $user->password)) {
            $user->intentos++;

            if ($user->intentos >= 3) {
                $user->bloqueado_hasta = now()->addMinutes(30);
                $user->intentos        = 0;
                $user->save();

                $this->registrarIntento($request, 'bloqueado');

                // Notificar al administrador por correo
                $adminEmail = env('ADMIN_EMAIL');
                if ($adminEmail) {
                    $login = $request->login;
                    $ip    = $request->ip();
                    dispatch(function () use ($adminEmail, $login, $ip) {
                        Mail::to($adminEmail)->send(new UsuarioBloqueadoMail($login, $ip));
                    })->afterResponse();
                }

                return response()->json([
                    'tipo'    => 'bloqueado',
                    'message' => 'Cuenta bloqueada por 3 intentos fallidos. Intente nuevamente en 30 minutos o recupere su contraseña.'
                ], 401);
            }

            $restantes = 3 - $user->intentos;
            $user->save();

            $this->registrarIntento($request, 'contrasena_incorrecta');
            return response()->json([
                'tipo'       => 'contrasena_incorrecta',
                'message'    => 'Contraseña incorrecta.',
                'restantes'  => $restantes
            ], 401);
        }

        // Rehash si la contraseña fue guardada con más rondas que las actuales
        if (Hash::needsRehash($user->password)) {
            $user->password = Hash::make($request->password);
        }

        // Resetear bloqueo (se guarda junto con el código 2FA en un solo UPDATE)
        $user->intentos        = 0;
        $user->bloqueado_hasta = null;

        // Verificar que el usuario tenga un funcionario asignado
        if (!$user->funcionario_id) {
            $user->save();
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

            $this->registrarIntento($request, 'exitoso');

            // enviarCodigoEmail hace el único save() combinado
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
    | HELPER — REGISTRAR INTENTO
    |--------------------------------------------------------------------------
    */
    private function registrarIntento(Request $request, string $resultado): void
    {
        LoginIntento::create([
            'login'      => $request->login,
            'resultado'  => $resultado,
            'ip'         => $request->ip(),
            'user_agent' => substr($request->userAgent() ?? '', 0, 300),
        ]);
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
