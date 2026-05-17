<?php

namespace App\Http\Controllers\Seguridad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use PragmaRX\Google2FA\Google2FA;
use App\Mail\TwoFactorCodeMail;
use App\Models\User;

class TwoFactorController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | TOTP POR QR (opcional, lo dejamos para futuro)
    |--------------------------------------------------------------------------
    */
    public function generarQR()
    {
        $user = User::first(); // solo para pruebas QR

        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();

        $user->two_factor_secret = encrypt($secret);
        $user->save();

        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        return response()->json([
            'qr'     => $qrCodeUrl,
            'secret' => $secret
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | ENVÍO DE CÓDIGO 2FA POR CORREO
    |--------------------------------------------------------------------------
    | Este método ES LLAMADO DESDE AuthController@login
    | NO devuelve response
    |--------------------------------------------------------------------------
    */
    public function enviarCodigoEmail(User $user)
{
    if (empty($user->email)) {
        return response()->json([
            'mensaje' => 'El usuario no tiene correo cargado',
            'tipo' => 'error'
        ], 422);
    }

    $code = (string) random_int(100000, 999999);

    $user->email_two_factor_code       = $code;
    $user->email_two_factor_expires_at = now()->addMinutes(5);
    $user->save(); // guarda también los cambios de intentos/password rehash del AuthController

    // Enviar correo DESPUÉS de que la respuesta HTTP llega al cliente
    $email = $user->email;
    dispatch(function() use ($email, $code) {
        Mail::to($email)->send(new TwoFactorCodeMail($code));
    })->afterResponse();

    return true;
}


    /*
    |--------------------------------------------------------------------------
    | VALIDACIÓN DEL CÓDIGO 2FA
    |--------------------------------------------------------------------------
    | ACÁ SE CREA EL TOKEN (PASO FINAL)
    |--------------------------------------------------------------------------
    */
    public function validarCodigoEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code'  => 'required|string'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !$user->email_two_factor_code) {
            return response()->json([
                'mensaje' => 'No existe código pendiente de validación',
                'tipo'    => 'error'
            ], 422);
        }

        if ($user->email_two_factor_expires_at < now()) {
            return response()->json([
                'mensaje' => 'El código ha expirado',
                'tipo'    => 'error'
            ], 422);
        }

        if ($user->email_two_factor_code !== $request->code) {
            return response()->json([
                'mensaje' => 'Código incorrecto',
                'tipo'    => 'error'
            ], 422);
        }

        // Código válido → limpiar
        $user->email_two_factor_code = null;
        $user->email_two_factor_expires_at = null;
        $user->save();

        // 🔐 CREAR TOKEN AHORA
        $token = $user->createToken('auth_token')->plainTextToken;

        $esSuperadmin = (bool) DB::table('perfiles')
            ->where('id', $user->perfil_id)
            ->value('pref_superadmin');

        if ($esSuperadmin) {
            // El superadmin ve todos los módulos sin filtro de accesos
            $modulos  = DB::table('modulos')->pluck('mod_nombre')->toArray();
            $permisos = [];
        } else {
            // Módulos y permisos configurados para el perfil del usuario
            $accesos = DB::table('accesos as a')
                ->join('permisos as p', 'p.id', '=', 'a.permiso_id')
                ->join('modulos as m',  'm.id', '=', 'a.mod_id')
                ->where('a.perfil_id', $user->perfil_id)
                ->where('a.acc_estado', 'ACTIVO')
                ->whereNotNull('a.mod_id')
                ->select('m.mod_nombre', 'p.per_nombre')
                ->get();

            $modulos  = $accesos->pluck('mod_nombre')->unique()->values()->toArray();
            $permisos = $accesos->pluck('per_nombre')
                ->filter(fn($p) => str_contains($p, '.'))
                ->unique()
                ->values()
                ->toArray();
        }

        // Incluir descripción del perfil en el objeto user
        $user->perfil_descripcion = DB::table('perfiles')
            ->where('id', $user->perfil_id)
            ->value('pref_descripcion');

        return response()->json([
            'mensaje'    => 'Autenticación verificada correctamente',
            'tipo'       => 'success',
            'token'      => $token,
            'user'       => $user,
            'modulos'    => $modulos,
            'permisos'   => $permisos,
            'superadmin' => $esSuperadmin,
        ]);
    }
}
