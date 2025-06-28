<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recurso_id');
            $table->unsignedBigInteger('usuario_id');
            $table->datetime('data_inicio');
            $table->datetime('data_fim');
            $table->timestamps();
            $table->foreign('recurso_id')->references('id')->on('recursos')->onDelete('cascade');    
            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('cascade');        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};
