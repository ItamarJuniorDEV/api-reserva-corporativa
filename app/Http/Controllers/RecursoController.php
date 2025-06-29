<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Recurso;
use App\Models\Reserva;

class RecursoController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Configuração da paginação
            $perPage = 10; // Recursos por página
            $page = $request->get('page', 1); // Página atual (padrão: 1)
            $offset = ($page - 1) * $perPage; // Calcular offset para SQL

            // Buscar recursos ativos com paginação
            $recursos = Recurso::buscarAtivosComPaginacao($perPage, $offset);
            
            // Contar total para informações de paginação
            $total = Recurso::contarAtivos();
            
            // Montar resposta com dados e metadados de paginação
            return response()->json([
                'recursos' => $recursos,
                'total_registros' => $total,
                'por_pagina' => $perPage,
                'pagina_atual' => $page,
                'ultima_pagina' => ceil($total / $perPage) // Arredondar para cima
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao buscar recursos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function disponibilidade($id, Request $request)
    {
        try {
            // Validar formato da data
            $request->validate([
                'data' => 'required|date_format:Y-m-d'
            ],[
                'data.required' => 'A data é obrigatória',
                'data.date_format' => 'A data deve estar no formato AAAA-MM-DD'
            ]);
            
            $data = $request->get('data');

            // Buscar horários já reservados para o recurso na data
            $reservas = Reserva::buscarPorRecursoEData($id, $data);

            // Retornar lista de horários ocupados
            return response()->json([
                'recurso_id' => $id,
                'data' => $data,
                'horarios_ocupados' => $reservas
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao verificar disponibilidade',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}