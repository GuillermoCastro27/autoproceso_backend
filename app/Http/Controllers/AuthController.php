<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Models\User;
use App\Models\Perfil;
use \stdClass;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'name' => 'required|string|max:255',
            'email' => 'string|email|max:255|unique:users',
            'password' => [
                'required', 
                'string', 
                'min:8', 
                'regex:/[a-z]/', 
                'regex:/[A-Z]/', 
                'regex:/[0-9]/', 
                'regex:/[*\-\/@$!%#?&]/'
            ],
            'login' => 'required|string'
        ],[
            'password.min' => 'La contraseña debe de tener al menos 8 caracteres',
            'password.regex' => 'La contraseña debe incluir al menos una letra mayúscula, una letra minúscula, un número y un carácter especial (*, -, /, +, @, $, !, %, #, ?, &).'
        ]);

        if($validator->fails())
        {
            return response()->json($validator->errors());
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'login' => $request->login
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['data' => $user,'access_token' => $token, 'token_type' => 'Bearer',]);
    }

    public function login(Request $request)
    {
        if(!Auth::attempt($request->only('login','password'))){
            $user = User::where('login',$request['login'])->first();
            if($user!=null){
                $user->intentos = $user->intentos + 1;
                $user->save();
            }
            return response()->json(['message' => 'USUARIO O CONTRASEÑA INCORRECTA'],401);
        }

        $user = User::where('login',$request['login'])->firstOrFail();
        $perfil = Perfil::where('id', $user->perfil_id)-> firstOrFail();

        if($user->intentos > 2){
            return response()->json(['message' => 'USUARIO BLOQUEADO POR INTENTOS FALLIDOS'],401);
        }
        $user->intentos = 0;
        $user->save();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()
            ->json([
                'message' => 'Bienvenido '.$user->name,
                'accessToken' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
                'perfil' => $perfil
            ]);
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();
        return ['message' => 'Usted se ha desconectado satisfactoriamente'];
    }
}