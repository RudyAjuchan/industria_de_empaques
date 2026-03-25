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
            $table->float('alto')->nullable();
            $table->float('ancho')->nullable();
            $table->float('fuelle')->nullable();
            $table->string('tipo')->nullable();
            $table->foreignId('paginas_id')->constrained('paginas');
            // tipo de producto
            $table->enum('tipo_producto', ['personalizado', 'simple'])->default('personalizado');
            // precio base para productos simples
            $table->decimal('precio_base', 12, 2)->nullable();
            $table->text('descripcion')->nullable();
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
