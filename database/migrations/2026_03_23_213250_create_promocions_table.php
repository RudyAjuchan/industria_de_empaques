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
        Schema::create('promocions', function (Blueprint $table) {
            $table->id();

            $table->string('nombre');
            $table->enum('tipo', ['porcentaje', 'monto']);
            $table->decimal('valor', 10, 2);

            $table->date('fecha_inicio');
            $table->date('fecha_fin');

            $table->enum('aplica_a', ['producto', 'carrito'])->default('producto');

            $table->boolean('activo')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promocions');
    }
};
