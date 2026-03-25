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
        Schema::create('detalle_ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ventas_id')->constrained('ventas');
            $table->foreignId('productos_id')->constrained('productos');
            $table->foreignId('tipo_agarradors_id')->nullable()->constrained('tipo_agarradors');
            $table->foreignId('tipo_papels_id')->nullable()->constrained('tipo_papels');
            $table->foreignId('proceso_estado_produccions_id')->constrained('proceso_estado_produccions');
            $table->string('color_agarrador')->nullable();
            $table->text('detalle_impresion')->nullable();
            $table->string('nombre_logo')->nullable();
            $table->string('logo_path')->nullable();
            $table->decimal('precio', 12, 2);
            $table->integer('cantidad');
            $table->decimal('total', 12, 2);
            $table->json('promocion_aplicada')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_ventas');
    }
};
