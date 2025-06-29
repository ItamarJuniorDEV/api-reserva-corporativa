<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::insert("INSERT INTO users (name, email, password, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())",
            ['João Silva', 'joao@empresa.com', Hash::make('12345678')]
        );

        DB::insert("INSERT INTO users (name, email, password, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())",
            ['Maria Santos', 'maria@empresa.com', Hash::make('12345678')]
        );
        $this->call([
            RecursosSeeder::class,
        ]);
    }
}