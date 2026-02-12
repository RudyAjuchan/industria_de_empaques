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
                'detalleVenta.historialEstados.estadoProduccion',
                'detalleVenta.historialEstados.procesoEstado',
                'detalleVenta.historialEstados.usuario',
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

        // Puede ser null si ya estamos en proceso
        $estadoActivo = $detalleVenta->getEstadoActivo();

        // Proceso activo (si existe)
        $procesoActivo = $detalleVenta->getProcesoActivo();

        // Caso inválido: no hay estado en cola NI proceso en curso
        if (!$estadoActivo && !$procesoActivo) {
            return response()->json([
                'message' => 'El producto no tiene un estado válido para iniciar o cambiar proceso'
            ], 422);
        }

        // Determinar el estado real (desde donde se trabaja)
        $estadoProduccionId = $estadoActivo
            ? $estadoActivo->estado_produccions_id
            : $procesoActivo->estado_produccions_id;

        //Cerrar espera SOLO si existe
        if ($estadoActivo) {
            $estadoActivo->update([
                'fecha_fin' => now()
            ]);
        }

        //Cerrar proceso activo SOLO si existe

        if ($procesoActivo) {
            $procesoActivo->update([
                'fecha_fin' => now()
            ]);
        }
        //Crear nuevo proceso

        HistorialEstadoProduccion::create([
            'detalle_ventas_id' => $detalleVenta->id,
            'estado_produccions_id' => $estadoProduccionId,
            'proceso_estado_produccions_id' => $request->proceso_estado_produccions_id,
            'users_id' => Auth::id(),
            'fecha_inicio' => now(),
            'observacion' => $request->observacion,
            'tipo_evento' => $procesoActivo
                ? 'cambio_proceso'
                : 'inicio_proceso',
        ]);

        $detalleVenta->venta->recalcularEstadoProduccion();

        return response()->json([
            'message' => 'Proceso actualizado correctamente'
        ]);
    }


    public function finalizarProceso(Request $request, DetalleVenta $detalleVenta)
    {
        $request->validate([
            'observacion' => 'nullable|string'
        ]);

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

        //Cerrar el estado actual (SIEMPRE)
        $estadoActivo->update([
            'fecha_fin' => now(),
            'observacion' => $request->observacion,
            'users_id' => Auth::id(),
        ]);

        //Evento histórico de finalización (opcional)
        HistorialEstadoProduccion::create([
            'detalle_ventas_id' => $detalleVenta->id,
            'estado_produccions_id' => $estadoActivo->estado_produccions_id,
            'users_id' => Auth::id(),
            'fecha_inicio' => now(),
            'fecha_fin' => now(),
            'tipo_evento' => 'finalizacion_estado',
            'observacion' => $request->observacion,
        ]);

        //Solo si HAY siguiente estado, crear entrada
        if ($siguienteEstado) {
            HistorialEstadoProduccion::create([
                'detalle_ventas_id' => $detalleVenta->id,
                'estado_produccions_id' => $siguienteEstado->id,
                'fecha_inicio' => now(),
                'tipo_evento' => 'entrada_estado',
            ]);
        }

        //Recalcular estado de la venta
        $detalleVenta->venta->recalcularEstadoProduccion();

        return response()->json([
            'message' => $siguienteEstado
                ? 'Producto enviado al siguiente estado'
                : 'Producto finalizado completamente'
        ]);
    }
}
