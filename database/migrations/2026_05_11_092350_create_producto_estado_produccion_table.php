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
        Schema::create('producto_estado_produccion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('productos_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('estado_produccions_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->integer('orden');
            $table->timestamps();
            $table->unique(
                ['productos_id', 'estado_produccions_id'],
                'producto_estado_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('producto_estado_produccion');
    }
};
