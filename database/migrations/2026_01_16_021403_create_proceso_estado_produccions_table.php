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
        Schema::create('proceso_estado_produccions', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->foreignId('estado_produccions_id')->constrained('estado_produccions');
            $table->integer('estado')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proceso_estado_produccions');
    }
};
