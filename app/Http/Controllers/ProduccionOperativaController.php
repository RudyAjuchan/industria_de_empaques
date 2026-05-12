<?php

namespace App\Http\Controllers;

use App\Mail\VentaFinalizadaMail;
use App\Models\DetalleEstadoProduccion;
use App\Models\DetalleVenta;
use App\Models\EstadoProduccion;
use App\Models\HistorialEstadoCampo;
use App\Models\HistorialEstadoProduccion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

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
                'detalleVenta.producto.estadosProduccion',

                'detalleVenta.venta',
                'detalleVenta.imagenes',

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
        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | CARGAR RELACIONES
            |--------------------------------------------------------------------------
            */
            $detalle = $detalleVenta->load([
                'venta.pagos',
                'producto.estadosProduccion'
            ]);

            /*
            |--------------------------------------------------------------------------
            | OBTENER ESTADO ACTIVO
            |--------------------------------------------------------------------------
            */
            $estadoActivo = $detalleVenta->getEstadoActual();

            if (!$estadoActivo) {
                return response()->json([
                    'message' => 'No hay estado activo'
                ], 422);
            }

            /*
            |--------------------------------------------------------------------------
            | CAMPOS DINÁMICOS
            |--------------------------------------------------------------------------
            */
            $camposDefinidos = DetalleEstadoProduccion::where(
                'estado_produccions_id',
                $estadoActivo->estado_produccions_id
            )->get();

            /*
            |--------------------------------------------------------------------------
            | REGLAS DINÁMICAS
            |--------------------------------------------------------------------------
            */
            $rules = [
                'observacion' => 'nullable|string',
                'campos' => 'nullable|array'
            ];

            foreach ($camposDefinidos as $campo) {

                $rule = match ($campo->tipo) {
                    'texto'   => 'string',
                    'entero'  => 'integer',
                    'decimal' => 'numeric',
                    'fecha'   => 'date',
                    default   => 'nullable'
                };

                $rules["campos.{$campo->id}"] = $campo->requerido
                    ? "required|$rule"
                    : "nullable|$rule";
            }

            $attributes = [];

            foreach ($camposDefinidos as $campo) {

                $attributes["campos.{$campo->id}"] =
                    $campo->label ?? $campo->nombre;
            }

            $request->validate($rules, [], $attributes);

            /*
            |--------------------------------------------------------------------------
            | ESTADO ACTUAL
            |--------------------------------------------------------------------------
            */
            $estadoActual = EstadoProduccion::find(
                $estadoActivo->estado_produccions_id
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

            if (!$flujoActual) {

                return response()->json([
                    'message' => 'El estado actual no pertenece al flujo del producto'
                ], 422);
            }

            /*
            |--------------------------------------------------------------------------
            | SIGUIENTE ESTADO
            |--------------------------------------------------------------------------
            */
            $siguienteEstado = $flujo->first(function ($estado) use ($flujoActual) {

                return $estado->pivot->orden >
                    $flujoActual->pivot->orden;
            });

            /*
            |--------------------------------------------------------------------------
            | VALIDAR PAGO SOLO EN ÚLTIMO ESTADO
            |--------------------------------------------------------------------------
            */
            if (!$siguienteEstado) {

                $venta = $detalle->venta;

                $pagado = $venta->pagos->sum('monto');

                $pendiente = $venta->total - $pagado;

                if ($pendiente > 0) {

                    return response()->json([
                        'message' => 'La venta tiene un saldo pendiente'
                    ], 422);
                }
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
            | EVENTO FINALIZACIÓN
            |--------------------------------------------------------------------------
            */
            $eventoFinalizacion = HistorialEstadoProduccion::create([
                'detalle_ventas_id' => $detalleVenta->id,
                'estado_produccions_id' => $estadoActivo->estado_produccions_id,
                'users_id' => Auth::id(),
                'fecha_inicio' => now(),
                'fecha_fin' => now(),
                'tipo_evento' => 'finalizacion_estado',
                'observacion' => $request->observacion,
            ]);

            /*
            |--------------------------------------------------------------------------
            | CREAR SIGUIENTE ESTADO
            |--------------------------------------------------------------------------
            */
            if ($siguienteEstado) {

                HistorialEstadoProduccion::create([
                    'detalle_ventas_id' => $detalleVenta->id,
                    'estado_produccions_id' => $siguienteEstado->id,
                    'fecha_inicio' => now(),
                    'tipo_evento' => 'entrada_estado',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | GUARDAR CAMPOS DINÁMICOS
            |--------------------------------------------------------------------------
            */
            if ($request->has('campos') && is_array($request->campos)) {

                foreach ($request->campos as $campoId => $valor) {

                    $campo = DetalleEstadoProduccion::find($campoId);

                    if (!$campo) {
                        continue;
                    }

                    HistorialEstadoCampo::create([
                        'historial_estado_produccions_id' => $eventoFinalizacion->id,
                        'detalle_estado_produccions_id' => $campoId,

                        'valor_string' =>
                        $campo->tipo === 'texto'
                            ? $valor
                            : null,

                        'valor_double' =>
                        $campo->tipo === 'decimal'
                            ? $valor
                            : null,

                        'valor_integer' =>
                        $campo->tipo === 'entero'
                            ? $valor
                            : null,

                        'valor_date' =>
                        $campo->tipo === 'fecha'
                            ? $valor
                            : null,
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | RECALCULAR ESTADO DE VENTA
            |--------------------------------------------------------------------------
            */
            $detalleVenta->venta->recalcularEstadoProduccion();

            $venta = $detalleVenta->venta->fresh();

            /*
            |--------------------------------------------------------------------------
            | ENVIAR CORREO SI FINALIZÓ
            |--------------------------------------------------------------------------
            */
            if ($venta->estado_produccion === 'finalizada') {

                $venta->load('cliente', 'pagos');

                Mail::to($venta->cliente->email)
                    ->send(
                        new VentaFinalizadaMail(
                            $venta->cliente,
                            $venta
                        )
                    );
            }

            DB::commit();

            return response()->json([
                'message' => $siguienteEstado
                    ? 'Producto enviado al siguiente estado'
                    : 'Producto finalizado completamente'
            ]);
        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'message' => 'Error al finalizar proceso',
                'error' => $e->getMessage()
            ], 500);
        }
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

    return response()->json([
        'estado_id' => $estadoActivo->estado_produccions_id,
        'campos' => $campos
    ]);
}
}
