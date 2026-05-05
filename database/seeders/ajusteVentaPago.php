<?php

namespace Database\Seeders;

use App\Models\Pago;
use App\Models\Venta;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ajusteVentaPago extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ventas = Venta::where('cantidad_deposito', '>', 0)->get();

        foreach ($ventas as $venta) {
            Pago::create([
                'ventas_id' => $venta->id,
                'monto' => $venta->cantidad_deposito,
                'metodo_pago' => 'Depósito inicial',
                'referencia' => $venta->no_deposito,
                'created_at' => $venta->created_at,
            ]);
        }
    }
}
