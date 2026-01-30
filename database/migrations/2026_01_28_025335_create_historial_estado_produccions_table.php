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
        Schema::create('historial_estado_produccions', function (Blueprint $table) {
            $table->id();

            // Producto específico de la venta
            $table->foreignId('detalle_ventas_id')
                ->constrained('detalle_ventas')
                ->cascadeOnDelete();

            // Estado macro
            $table->foreignId('estado_produccions_id')
                ->constrained('estado_produccions');

            // Subestado / proceso
            $table->foreignId('proceso_estado_produccions_id')
                ->nullable()
                ->constrained('proceso_estado_produccions', 'id', 'hist_proc_prod_fk');

            // Usuario responsable del cambio
            $table->foreignId('users_id')
                ->constrained('users');

            // Control de tiempo
            $table->timestamp('fecha_inicio');
            $table->timestamp('fecha_fin')->nullable();

            // Observaciones opcionales
            $table->text('observacion')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historial_estado_produccions');
    }
};
