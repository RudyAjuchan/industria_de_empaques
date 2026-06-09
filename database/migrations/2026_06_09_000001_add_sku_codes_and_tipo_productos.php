<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('paginas', function (Blueprint $table) {
            $table->string('codigo', 10)->nullable()->unique()->after('nombre');
        });

        Schema::create('tipo_productos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('codigo', 10)->unique();
            $table->integer('estado')->default(1);
            $table->timestamps();
        });

        Schema::table('productos', function (Blueprint $table) {
            $table->string('sku', 50)->nullable()->unique()->after('id');
            $table->foreignId('tipo_productos_id')
                ->nullable()
                ->after('tipo')
                ->constrained('tipo_productos')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tipo_productos_id');
            $table->dropColumn('sku');
        });

        Schema::dropIfExists('tipo_productos');

        Schema::table('paginas', function (Blueprint $table) {
            $table->dropColumn('codigo');
        });
    }
};
