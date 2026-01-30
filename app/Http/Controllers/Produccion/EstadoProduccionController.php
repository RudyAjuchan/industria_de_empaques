<?php

namespace App\Http\Controllers\Produccion;

use App\Http\Controllers\Controller;
use App\Models\DetalleVenta;
use App\Models\HistorialEstadoProduccion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EstadoProduccionController extends Controller
{
    public function cambiarProceso(Request $request, DetalleVenta $detalleVenta)
    {
        $request->validate([
            'proceso_estado_produccions_id' => 'required|exists:proceso_estado_produccions,id',
            'observacion' => 'nullable|string',
        ]);

        $actual = $detalleVenta->estadoActual;

        $actual->update([
            'fecha_fin' => now(),
        ]);

        HistorialEstadoProduccion::create([
            'detalle_ventas_id' => $detalleVenta->id,
            'estado_produccions_id' => $actual->estado_produccions_id,
            'proceso_estado_produccions_id' => $request->proceso_estado_produccions_id,
            'users_id' => Auth::user()->id,
            'fecha_inicio' => now(),
            'observacion' => $request->observacion,
        ]);

        return response()->json(['ok' => true]);
    }

    public function cambiarEstado(Request $request, DetalleVenta $detalleVenta)
    {
        $request->validate([
            'estado_produccions_id' => 'required|exists:estado_produccions,id',
            'proceso_estado_produccions_id' => 'nullable|exists:proceso_estado_produccions,id',
            'observacion' => 'nullable|string',
        ]);

        $actual = $detalleVenta->estadoActual;
        $actual->update(['fecha_fin' => now()]);

        HistorialEstadoProduccion::create([
            'detalle_ventas_id' => $detalleVenta->id,
            'estado_produccions_id' => $request->estado_produccions_id,
            'proceso_estado_produccions_id' => $request->proceso_estado_produccions_id,
            'users_id' => Auth::user()->id,
            'fecha_inicio' => now(),
            'observacion' => $request->observacion,
        ]);

        return response()->json(['ok' => true]);
    }
}
