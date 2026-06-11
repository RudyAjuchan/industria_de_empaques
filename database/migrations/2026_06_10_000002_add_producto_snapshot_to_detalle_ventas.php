<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detalle_ventas', function (Blueprint $table) {
            $table->string('producto_sku', 50)->nullable()->after('productos_id');
            $table->string('producto_nombre')->nullable()->after('producto_sku');
            $table->string('producto_tipo')->nullable()->after('producto_nombre');
            $table->string('producto_tipo_producto')->nullable()->after('producto_tipo');
            $table->decimal('producto_alto', 10, 2)->nullable()->after('producto_tipo_producto');
            $table->decimal('producto_ancho', 10, 2)->nullable()->after('producto_alto');
            $table->decimal('producto_fuelle', 10, 2)->nullable()->after('producto_ancho');
            $table->text('producto_descripcion')->nullable()->after('producto_fuelle');
        });

        DB::table('detalle_ventas as dv')
            ->join('productos as p', 'p.id', '=', 'dv.productos_id')
            ->select([
                'dv.id',
                'p.sku as producto_sku',
                'p.nombre as producto_nombre',
                'p.tipo as producto_tipo',
                'p.tipo_producto as producto_tipo_producto',
                'p.alto as producto_alto',
                'p.ancho as producto_ancho',
                'p.fuelle as producto_fuelle',
                'p.descripcion as producto_descripcion',
            ])
            ->orderBy('dv.id')
            ->chunk(500, function ($detalles) {
                foreach ($detalles as $detalle) {
                    DB::table('detalle_ventas')
                        ->where('id', $detalle->id)
                        ->update([
                            'producto_sku' => $detalle->producto_sku,
                            'producto_nombre' => $detalle->producto_nombre,
                            'producto_tipo' => $detalle->producto_tipo,
                            'producto_tipo_producto' => $detalle->producto_tipo_producto,
                            'producto_alto' => $detalle->producto_alto,
                            'producto_ancho' => $detalle->producto_ancho,
                            'producto_fuelle' => $detalle->producto_fuelle,
                            'producto_descripcion' => $detalle->producto_descripcion,
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('detalle_ventas', function (Blueprint $table) {
            $table->dropColumn([
                'producto_sku',
                'producto_nombre',
                'producto_tipo',
                'producto_tipo_producto',
                'producto_alto',
                'producto_ancho',
                'producto_fuelle',
                'producto_descripcion',
            ]);
        });
    }
};
