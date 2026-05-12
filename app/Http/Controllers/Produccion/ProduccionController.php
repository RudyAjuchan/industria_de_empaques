<?php

namespace App\Http\Controllers\Produccion;

use App\Http\Controllers\Controller;
use App\Models\DetalleEstadoProduccion;
use App\Models\DetalleVenta;
use App\Models\EstadoProduccion;
use App\Models\HistorialEstadoProduccion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProduccionController extends Controller
{
    public function estadosAnteriores(HistorialEstadoProduccion $tarea)
    {
        /*
        |--------------------------------------------------------------------------
        | DETALLE Y PRODUCTO
        |--------------------------------------------------------------------------
        */
        $detalleVenta = $tarea->detalleVenta;

        if (!$detalleVenta || !$detalleVenta->producto) {
            return response()->json([]);
        }

        /*
        |--------------------------------------------------------------------------
        | ESTADO ACTUAL
        |--------------------------------------------------------------------------
        */
        $estadoActual = EstadoProduccion::find(
            $tarea->estado_produccions_id
        );

        if (!$estadoActual) {
            return response()->json([]);
        }

        /*
        |--------------------------------------------------------------------------
        | FLUJO DEL PRODUCTO
        |--------------------------------------------------------------------------
        */
        $flujo = $detalleVenta->producto
            ->estadosProduccion
            ->sortBy('pivot.orden')
            ->values();

        /*
        |--------------------------------------------------------------------------
        | ESTADO ACTUAL DENTRO DEL FLUJO
        |--------------------------------------------------------------------------
        */
        $flujoActual = $flujo->firstWhere(
            'id',
            $estadoActual->id
        );

        if (!$flujoActual) {
            return response()->json([]);
        }

        /*
        |--------------------------------------------------------------------------
        | SOLO ESTADOS ANTERIORES DEL FLUJO
        |--------------------------------------------------------------------------
        */
        $anteriores = $flujo
            ->filter(function ($estado) use ($flujoActual) {

                return $estado->pivot->orden <
                    $flujoActual->pivot->orden;
            })
            ->sortByDesc('pivot.orden')
            ->values();

        return response()->json($anteriores);
    }

    public function regresarEstado(Request $request, DetalleVenta $detalleVenta)
    {
        $request->validate([
            'estado_destino_id' => 'required|exists:estado_produccions,id',
            'observacion' => 'nullable|string'
        ]);

        /*
        |--------------------------------------------------------------------------
        | ESTADO ACTUAL ACTIVO
        |--------------------------------------------------------------------------
        */
        $estadoActivo = $detalleVenta->getEstadoActual();

        if (!$estadoActivo) {
            return response()->json([
                'message' => 'No hay estado activo para regresar'
            ], 422);
        }

        $estadoActual = EstadoProduccion::find(
            $estadoActivo->estado_produccions_id
        );

        $estadoDestino = EstadoProduccion::find(
            $request->estado_destino_id
        );

        /*
        |--------------------------------------------------------------------------
        | FLUJO REAL DEL PRODUCTO
        |--------------------------------------------------------------------------
        */
        $flujo = $detalleVenta->producto
            ->estadosProduccion
            ->sortBy('pivot.orden')
            ->values();

        // Estado actual dentro del flujo
        $flujoActual = $flujo->firstWhere(
            'id',
            $estadoActual->id
        );

        // Estado destino dentro del flujo
        $flujoDestino = $flujo->firstWhere(
            'id',
            $estadoDestino->id
        );

        /*
        |--------------------------------------------------------------------------
        | VALIDACIONES
        |--------------------------------------------------------------------------
        */

        // El producto no usa ese estado
        if (!$flujoDestino) {
            return response()->json([
                'message' => 'El producto no utiliza ese estado'
            ], 422);
        }

        // El estado actual no existe en flujo
        if (!$flujoActual) {
            return response()->json([
                'message' => 'El estado actual no pertenece al flujo del producto'
            ], 422);
        }

        // No permitir regresar al mismo o superior
        if (
            $flujoDestino->pivot->orden >=
            $flujoActual->pivot->orden
        ) {
            return response()->json([
                'message' => 'Solo se puede regresar a estados anteriores'
            ], 422);
        }

        // No permitir regresar desde el primero
        if ($flujoActual->pivot->orden == 1) {
            return response()->json([
                'message' => 'No se puede regresar desde el primer estado'
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | CERRAR PROCESO ACTIVO
        |--------------------------------------------------------------------------
        */
        $procesoActivo = $detalleVenta->getProcesoActivo();

        if ($procesoActivo) {
            $procesoActivo->update([
                'fecha_fin' => now()
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | CERRAR ESTADO ACTUAL
        |--------------------------------------------------------------------------
        */
        $estadoActivo->update([
            'fecha_fin' => now(),
            'observacion' => $request->observacion,
            'users_id' => Auth::id(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | REGISTRAR EVENTO DE REGRESO
        |--------------------------------------------------------------------------
        */
        HistorialEstadoProduccion::create([
            'detalle_ventas_id' => $detalleVenta->id,
            'estado_produccions_id' => $estadoActual->id,
            'users_id' => Auth::id(),
            'fecha_inicio' => now(),
            'fecha_fin' => now(),
            'tipo_evento' => 'regreso_estado',
            'observacion' => $request->observacion,
        ]);

        /*
        |--------------------------------------------------------------------------
        | CREAR NUEVA ENTRADA AL ESTADO DESTINO
        |--------------------------------------------------------------------------
        */
        HistorialEstadoProduccion::create([
            'detalle_ventas_id' => $detalleVenta->id,
            'estado_produccions_id' => $estadoDestino->id,
            'fecha_inicio' => now(),
            'tipo_evento' => 'entrada_estado',
        ]);

        /*
        |--------------------------------------------------------------------------
        | RECALCULAR ESTADO DE VENTA
        |--------------------------------------------------------------------------
        */
        $detalleVenta->venta->recalcularEstadoProduccion();

        return response()->json([
            'message' => 'Estado regresado correctamente'
        ]);
    }

    public function camposFinalizacion(DetalleVenta $detalleVenta)
    {
        $estadoActivo = $detalleVenta->getEstadoActual();

        if (!$estadoActivo) {
            return response()->json([
                'message' => 'No hay estado activo'
            ], 422);
        }

        $campos = DetalleEstadoProduccion::where(
            'estado_produccions_id',
            $estadoActivo->estado_produccions_id
        )->get();

        return response()->json($campos);
    }

}
