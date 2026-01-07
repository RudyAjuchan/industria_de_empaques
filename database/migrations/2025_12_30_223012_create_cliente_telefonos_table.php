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
        Schema::create('cliente_telefonos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clientes_id')->constrained('clientes');
            $table->string('telefono_pais')->nullable();
            $table->string('telefono_codigo_pais')->nullable();
            $table->string('telefono_numero')->nullable();
            $table->integer('estado')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cliente_telefonos');
    }
};
