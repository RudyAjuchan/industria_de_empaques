<?php

namespace App\Http\Controllers\Produccion;

use App\Http\Controllers\Controller;
use App\Models\DetalleVenta;
use App\Models\EstadoProduccion;
use App\Models\HistorialEstadoProduccion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProduccionController extends Controller
{
    public function estadosAnteriores(HistorialEstadoProduccion $tarea)
    {
        $estadoActual = EstadoProduccion::find($tarea->estado_produccions_id);

        return EstadoProduccion::where('orden', '<', $estadoActual->orden)
            ->orderByDesc('orden')
            ->get();
    }

    public function regresarEstado(Request $request, DetalleVenta $detalleVenta)
    {
        $request->validate([
            'estado_destino_id' => 'required|exists:estado_produccions,id',
            'observacion' => 'nullable|string'
        ]);

        // Estado actual activo
        $estadoActivo = $detalleVenta->getEstadoActual();

        if (!$estadoActivo) {
            return response()->json([
                'message' => 'No hay estado activo para regresar'
            ], 422);
        }

        $estadoActual = EstadoProduccion::find($estadoActivo->estado_produccions_id);
        $estadoDestino = EstadoProduccion::find($request->estado_destino_id);

        // No permitir regresar al mismo o superior
        if ($estadoDestino->orden >= $estadoActual->orden) {
            return response()->json([
                'message' => 'Solo se puede regresar a estados anteriores'
            ], 422);
        }

        // No permitir regresar al primero
        if ($estadoActual->orden == 1) {
            return response()->json([
                'message' => 'No se puede regresar desde el primer estado'
            ], 422);
        }

        // Cerrar proceso activo si existe
        $procesoActivo = $detalleVenta->getProcesoActivo();

        if ($procesoActivo) {
            $procesoActivo->update([
                'fecha_fin' => now()
            ]);
        }

        // Cerrar estado actual
        $estadoActivo->update([
            'fecha_fin' => now(),
            'observacion' => $request->observacion,
            'users_id' => Auth::id(),
        ]);

        // Registrar evento de regreso
        HistorialEstadoProduccion::create([
            'detalle_ventas_id' => $detalleVenta->id,
            'estado_produccions_id' => $estadoActual->id,
            'users_id' => Auth::id(),
            'fecha_inicio' => now(),
            'fecha_fin' => now(),
            'tipo_evento' => 'regreso_estado',
            'observacion' => $request->observacion,
        ]);

        // Crear nueva entrada al estado destino
        HistorialEstadoProduccion::create([
            'detalle_ventas_id' => $detalleVenta->id,
            'estado_produccions_id' => $estadoDestino->id,
            'fecha_inicio' => now(),
            'tipo_evento' => 'entrada_estado',
        ]);

        // Recalcular venta
        $detalleVenta->venta->recalcularEstadoProduccion();

        return response()->json([
            'message' => 'Estado regresado correctamente'
        ]);
    }

}
