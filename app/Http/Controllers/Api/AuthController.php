<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Credenciais inválidas'
            ], 401);
        }

        // Remove tokens antigos (opcional)
        $user->tokens()->delete();

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user
        ]);
    }

    // public function logout(Request $request)
    // {
    //     $request->user()->currentAccessToken()->delete();

    //     return response()->json([
    //         'message' => 'Logout realizado com sucesso'
    //     ]);
    // }

    // No seu api.php ou AuthController.php
public function logout(Request $request)
{
    // 1. Revoga o token se for via Token
    if ($request->user()) {
        $request->user()->currentAccessToken()->delete();
    }

    // 2. Se estiver usando Sessão/Cookies (Sanctum SPA)
    // Auth::guard('web')->logout(); 
    // $request->session()->invalidate();
    // $request->session()->regenerateToken();

    return response(['message' => 'Deslogado com sucesso'])
            ->withoutCookie('token'); // Garanta que o path '/' coincida
}

    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
