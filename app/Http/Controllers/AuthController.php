<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credenciais = $request->all(['email', 'password']);

        $token = auth('api')->attempt($credenciais);

        if ($token) { //usuario autenticado
            return response()->json(['token' => $token], 200);
        } else { //erro usuario ou senha
            return response()->json([
                'erro' => 'Usuario ou senha invalidos'
            ], 403);
        }
    }

    public function logout() 
    {
        auth('api')->logout();
        return response()->json(['msg' => 'Logout foi realizado com sucesso']);
    }

    public function refresh() 
    {
        //encaminhar jwt valido
        $token = auth('api')->refresh();
        return response()->json(['toke' => $token]);
    }

    public function me() 
    {
        return response()->json(auth()->user());
    }
}
