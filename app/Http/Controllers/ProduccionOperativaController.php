<?php

namespace App\Http\Controllers;

use App\Models\DetalleVenta;
use App\Models\EstadoProduccion;
use App\Models\HistorialEstadoProduccion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProduccionOperativaController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        // Obtener el área asignada al usuario
        $estado = EstadoProduccion::where('users_id', $userId)->first();

        if (!$estado) {
            return response()->json([
                'message' => 'El usuario no tiene un área de producción asignada'
            ], 422);
        }

        //Tareas pendientes, solo entradas al estado
        $tareas = HistorialEstadoProduccion::where('estado_produccions_id', $estado->id)
            ->whereNull('fecha_fin')
            ->with([
                'detalleVenta.producto',
                'detalleVenta.venta',
                'procesoEstado'
            ])
            ->orderBy('fecha_inicio')
            ->get();


        return response()->json([
            'estado' => $estado,
            'tareas' => $tareas
        ]);
    }



    public function iniciarProceso(Request $request, DetalleVenta $detalleVenta)
    {
        $request->validate([
            'proceso_estado_produccions_id' => 'required|exists:proceso_estado_produccions,id',
            'observacion' => 'nullable|string'
        ]);

        // Estado activo REAL (entrada_estado vigente)
        $estadoActual = $detalleVenta->getEstadoActual();

        if (!$estadoActual) {
            return response()->json([
                'message' => 'El producto no tiene estado activo'
            ], 422);
        }

        // Obtener proceso activo ANTES de cerrar nada
        $procesoActivo = $detalleVenta->getProcesoActivo();

        //Cerrar la espera (entrada_estado)

        $detalleVenta->historialEstados()
            ->where('estado_produccions_id', $estadoActual->estado_produccions_id)
            ->where('tipo_evento', 'entrada_estado')
            ->whereNull('fecha_fin')
            ->update([
                'fecha_fin' => now()
            ]);
        //Cerrar proceso activo (si existe)
        if ($procesoActivo) {
            $procesoActivo->update([
                'fecha_fin' => now()
            ]);
        }

        //Crear nuevo proceso

        HistorialEstadoProduccion::create([
            'detalle_ventas_id' => $detalleVenta->id,
            'estado_produccions_id' => $estadoActual->estado_produccions_id,
            'proceso_estado_produccions_id' => $request->proceso_estado_produccions_id,
            'users_id' => Auth::id(),
            'fecha_inicio' => now(),
            'observacion' => $request->observacion,
            'tipo_evento' => $procesoActivo
                ? 'cambio_proceso'
                : 'inicio_proceso',
        ]);

        return response()->json([
            'message' => 'Proceso actualizado correctamente'
        ]);
    }

    public function finalizarProceso(Request $request, DetalleVenta $detalleVenta)
    {
        $request->validate([
            'observacion' => 'nullable|string'
        ]);

        //Obtener el estado activo REAL
        $estadoActivo = $detalleVenta->getEstadoActual();

        if (!$estadoActivo) {
            return response()->json([
                'message' => 'No hay estado activo'
            ], 422);
        }

        $estadoActual = EstadoProduccion::find($estadoActivo->estado_produccions_id);

        $siguienteEstado = EstadoProduccion::where('orden', '>', $estadoActual->orden)
            ->orderBy('orden')
            ->first();

        if (!$siguienteEstado) {
            return response()->json([
                'message' => 'Este producto ya está en el último estado'
            ], 422);
        }

        //CERRAR el estado actual
        $estadoActivo->update([
            'fecha_fin' => now(),
            'observacion' => $request->observacion,
            'users_id' => Auth::id(),
        ]);

        // Crear evento de finalización
        HistorialEstadoProduccion::create([
            'detalle_ventas_id' => $detalleVenta->id,
            'estado_produccions_id' => $estadoActivo->estado_produccions_id,
            'users_id' => Auth::id(),
            'fecha_inicio' => now(),
            'fecha_fin' => now(),
            'tipo_evento' => 'finalizacion_estado',
            'observacion' => $request->observacion,
        ]);

        //CREAR entrada al siguiente estado
        HistorialEstadoProduccion::create([
            'detalle_ventas_id' => $detalleVenta->id,
            'estado_produccions_id' => $siguienteEstado->id,
            'fecha_inicio' => now(),
            'tipo_evento' => 'entrada_estado',
        ]);

        return response()->json([
            'message' => 'Producto enviado al siguiente estado'
        ]);
    }
}
