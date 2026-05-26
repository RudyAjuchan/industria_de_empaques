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
        Schema::table('promocions', function (Blueprint $table) {
            // HERO
            $table->string('titulo')->nullable()->after('nombre');
            $table->text('descripcion')->nullable()->after('titulo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promocions', function (Blueprint $table) {
            $table->dropColumn([
                'titulo',
                'descripcion'
            ]);
        });
    }
};
