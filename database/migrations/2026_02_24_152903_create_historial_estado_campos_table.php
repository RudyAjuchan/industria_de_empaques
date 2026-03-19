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
        Schema::create('historial_estado_campos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('historial_estado_produccions_id')
                ->constrained('historial_estado_produccions')
                ->cascadeOnDelete();

            $table->foreignId('detalle_estado_produccions_id')
                ->constrained('detalle_estado_produccions');

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
        Schema::dropIfExists('historial_estado_campos');
    }
};
