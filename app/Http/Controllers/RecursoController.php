<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecursoController extends Controller
{
    public function index(Request $request)
    {
        // Paginação Padrão
        // Cada Página vai mostrar 10 recursos
        $perPage = 10;
        // Pega o número da página que o usuário pediu na URL
        $page = $request->get('page', 1)
        // Se o usuário não enviar nada, usa 1 como padrão
        $offset = ($page - 1) * $perPage;

        // Buscando os registros ativos
        $recursos = DB::select("
            SELECT * FROM recursos
            WHERE ativo = 1 LIMIT ? OFFSET ?",
            [$perPage, $offset] 
    );
        // Contando o total para paginação
        $total = DB:select("
            SELECT COUNT(*) as total 
            FROM recursos WHERE ativo = 1")
            [0]->total;
        
        // Retornando a reposta pra requisição
        'recursos' => $recursos,
        'total_registros' => $total,
        'por_pagina' => $perPage,
        'pagina_atual' => $page,

        // ceil é uma função php que arredonda pra cima
        'ultima_pagina' => ceil($total / $page)
    }

    public function disponibilidade($id, Request $request)
    {
        // Verifica se a requisição tem o campo data e se está no formato correto
        $request->validate([
            'data' => 'required|date_format:Y-m-d'
        ]);
        // Pega o valor da data enviada na requisção
        $data = $request->get('data');

        // Buscar reservas do recurso na data específica
        $reservas = DB::select("
            SELECT data_inicio, data_fim
            FROM reservas
            WHERE recurso_id = ?
            AND DATE(data_inicio) = ?
            ORDER BY data_inicio",
            [$id, $data]
        );

        // Retornando a resposta pra requisição
        return response->json([
            'recurso_id' => $id,
            'data' => $data,
            'horarios_ocupados' => $reservas
        ]);
    }
}
