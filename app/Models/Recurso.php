<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Recurso extends Model
{
    protected $table = 'recursos';
    protected $fillable = [
        'nome',
        'tipo',
        'capacidade',
        'ativo'
    ];

    /**
     * Buscar recursos ativos com paginação
     * LIMIT define quantos registros retornar
     * OFFSET define quantos registros pular
     */
    public static function buscarAtivosComPaginacao($perPage, $offset)
    {
        return DB::select("
            SELECT * FROM recursos 
            WHERE ativo = 1 
            LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );
    }

    /**
     * Contar total de recursos ativos
     * Usado para calcular número de páginas na paginação
     */
    public static function contarAtivos()
    {
        $result = DB::select("
            SELECT COUNT(*) as total 
            FROM recursos 
            WHERE ativo = 1"
        );
        return $result[0]->total ?? 0;
    }

    /**
     * Verificar se recurso existe e está ativo
     * Usado antes de criar uma reserva
     */
    public static function buscarAtivoById($id)
    {
        return DB::select("
            SELECT * FROM recursos 
            WHERE id = ? AND ativo = 1",
            [$id]
        );
    }
}