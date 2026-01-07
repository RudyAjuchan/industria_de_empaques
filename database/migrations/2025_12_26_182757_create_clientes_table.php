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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('genero');            
            $table->string('dpi')->nullable();
            $table->foreignId('municipios_id')->nullable()->constrained('municipios');
            $table->text('pais')->nullable();
            $table->text('estado_pais')->nullable();
            $table->text('ciudad_pais')->nullable();
            $table->text('direccion')->nullable();
            $table->string('nit')->nullable();
            $table->integer('estado')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
