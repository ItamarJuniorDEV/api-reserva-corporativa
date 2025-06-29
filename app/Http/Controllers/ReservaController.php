<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reserva;
use App\Models\Recurso;

class ReservaController extends Controller
{
    public function minhasReservas(Request $request)
    {
        try {
            // Busca o ID do usuário autenticado
            $userId = $request->user()->id;

            // Busca todas as reservas do usuário com informações do recurso
            $reservas = Reserva::buscarMinhasReservas($userId);

            return response()->json($reservas);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao buscar reservas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id, Request $request)
    {
        try {
            $userId = $request->user()->id;
            
            // Verificar se a reserva pertence ao usuário antes de deletar
            $reserva = Reserva::buscarPorIdEUsuario($id, $userId);

            if(empty($reserva)) {
                return response()->json([
                    'message' => 'Reserva não encontrada'
                ], 404);
            }
            
            // Deletar a reserva
            Reserva::deletarPorId($id);
            
            return response()->json([
                'message' => 'Reserva cancelada com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao cancelar reserva',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            // Validar formato dos dados recebidos
            $request->validate([
                'recurso_id' => 'required|integer',
                'data_inicio' => 'required|date_format:Y-m-d H:i:s',
                'data_fim' => 'required|date_format:Y-m-d H:i:s|after:data_inicio'
            ], [
                'recurso_id.required' => 'O recurso é obrigatório',
                'data_inicio.required' => 'Data de início é obrigatória',
                'data_inicio.date_format' => 'Data início deve estar no formato Y-m-d H:i:s',
                'data_fim.required' => 'Data de fim é obrigatória',
                'data_fim.date_format' => 'Data fim deve estar no formato Y-m-d H:i:s',
                'data_fim.after' => 'Data final deve ser depois da data início'
            ]);

            // Capturar dados da requisição
            $userId = $request->user()->id;
            $recursoId = $request->recurso_id;
            $dataInicio = $request->data_inicio;
            $dataFim = $request->data_fim;
            
            // Verificar se o recurso existe e está disponível para reserva
            $recurso = Recurso::buscarAtivoById($recursoId);
            
            if (empty($recurso)) {
                return response()->json([
                    'message' => 'Recurso não encontrado ou inativo'
                ], 404);
            }

            // REGRA DE NEGÓCIO: Não permitir reservas no passado
            if (strtotime($dataInicio) < time()) {
                return response()->json([
                    'message' => 'Não é possivel reservar no passado'
                ], 422);
            }

            // REGRA DE NEGÓCIO: Validar duração da reserva
            $duracao = (strtotime($dataFim) - strtotime($dataInicio)) / 60; // converter para minutos
            
            if ($duracao < 30) {
                return response()->json([
                    'message' => 'Reserva mínima de 30 minutos'
                ], 422);
            }
            
            if ($duracao > 240) { // 4 horas em minutos
                return response()->json([
                    'message' => 'Reserva máxima de 4 horas'
                ], 422);
            }

            // Verificar se não há conflitos de horário
            $conflitos = Reserva::verificarConflitos($recursoId, $dataInicio, $dataFim);

            if ($conflitos > 0) {
                return response()->json([
                    'message' => 'Já existe uma reserva neste horário'
                ], 409); 
            }

            // Criar a reserva se passou por todas as validações
            $reservaId = Reserva::criar($recursoId, $userId, $dataInicio, $dataFim);

            return response()->json([
                'message' => 'Reserva criada com sucesso',
                'reserva_id' => $reservaId
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao criar reserva',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}