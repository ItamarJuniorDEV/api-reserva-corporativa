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
}