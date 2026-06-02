<?php

namespace App\Http\Controllers;

use App\Mail\VentaFinalizadaMail;
use App\Models\DetalleEstadoProduccion;
use App\Models\DetalleVenta;
use App\Models\EstadoProduccion;
use App\Models\HistorialEstadoCampo;
use App\Models\HistorialEstadoProduccion;
use App\Models\ProcesoEstadoProduccion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

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

        DB::beginTransaction();

        try {
            $detalleVenta = DetalleVenta::with('venta')
                ->whereKey($detalleVenta->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Puede ser null si ya estamos en proceso
            $estadoActivo = $detalleVenta->historialEstados()
                ->where('tipo_evento', 'entrada_estado')
                ->whereNull('fecha_fin')
                ->lockForUpdate()
                ->first();

            // Proceso activo (si existe)
            $procesoActivo = $detalleVenta->historialEstados()
                ->whereNotNull('proceso_estado_produccions_id')
                ->whereNull('fecha_fin')
                ->orderByDesc('fecha_inicio')
                ->lockForUpdate()
                ->first();

            // Caso inválido: no hay estado en cola NI proceso en curso
            if (!$estadoActivo && !$procesoActivo) {
                DB::rollBack();

                return response()->json([
                    'message' => 'El producto no tiene un estado válido para iniciar o cambiar proceso'
                ], 422);
            }

            // Determinar el estado real (desde donde se trabaja)
            $estadoProduccionId = $estadoActivo
                ? $estadoActivo->estado_produccions_id
                : $procesoActivo->estado_produccions_id;

            if (!$this->usuarioPuedeTrabajarEstado($estadoProduccionId)) {
                DB::rollBack();

                return response()->json([
                    'message' => 'No puedes registrar producción en esta área'
                ], 403);
            }

            if (!$this->procesoPerteneceAlEstado($request->proceso_estado_produccions_id, $estadoProduccionId)) {
                DB::rollBack();

                return response()->json([
                    'message' => 'El proceso seleccionado no pertenece al estado actual'
                ], 422);
            }

            if (
                $procesoActivo &&
                (int) $procesoActivo->proceso_estado_produccions_id ===
                (int) $request->proceso_estado_produccions_id
            ) {
                DB::rollBack();

                return response()->json([
                    'message' => 'El proceso seleccionado ya está activo'
                ]);
            }

            // Cerrar espera SOLO si existe
            if ($estadoActivo) {
                $estadoActivo->update([
                    'fecha_fin' => now()
                ]);
            }

            // Cerrar proceso activo SOLO si existe
            if ($procesoActivo) {
                $procesoActivo->update([
                    'fecha_fin' => now()
                ]);
            }

            // Crear nuevo proceso
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

            DB::commit();

            return response()->json([
                'message' => 'Proceso actualizado correctamente'
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();

            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al iniciar proceso',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function finalizarProceso(Request $request, DetalleVenta $detalleVenta)
    {
        $ventaParaNotificar = null;

        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | CARGAR RELACIONES
            |--------------------------------------------------------------------------
            */
            $detalle = DetalleVenta::with([
                'venta.pagos',
                'producto.estadosProduccion'
            ])
                ->whereKey($detalleVenta->id)
                ->lockForUpdate()
                ->firstOrFail();

            /*
            |--------------------------------------------------------------------------
            | OBTENER PROCESO ACTIVO
            |--------------------------------------------------------------------------
            */
            $procesoActivo = $detalle->historialEstados()
                ->whereNotNull('proceso_estado_produccions_id')
                ->whereNull('fecha_fin')
                ->orderByDesc('fecha_inicio')
                ->lockForUpdate()
                ->first();

            if (!$procesoActivo) {
                DB::rollBack();

                return response()->json([
                    'message' => 'Debe iniciar un proceso antes de finalizar'
                ], 422);
            }

            $estadoActivo = $procesoActivo;

            if (!$this->usuarioPuedeTrabajarEstado($estadoActivo->estado_produccions_id)) {
                DB::rollBack();

                return response()->json([
                    'message' => 'No puedes registrar producción en esta área'
                ], 403);
            }

            /*
            |--------------------------------------------------------------------------
            | CAMPOS DINÁMICOS
            |--------------------------------------------------------------------------
            */
            $camposDefinidos = DetalleEstadoProduccion::where(
                'estado_produccions_id',
                $estadoActivo->estado_produccions_id
            )->get()->keyBy('id');

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

            $camposRecibidos = $request->input('campos') ?? [];
            $camposInvalidos = array_diff(
                array_map('intval', array_keys($camposRecibidos)),
                $camposDefinidos->keys()->map(fn ($id) => (int) $id)->all()
            );

            if (!empty($camposInvalidos)) {
                DB::rollBack();

                return response()->json([
                    'message' => 'Uno o más campos no pertenecen al estado actual'
                ], 422);
            }

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
            $flujo = $detalle->producto
                ->estadosProduccion
                ->sortBy('pivot.orden')
                ->values();

            // Estado actual dentro del flujo
            $flujoActual = $flujo->firstWhere(
                'id',
                $estadoActual->id
            );

            if (!$flujoActual) {
                DB::rollBack();

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
                    DB::rollBack();

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
                'detalle_ventas_id' => $detalle->id,
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
                    'detalle_ventas_id' => $detalle->id,
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

                    $campo = $camposDefinidos->get((int) $campoId);

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
            $detalle->venta->recalcularEstadoProduccion();

            $venta = $detalle->venta->fresh();

            /*
            |--------------------------------------------------------------------------
            | ENVIAR CORREO SI FINALIZÓ
            |--------------------------------------------------------------------------
            */
            if ($venta->estado_produccion === 'finalizada') {
                $ventaParaNotificar = $venta->load('cliente', 'pagos');
            }

            DB::commit();

            if ($ventaParaNotificar) {
                try {
                    Mail::to($ventaParaNotificar->cliente->email)
                        ->send(
                            new VentaFinalizadaMail(
                                $ventaParaNotificar->cliente,
                                $ventaParaNotificar
                            )
                        );
                } catch (\Throwable $mailException) {
                    Log::warning('No se pudo enviar correo de venta finalizada', [
                        'venta_id' => $ventaParaNotificar->id,
                        'error' => $mailException->getMessage(),
                    ]);
                }
            }

            return response()->json([
                'message' => $siguienteEstado
                    ? 'Producto enviado al siguiente estado'
                    : 'Producto finalizado completamente'
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();

            throw $e;
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

        if (!$this->usuarioPuedeTrabajarEstado($estadoActivo->estado_produccions_id)) {
            return response()->json([
                'message' => 'No puedes consultar campos de esta área'
            ], 403);
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

    private function usuarioPuedeTrabajarEstado(int $estadoProduccionId): bool
    {
        return EstadoProduccion::whereKey($estadoProduccionId)
            ->where('users_id', Auth::id())
            ->exists();
    }

    private function procesoPerteneceAlEstado(int $procesoId, int $estadoProduccionId): bool
    {
        return ProcesoEstadoProduccion::whereKey($procesoId)
            ->where('estado_produccions_id', $estadoProduccionId)
            ->where('estado', 1)
            ->exists();
    }
}
