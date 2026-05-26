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
        Schema::create('banners', function (Blueprint $table) {

            $table->id();
            $table->string('imagen');
            $table->enum('tipo_redireccion', [
                'ninguno',
                'producto',
                'tipo',
                'promocion'
            ])->default('ninguno');
            $table->unsignedBigInteger('productos_id')->nullable();
            $table->string('tipo_producto')->nullable();
            $table->unsignedBigInteger('promocions_id')->nullable();
            $table->integer('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
