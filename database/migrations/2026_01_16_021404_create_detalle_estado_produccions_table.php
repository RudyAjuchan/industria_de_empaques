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
        Schema::create('detalle_estado_produccions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estado_produccions_id')->constrained('estado_produccions');
            $table->string('tipo');
            $table->string('nombre');
            $table->string('valor_string')->nullable();
            $table->double('valor_double')->nullable();
            $table->integer('valor_integer')->nullable();
            $table->date('valor_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_estado_produccions');
    }
};
