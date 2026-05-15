<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ], [
            'email.exists' => 'No existe una cuenta registrada con ese correo'
        ]);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'mensaje' => 'Se envió el enlace de recuperación a su correo',
                'tipo'    => 'success'
            ]);
        }

        return response()->json([
            'mensaje' => 'No se pudo enviar el enlace, intente nuevamente',
            'tipo'    => 'error'
        ], 400);
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
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->intentos = 0;
                $user->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'mensaje' => 'Contraseña actualizada con éxito',
                'tipo'    => 'success'
            ]);
        }

        return response()->json([
            'mensaje' => 'El enlace es inválido o ya expiró',
            'tipo'    => 'error'
        ], 400);
    }
}
