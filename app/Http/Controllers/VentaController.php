<?php

namespace App\Http\Controllers;

use App\Models\DetalleVenta;
use App\Models\Pagina;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VentaController extends Controller
{
    /**
     * Listar ventas
     */
    public function index()
    {
        return Venta::with(['cliente', 'vendedor', 'banco'])
            ->orderBy('id', 'desc')
            ->get();
    }

    public function getPaginas(){
        return Pagina::where('estado', 1)
            ->orderBy('nombre')
            ->get();
    }

    /**
     * Guardar nueva venta
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {

            $venta = Venta::create([
                'vendedor_id' => $request->vendedor_id,
                'clientes_id' => $request->clientes_id,
                'bancos_id' => $request->bancos_id,
                'serie' => $request->serie,
                'numero' => $request->numero,
                'fecha_entrega' => $request->fecha_entrega,
                'tipo_pago' => $request->tipo_pago,
                'no_deposito' => $request->no_deposito,
                'cantidad_deposito' => $request->cantidad_deposito,
                'pendiente_pagar' => $request->pendiente_pagar,
                'costo_logo' => $request->costo_logo,
                'subtotal' => $request->subtotal,
                'descuento' => $request->descuento,
                'promociones' => $request->promociones,
                'costo_envio' => $request->costo_envio,
                'total' => $request->total,
                'proceso_estado_produccions_id' => $request->proceso_estado_produccions_id,
                'estado' => 'emitida'
            ]);

            foreach ($request->detalle as $item) {
                DetalleVenta::create([
                    'ventas_id' => $venta->id,
                    'productos_id' => $item['productos_id'],
                    'tipo_agarradors_id' => $item['tipo_agarradors_id'],
                    'tipo_papels_id' => $item['tipo_papels_id'],
                    'color_agarrador' => $item['color_agarrador'],
                    'detalle_impresion' => $item['detalle_impresion'],
                    'nombre_logo' => $item['nombre_logo'],
                    'precio' => $item['precio'],
                    'cantidad' => $item['cantidad'],
                    'total' => $item['total'],
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Venta registrada correctamente',
                'venta' => $venta->load(['cliente', 'detalle'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al registrar venta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar una venta
     */
    public function show(Venta $venta)
    {
        return $venta->load(['cliente', 'vendedor', 'banco', 'detalle']);
    }

    /**
     * Anular venta
     */
    public function destroy(Venta $venta)
    {
        $venta->update([
            'estado' => 'anulada'
        ]);

        return response()->json([
            'message' => 'Venta anulada correctamente'
        ]);
    }

    /**
     * Exportar PDF (opcional)
     */
    public function exportPdf()
    {
        return Venta::all(); // aquí luego puedes adaptar igual que hiciste con clientes
    }

    /**
     * Exportar Excel (opcional)
     */
    public function exportExcel()
    {
        return Venta::all();
    }
}
