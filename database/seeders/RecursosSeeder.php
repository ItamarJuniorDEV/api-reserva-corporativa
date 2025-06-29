<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RecursosSeeder extends Seeder
{
    public function run(): void
    {
        // Salas de reunião
        DB::insert("INSERT INTO recursos (nome, tipo, capacidade, ativo, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())", 
            ['Sala de Reunião 101', 'sala', 10, 1]);
        DB::insert("INSERT INTO recursos (nome, tipo, capacidade, ativo, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())", 
            ['Sala de Reunião 102', 'sala', 6, 1]);
        DB::insert("INSERT INTO recursos (nome, tipo, capacidade, ativo, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())", 
            ['Auditório Principal', 'sala', 50, 1]);
        
        // Equipamentos
        DB::insert("INSERT INTO recursos (nome, tipo, capacidade, ativo, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())", 
            ['Projetor EPSON', 'equipamento', null, 1]);
        DB::insert("INSERT INTO recursos (nome, tipo, capacidade, ativo, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())", 
            ['Notebook Dell', 'equipamento', null, 1]);
        
        // Estacionamento
        DB::insert("INSERT INTO recursos (nome, tipo, capacidade, ativo, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())", 
            ['Vaga A1', 'estacionamento', 1, 1]);
        DB::insert("INSERT INTO recursos (nome, tipo, capacidade, ativo, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())", 
            ['Vaga B1', 'estacionamento', 1, 1]);
    }
}