<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Validação da entrada
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8'
        ], [
            'email.required' => 'O email é obrigatorio',
            'email.email' => 'Digite um email válido',
            'password.required' => 'A senha é obrigatória',
            'password.min' => 'A senha deve ter no mínimo 8 caracteres'
        ]);

        // Busca de usuário
        $users = DB::select("SELECT * FROM users WHERE email = ?", [$request->email]);
        
        // Se o usuário não existir vai dar erro
        if (empty($users)){
            return response()->json([
                'message' => 'Email ou senha incorretos'], 401);
        }

        // Se senha estiver errada, vai dar erro
        if (!Hash::check($request->password, $users[0]->password)) {
            return response()->json([
                'message' => 'Email ou senha incorretos'], 401);
        }

        // Criação do token
        $user = User::find($users[0]->id);
        $token = $user->createToken('login')->plainTextToken;

        // Retornando sucesso, caso passe de todas as validações
        return response()->json([
            'token' => $token,
            'message' => 'Login realizado com sucesso'
        ]);
    }
}
