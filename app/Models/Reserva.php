<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Reserva extends Model
{
    protected $table = 'reservas';
    protected $fillable = [
        'recurso_id',
        'usuario_id',
        'data_inicio',
        'data_fim'
    ];

    /**
     * Buscar reservas do usuário com informações do recurso
     * 
     * r = reservas (apelido)
     * rec = recursos (apelido)
     * reservas.recurso_id = recursos.id  (ligação entre as tabelas)
     *
     * SELECT pega:
     * - todas as colunas de reservas (r.*)
     * - o nome do recurso (rec.nome como recurso_nome)
     * - o tipo do recurso (rec.tipo)
     * - JOIN recursos rec ON r.recurso_id = rec.id 
     *     - Junta as tabelas reservas("r") e recursos("rec")
     *       Onde valor de reservas.recurso_id e recursos.id
     *       São iguais.
     * - Total:
     *     - Pega todos os campos da tabela reservas + nome e tipo da tabela recursos
     *     - Junta essas tabelas onde o campo recurso_id = campo id da tabela recursos
     *     - Filtra só as reservas onde usuario_id = usuário que fez a requisição
     *     - Ordena o resultado pelo campo data_inicio
     *     - Da mais recente para a mais antiga
     */
    public static function buscarMinhasReservas($userId)
    {
        return DB::select("
            SELECT r.*, rec.nome as recurso_nome, rec.tipo
            FROM reservas r
            JOIN recursos rec ON r.recurso_id = rec.id
            WHERE r.usuario_id = ?
            ORDER BY r.data_inicio DESC",
            [$userId]
        );
    }

    /**
     * Buscar reserva específica verificando se pertence ao usuário
     * Usado para validar antes de deletar
     */
    public static function buscarPorIdEUsuario($id, $userId)
    {
        return DB::select("
            SELECT * FROM reservas
            WHERE id = ? AND usuario_id = ?",
            [$id, $userId]
        );
    }

    /**
     * Deletar reserva por ID
     */
    public static function deletarPorId($id)
    {
        return DB::delete("DELETE FROM reservas WHERE id = ?", [$id]);
    }

    /**
     * Buscar reservas de um recurso em uma data específica
     * Retorna apenas os horários (data_inicio e data_fim)
     * DATE() converte datetime para apenas data para comparação
     */
    public static function buscarPorRecursoEData($recursoId, $data)
    {
        return DB::select("
            SELECT data_inicio, data_fim
            FROM reservas
            WHERE recurso_id = ?
            AND DATE(data_inicio) = ?
            ORDER BY data_inicio",
            [$recursoId, $data]
        );
    }

    /**
     * Verificar conflitos de horário para um recurso
     * 
     * Verifica três cenários de sobreposição:
     * 1. Nova reserva começa antes e termina durante uma existente
     * 2. Nova reserva começa durante e termina depois de uma existente
     * 3. Nova reserva está completamente dentro de uma existente
     * 
     * @return int Total de conflitos encontrados
     */
    public static function verificarConflitos($recursoId, $dataInicio, $dataFim)
    {
        $result = DB::select("
            SELECT COUNT(*) as total
            FROM reservas
            WHERE recurso_id = ?
            AND ((data_inicio < ? AND data_fim > ?)
                OR (data_inicio < ? AND data_fim > ?)
                OR (data_inicio >= ? AND data_fim <= ?))",
            [$recursoId, $dataFim, $dataInicio, $dataFim, $dataFim, $dataInicio, $dataFim]
        );
        return $result[0]->total ?? 0;
    }

    /**
     * Criar nova reserva
     * Usa NOW() para preencher created_at e updated_at automaticamente
     */
    public static function criar($recursoId, $userId, $dataInicio, $dataFim)
    {
        DB::insert("
            INSERT INTO reservas (recurso_id, usuario_id, data_inicio, data_fim, created_at, updated_at)
            VALUES (?, ?, ?, ?, NOW(), NOW())",
            [$recursoId, $userId, $dataInicio, $dataFim]
        );
        
        return DB::getPdo()->lastInsertId();
    }
}