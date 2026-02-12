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
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendedor_id')->constrained('users');
            $table->foreignId('clientes_id')->constrained('clientes');
            $table->foreignId('bancos_id')->constrained('bancos');
            $table->string('serie', 10);
            $table->unsignedBigInteger('numero');
            $table->unique(['serie', 'numero']);
            $table->date('fecha_entrega');
            $table->string('tipo_pago');
            $table->string('no_deposito')->nullable();
            $table->decimal('cantidad_deposito', 12, 2)->nullable();
            $table->decimal('pendiente_pagar', 12, 2)->nullable();
            $table->decimal('costo_logo', 12, 2)->nullable();
            $table->decimal('subtotal', 12, 2);
            $table->decimal('descuento', 12, 2);
            $table->decimal('promociones', 12, 2);
            $table->decimal('costo_envio', 12, 2);
            $table->decimal('total', 12, 2);
            $table->enum('estado', [
                'pendiente',   // reservado
                'emitida',     // válida (interna o SAT)
                'error',       // fallo técnico
                'anulada'      // anulada legalmente
            ])->default('emitida'); // hoy emite directo
            $table->string('estado_produccion', 30)->default('sin_iniciar');
            // Campos SAT (para el futuro)
            $table->string('sat_uuid')->nullable();
            $table->timestamp('sat_fecha')->nullable();
            $table->string('sat_certificador')->nullable();
            $table->json('sat_respuesta')->nullable();
            $table->timestamps();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
