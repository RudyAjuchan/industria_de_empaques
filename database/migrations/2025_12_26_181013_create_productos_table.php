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
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->float('alto');
            $table->float('ancho');
            $table->float('fuelle');
            $table->foreignId('tipo_agarradors_id')->constrained('tipo_agarradors');
            $table->foreignId('tipo_papels_id')->constrained('tipo_papels');
            $table->foreignId('paginas_id')->constrained('paginas');
            $table->integer('estado')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
