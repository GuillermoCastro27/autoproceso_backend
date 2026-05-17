<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use App\Models\User;
use App\Mail\PasswordCambiado;

class ForgotPasswordController extends Controller
{
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // Siempre responde igual para no revelar si el email existe o no
        $user = User::where('email', $request->email)->first();

        if ($user) {
            Password::sendResetLink($request->only('email'));
        }

        return response()->json([
            'mensaje' => 'Si ese correo está registrado, recibirás un enlace para restablecer tu contraseña.',
            'tipo'    => 'success'
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
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

        $status = Password::reset(
            $request->only('email', 'password', 'token'),
            function (User $user, string $password) {
                // Actualizar contraseña y limpiar bloqueos
                $user->password        = Hash::make($password);
                $user->intentos        = 0;
                $user->bloqueado_hasta = null;
                $user->save();

                // Revocar todos los tokens Sanctum activos
                $user->tokens()->delete();

                // Notificar al usuario por correo (async)
                $nombre = $user->name;
                $email  = $user->email;
                dispatch(function () use ($email, $nombre) {
                    Mail::to($email)->send(new PasswordCambiado($nombre));
                })->afterResponse();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'mensaje' => 'Contraseña actualizada con éxito. Tus sesiones activas fueron cerradas por seguridad.',
                'tipo'    => 'success'
            ]);
        }

        return response()->json([
            'mensaje' => 'El enlace es inválido o ya expiró',
            'tipo'    => 'error'
        ], 400);
    }
}
