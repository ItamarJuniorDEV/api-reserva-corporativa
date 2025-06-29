<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservaController extends Controller
{
    public function minhasReservas(Request $request)
    {
        // Busca o ID do usuário autenticado
        $userId = $request->user()->id;

        // Reservas do usuário com info do recurso, por data
        /*
        r = reservas (apelido)
        rec = recursos (apelido)
        reservas.recurso_id = recursos.id  (ligação entre as tabelas)

        SELECT pega:
        - todas as colunas de reservas (r.*)
        - o nome do recurso (rec.nome como recurso_nome)
        - o tipo do recurso (rec.tipo)
        - JOIN recursos rec ON r.recurso_id = rec.id 
            - Junta as tabelas reservas("r") e recursos("rec")
              Onde valor de reservas.recurso_id e recursos.id
              São iguais.
        - Total:
            - Pega todos os campos da tabela reservas + nome e tipo da tabela recursos
            - Junta essas tabelas onde o campo recurso_id = campo id da tabela recursos
            - Filtra só as reservas onde usuario_id = usuário que fez a requisição
            - Ordena o resultado pelo campo data_inicio
            - Da mais recente para a mais antiga
        */
        $reservas = DB::select("
            SELECT r.*, rec.nome as recurso_nome, rec.tipo
            FROM reservas r
            JOIN recursos rec ON r.recurso_id = rec.id
            WHERE r.usuario_id = ?
            ORDER BY r.data_inicio DESC",
            [$userId]
        );

        return response()->json($reservas);
    }

    public function destroy($id, Request $request)
    {
        $userId = $request->user()->id;
        
        // Verificar se a reserva existe e é do usuário
        $reserva = DB::select("
            SELECT * FROM reservas
            WHERE id = ? AND usuario_id = ?",
            [$id, $userId]
        );

        if(empty($reserva)) {
            return response()->json([
                'message' => 'Reserva não encontrada'
            ], 404);
        }
        
        // Deletar a reserva
        DB::delete("DELETE FROM reservas WHERE id = ?", [$id]);
        
        return response()->json([
            'message' => 'Reserva cancelada com sucesso'
        ]);
    }

    public function store(Request $request){

        // Validar dados básicos
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

        // Pega os dados enviados na requisição (Id do usuário, recurso, data início e fim)
        $userId = $request->user()->id;
        $recursoId = $request->recurso_id;
        $dataInicio = $request->data_inicio;
        $dataFim = $request->data_fim;
        
        // Verificar se o recurso existe e se está ativo
        $recurso = DB::select("
            SELECT * FROM recursos
            WHERE id = ? AND ativo = 1",
            [$recursoId]
    );
        if (empty($recurso)) {
            return response()->json([
                'message' => 'Recurso não encontrado ou inativo'
            ], 404);
        }

        // Validar regras de negócio
        // Não pode ser no passado
        if (strtotime($dataInicio) < time()) {
            return response()->json([
                'message' => 'Não é possivel reservar no passado'
            ], 422);
        }

        // Verificar conflitos
        $conflitos = DB::select("
            SELECT COUNT(*) as total
            FROM reservas
            WHERE recurso_id = ?
            AND ((data_inicio < ? AND data_fim > ?)
                OR (data_inicio < ? AND data_fim > ?)
                OR (data_inicio >= ? AND data_fim <= ?))",
            [$recursoId, $dataFim, $dataInicio, $dataFim, $dataFim, $dataInicio, $dataFim]
        );

        if ($conflitos[0]->total > 0) {
            return response()->json([
                'message' => 'Já existe uma reserva neste horário'
            ], 409); 
        }

        // Criar a reserva
        DB::insert("
            INSERT INTO reservas (recurso_id, usuario_id, data_inicio, data_fim, created_at, updated_at)
            VALUES (?, ?, ?, ?, NOW(), NOW())",
            [$recursoId, $userId, $dataInicio, $dataFim]
    );
        // Pegar o ID da reserva criada
        $reservaId = DB::getPdo()->lastInsertId();

        return response()->json([
            'message' => 'Reserva criada com sucesso',
            'reserva_id' => $reservaId
        ], 201);
    }
}