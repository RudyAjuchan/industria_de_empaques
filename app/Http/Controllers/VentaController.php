<?php

namespace App\Http\Controllers;

use App\Exports\VentaExport;
use App\Exports\VentasContabilidadExport;
use App\Http\Requests\StoreVentaRequest;
use App\Mail\ConfirmarVentaMail;
use App\Models\Cliente;
use App\Models\DetalleVenta;
use App\Models\DetalleVentaImagen;
use App\Models\EstadoProduccion;
use App\Models\HistorialEstadoProduccion;
use App\Models\Pagina;
use App\Models\Pago;
use App\Models\ProcesoEstadoProduccion;
use App\Models\Producto;
use App\Models\Promocion;
use App\Models\Venta;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class VentaController extends Controller
{
    /**
     * Listar ventas
     */
    public function index(Request $request)
    {
        // Comenzamos la consulta con las relaciones necesarias
        $query = Venta::with(['cliente', 'vendedor', 'banco'])
            ->orderBy('id', 'desc');

        // Filtro por Rango de Fechas
        if ($request->filled(['fecha_inicio', 'fecha_fin'])) {
            $query->whereBetween('created_at', [
                $request->fecha_inicio . ' 00:00:00',
                $request->fecha_fin . ' 23:59:59'
            ]);
        }

        // Filtro por Estado
        $estado = $request->estado;

        if ($estado === 'Emitidas') {
            $query->where('estado', 'emitida');
        } elseif ($estado === 'Anuladas') {
            $query->where('estado', 'anulada');
        } elseif ($estado === 'En Produccion') {
            $query->where('estado_produccion', 'en_produccion');
        } elseif ($estado === 'Sin Iniciar') {
            $query->where('estado_produccion', 'sin_iniciar');
        } elseif ($estado === 'Finalizadas') {
            $query->where('estado_produccion', 'finalizada');
        }
        // Si $estado es 'todos', simplemente no filtramos por estado, los traemos todos

        return $query->get();
    }

    public function cotizaciones()
    {
        return Venta::with(['cliente', 'vendedor', 'banco'])
            ->where('estado', 'pendiente')
            ->orderBy('id', 'desc')
            ->get();
    }

    public function ventas_activas()
    {
        return Venta::with(['cliente', 'vendedor', 'banco'])
            ->where('estado_produccion', '<>', 'finalizada')
            ->where('estado', '<>', 'pendiente')
            ->where('estado', '<>', 'anulada')
            ->orderBy('id', 'desc')
            ->get();
    }

    public function getPaginas()
    {
        return Pagina::where('estado', 1)
            ->orderBy('nombre')
            ->get();
    }

    /**
     * Guardar nueva venta
     */
    public function store(StoreVentaRequest $request)
    {
        return DB::transaction(function () use ($request) {

            $data = $request->validated();
            $serie = 'VTA';

            $ultimoNumero = Venta::where('serie', $serie)
                ->lockForUpdate()
                ->max('numero');

            $numero = ($ultimoNumero ?? 0) + 1;

            // =========================
            // SUBTOTAL
            // =========================
            $subtotal = collect($data['detalle'])->sum(function ($item) {

                $producto = Producto::find($item['productos_id']);

                $precio = $producto->tipo_producto === 'simple'
                    ? $producto->precio_base
                    : $item['precio'];

                $total = $precio * $item['cantidad'];

                $total = $this->aplicarPromocionProducto($total, $item['productos_id']);

                return $total;
            });

            $costoLogo   = $data['costo_logo'] ?? 0;
            $costoEnvio  = $data['costo_envio'] ?? 0;
            $descuento   = $data['descuento'] ?? 0;
            $deposito    = $data['cantidad_deposito'] ?? 0;

            // =========================
            // PROMO CARRITO
            // =========================
            $promoData = $data['promociones'] ?? null;
            $promocionMonto = 0;

            if ($promoData) {
                if ($promoData['tipo'] === 'porcentaje') {
                    $promocionMonto = $subtotal * ($promoData['valor'] / 100);
                } else {
                    $promocionMonto = $promoData['valor'];
                }
            }

            $total = $subtotal + $costoLogo + $costoEnvio - $descuento - $promocionMonto;
            $pendiente = $total - $deposito;

            // =========================
            // CREAR VENTA
            // =========================
            $esNuevoCliente = !Venta::where('clientes_id', $data['clientes_id'])
                ->lockForUpdate()
                ->exists();
            $venta = Venta::create([
                'serie' => $serie,
                'numero' => $numero,
                'vendedor_id' => Auth::user()->id,
                'clientes_id' => $data['clientes_id'],
                'bancos_id' => $data['bancos_id'],
                'fecha_entrega' => $data['fecha_entrega'],
                'tipo_pago' => $data['tipo_pago'],

                'no_deposito' => $data['no_deposito'] ?? null,
                'cantidad_deposito' => $deposito,
                'pendiente_pagar' => $pendiente,

                'costo_logo' => $costoLogo,
                'subtotal' => $subtotal,
                'descuento' => $descuento,
                'promociones' => $promoData,
                'costo_envio' => $costoEnvio,
                'total' => $total,

                'es_cliente_nuevo' => $esNuevoCliente,

                'estado' => 'emitida',
            ]);

            // =========================
            // SI TIENE DEPOSITO DEBE GUARDARSE EN PAGOS
            // =========================
            if ($deposito > 0) {
                Pago::create([
                    'ventas_id' => $venta->id,
                    'monto' => $deposito,
                    'metodo_pago' => $data['tipo_pago'],
                    'referencia' => $data['no_deposito'] ?? null,
                    'users_id' => Auth::user()->id,
                    'bancos_id' => $data['bancos_id'],
                ]);
            }

            // =========================
            // DETALLES
            // =========================
            foreach ($data['detalle'] as $index => $item) {

                $producto = Producto::with(['estadosProduccion', 'paginas'])
                    ->find($item['productos_id']);

                $precio = $producto->tipo_producto === 'simple'
                    ? $producto->precio_base
                    : $item['precio'];

                $totalItem = $precio * $item['cantidad'];

                $promocionAplicada = $this->promocionProducto($item['productos_id']);
                $totalItem = $this->aplicarPromocion($totalItem, $promocionAplicada);

                $detalle = $venta->detalles()->create([
                    'productos_id' => $item['productos_id'],
                    ...$this->snapshotProducto($producto),

                    'tipo_agarradors_id' => $producto->tipo_producto === 'simple' ? null : $item['tipo_agarradors_id'],
                    'tipo_papels_id' => $producto->tipo_producto === 'simple' ? null : $item['tipo_papels_id'],

                    'color_agarrador' => $producto->tipo_producto === 'simple' ? null : ($item['color_agarrador'] ?? ''),
                    'detalle_impresion' => $producto->tipo_producto === 'simple' ? null : ($item['detalle_impresion'] ?? ''),
                    'observaciones' => $item['observaciones'] ?? null,
                    'nombre_logo' => $producto->tipo_producto === 'simple' ? null : ($item['nombre_logo'] ?? ''),

                    'logo_path' => $item['logo_path'] ?? null,
                    'promocion_aplicada' => $promocionAplicada,

                    'precio' => $precio,
                    'cantidad' => $item['cantidad'],
                    'total' => $totalItem,

                    'proceso_estado_produccions_id' => 1,
                ]);

                // =========================
                // IMÁGENES POR DETALLE
                // =========================
                if ($request->hasFile("detalle.$index.imagenes")) {

                    foreach ($request->file("detalle.$index.imagenes") as $file) {

                        $path = $file->store('ventas/detalles', 's3');

                        $detalle->imagenes()->create([
                            'path' => $path,
                            'estado' => 1
                        ]);
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | FLUJO DEL PRODUCTO
                |--------------------------------------------------------------------------
                */
                $flujo = $producto->estadosProduccion
                    ->sortBy('pivot.orden')
                    ->values();

                $estadoInicial = $flujo->first();

                if (!$estadoInicial) {

                    throw new \Exception(
                        "El producto {$producto->nombre} no tiene flujo configurado"
                    );
                }

                // =========================
                // HISTORIAL
                // =========================
                HistorialEstadoProduccion::create([
                    'detalle_ventas_id' => $detalle->id,
                    'estado_produccions_id' => $estadoInicial->id,
                    'proceso_estado_produccions_id' => null,
                    'users_id' => Auth::user()->id,
                    'fecha_inicio' => now(),
                    'tipo_evento' => 'entrada_estado',
                ]);
            }

            // =========================
            // RELACIONES
            // =========================
            $venta->load(
                'detalles.producto',
                'detalles.tipoAgarrador',
                'detalles.tipoPapel',
                'detalles.imagenes',
                'pagos'
            );

            // =========================
            // EMAIL
            // =========================
            $cliente = Cliente::find($data['clientes_id']);

            Mail::to($cliente->email)
                ->send(new ConfirmarVentaMail($cliente, $venta));

            return response()->json([
                'message' => 'Venta registrada correctamente',
                'venta'   => $venta->load('detalles.historialEstados'),
            ], 201);
        });
    }



    /**
     * Mostrar una venta
     */
    public function show(Venta $venta)
    {
        $venta->load([
            'cliente',
            'cliente.telefonos',
            'cliente.emails',
            'cliente.municipio.departamento',
            'banco',
            'pagos.banco',
            'detalles.producto.paginas',
            'detalles.tipoAgarrador',
            'detalles.tipoPapel',
            'vendedor',
            'detalles.imagenes'
        ]);
        $nombresPaginas = $venta->detalles
            ->map(function ($detalle) {
                return $detalle->producto_pagina
                    ?? $detalle->producto->paginas->nombre
                    ?? null;
            })
            ->filter()
            ->unique();

        $venta->nombres_paginas_productos = $nombresPaginas->implode(' / ');

        return response()->json($venta);
    }

    /**
     * Anular venta
     */
    public function destroy(Venta $venta)
    {
        $estado = $venta->estado === 'pendiente'
            ? 'rechazada'
            : 'anulada';

        $venta->update([
            'estado' => $estado
        ]);

        return response()->json([
            'message' => $estado === 'rechazada'
                ? 'Cotización rechazada correctamente'
                : 'Venta anulada correctamente'
        ]);
    }

    public function exportPdf(Request $request)
    {
        // Inicializamos la consulta
        $query = Venta::with(['cliente', 'vendedor', 'banco', 'pagos.banco'])
            ->orderBy('id', 'desc');

        // Filtro por Rango de Fechas
        if ($request->filled(['fecha_inicio', 'fecha_fin'])) {
            $query->whereBetween('created_at', [
                $request->fecha_inicio . ' 00:00:00',
                $request->fecha_fin . ' 23:59:59'
            ]);
        }

        // Filtro por Estado
        $estado = $request->estado;
        if ($estado === 'Emitidas') {
            $query->where('estado', 'emitida');
        } elseif ($estado === 'Anuladas') {
            $query->where('estado', 'anulada');
        } elseif ($estado === 'En Produccion') {
            $query->where('estado_produccion', 'en_produccion');
        } elseif ($estado === 'Sin Iniciar') {
            $query->where('estado_produccion', 'sin_iniciar');
        } elseif ($estado === 'Finalizadas') {
            $query->where('estado_produccion', 'finalizada');
        }

        // Búsqueda 
        $search = trim($request->query('search', ''));
        if (!empty($search) && $search !== 'null') {
            $query->where(function ($sub) use ($search) {
                if (str_contains($search, '-')) {
                    [$serie, $numero] = explode('-', $search);
                    $numero = ltrim($numero, '0');
                    $sub->where('serie', 'like', "%{$serie}%")
                        ->where('numero', $numero);
                } else {
                    $sub->where('serie', 'like', "%{$search}%")
                        ->orWhere('numero', 'like', "%{$search}%");
                }

                $sub->orWhereHas('cliente', function ($c) use ($search) {
                    $c->where('nombre', 'like', "%{$search}%");
                });
                $sub->orWhereHas('vendedor', function ($v) use ($search) {
                    $v->where('name', 'like', "%{$search}%");
                });
            });
        }

        $ventas = $query->get();

        return Pdf::loadView('pdf.venta.venta-lista', [
            'ventas' => $ventas,
            'search' => $search
        ])
            ->setPaper('letter', 'landscape')
            ->stream('venta-lista.pdf');
    }


    public function exportExcel(Request $request)
    {
        // Pasamos todo el request para tener acceso a fechas, estado y búsqueda
        return Excel::download(
            new VentaExport($request->all()),
            'ventas.xlsx'
        );
    }

    public function imprimir(Venta $venta)
    {
        $venta->load([
            'cliente',
            'cliente.telefonos',
            'cliente.emails',
            'cliente.municipio.departamento',
            'banco',
            'detalles.producto.paginas',
            'detalles.tipoAgarrador',
            'detalles.tipoPapel',
            'vendedor',
            'pagos'
        ]);
        $nombresPaginas = $venta->detalles
            ->map(function ($detalle) {
                return $detalle->producto_pagina
                    ?? $detalle->producto->paginas->nombre
                    ?? null;
            })
            ->filter()
            ->unique();

        $venta->nombres_paginas_productos = $nombresPaginas->implode(' / ');

        $pdf = Pdf::loadView('pdf.venta.venta', compact('venta'))
            ->setPaper('letter', 'portrait');

        return $pdf->stream("venta-{$venta->numero_completo}.pdf");
    }

    public function getVenta($id)
    {
        $venta = Venta::with([
            'cliente.municipio.departamento',
            'cliente.telefonos',
            'vendedor',
            'banco',
            'detalles.producto.paginas',
            'detalles.producto.tipoCatalogo',
            'detalles.tipoAgarrador',
            'detalles.tipoPapel',
            'detalles.imagenes',
        ])->findOrFail($id);

        // adaptar estructura para el frontend
        $venta->detalle = $venta->detalles->map(function ($d) {
            return [
                'id' => $d->id,
                'productos_id' => $d->productos_id,
                'tipo_agarradors_id' => $d->tipo_agarradors_id,
                'tipo_papels_id' => $d->tipo_papels_id,
                'color_agarrador' => $d->color_agarrador,
                'detalle_impresion' => $d->detalle_impresion,
                'observaciones' => $d->observaciones,
                'nombre_logo' => $d->nombre_logo,
                'logo_path' => $d->logo_path,
                'precio' => $d->precio,
                'cantidad' => $d->cantidad,
                'promocion_aplicada' => $d->promocion_aplicada,
                'total' => $d->total,

                // extras para mostrar
                'producto' => $d->producto,
                'tipo_agarrador' => $d->tipoAgarrador,
                'tipo_papel' => $d->tipoPapel,
                'imagenes' => $d->imagenes,
            ];
        });

        return $venta;
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'bancos_id' => 'required',
        ]);

        return DB::transaction(function () use ($request, $id) {

            $venta = Venta::with([
                'detalles.imagenes',
                'detalles.historialEstados.campos'
            ])->findOrFail($id);

            $data = $request->all();

            /*
            |--------------------------------------------------------------------------
            | IMÁGENES EXISTENTES
            |--------------------------------------------------------------------------
            */
            $imagenesExistentes = [];

            foreach ($data['detalle'] as $item) {

                if (!empty($item['imagenes'])) {

                    foreach ($item['imagenes'] as $img) {

                        if (is_array($img) && !empty($img['uploaded'])) {
                            $imagenesExistentes[] = $img['path'];
                        }
                    }
                }
            }

            /*
            |--------------------------------------------------------------------------
            | SUBTOTAL
            |--------------------------------------------------------------------------
            */
            $subtotal = collect($data['detalle'])->sum(function ($item) {
                $producto = Producto::find( $item['productos_id']);
                $precio = $producto->tipo_producto === 'simple' ? $producto->precio_base : $item['precio'];

                $total = $precio * $item['cantidad'];

                $total = $this->aplicarPromocionProducto($total, $item['productos_id']);
                return $total;
            });
            $costoLogo = $data['costo_logo'] ?? 0;
            $costoEnvio = $data['costo_envio'] ?? 0;
            $descuento = $data['descuento'] ?? 0;
            $deposito = $data['cantidad_deposito'] ?? 0;

            /*
            |--------------------------------------------------------------------------
            | PROMO CARRITO
            |--------------------------------------------------------------------------
            */
            $promoData = $data['promociones'] ?? null;
            $promocionMonto = 0;
            if ($promoData) {
                if ( $promoData['tipo']=== 'porcentaje' ) {
                    $promocionMonto = $subtotal * ($promoData['valor'] / 100);
                } else {
                    $promocionMonto = $promoData['valor'];
                }
            }

            $total = $subtotal + $costoLogo + $costoEnvio - $descuento - $promocionMonto;

            $pendiente = $total - $deposito;

            /*
            |--------------------------------------------------------------------------
            | UPDATE VENTA
            |--------------------------------------------------------------------------
            */
            $venta->update([

                'clientes_id' => $data['clientes_id'],
                'bancos_id' => $data['bancos_id'],
                'fecha_entrega' => $data['fecha_entrega'],
                'tipo_pago' => $data['tipo_pago'],
                'no_deposito' => $data['no_deposito'] ?? null,
                'cantidad_deposito' => $deposito,
                'pendiente_pagar' => $pendiente,
                'costo_logo' => $costoLogo,
                'subtotal' => $subtotal,
                'descuento' => $descuento,
                'promociones' => $promoData,
                'costo_envio' => $costoEnvio,
                'total' => $total,
                'estado' => 'emitida',
            ]);

            /*
            |--------------------------------------------------------------------------
            | PAGOS
            |--------------------------------------------------------------------------
            */
            $pago = $venta->pagos()->oldest()->first();
            if ($deposito > 0 && $pago) {
                $pago->update([
                    'monto' => $deposito,
                    'metodo_pago' => $data['tipo_pago'],
                    'referencia' => $data['no_deposito'] ?? null,
                    'bancos_id' => $data['bancos_id'],
                ]);
            } elseif ($deposito > 0) {
                $venta->pagos()->create([
                    'monto' => $deposito,
                    'metodo_pago' => $data['tipo_pago'],
                    'referencia' => $data['no_deposito'] ?? null,
                    'users_id' => Auth::id(),
                    'bancos_id' => $data['bancos_id'],
                ]);
            } elseif ($pago) {
                $pago->delete();
            }

            /*
            |--------------------------------------------------------------------------
            | ELIMINAR DETALLES ANTERIORES
            |--------------------------------------------------------------------------
            */
            foreach ($venta->detalles as $detalle) {
                /*
                |--------------------------------------------------------------------------
                | IMÁGENES
                |--------------------------------------------------------------------------
                */
                foreach ($detalle->imagenes as $img) {
                    if ( !in_array( $img->path, $imagenesExistentes ) ) {
                        Storage::disk('s3')->delete( $img->path);
                    }
                    $img->delete();
                }
                /*
                |--------------------------------------------------------------------------
                | HISTORIAL
                |--------------------------------------------------------------------------
                */
                foreach ($detalle->historialEstados as $historial) {
                    $historial->campos()->delete();
                }
                $detalle->historialEstados()->delete();
                $detalle->delete();
            }

            /*
            |--------------------------------------------------------------------------
            | NUEVOS DETALLES
            |--------------------------------------------------------------------------
            */
            foreach ($data['detalle'] as $index => $item) {

                $producto = Producto::with(['estadosProduccion', 'paginas'])->find($item['productos_id']);
                $precio = $producto->tipo_producto === 'simple' ? $producto->precio_base : $item['precio'];
                $totalItem =
                    $precio * $item['cantidad'];
                $promocionAplicada = $this->promocionProducto($item['productos_id']);
                $totalItem = $this->aplicarPromocion($totalItem, $promocionAplicada);

                /*
                |--------------------------------------------------------------------------
                | DETALLE
                |--------------------------------------------------------------------------
                */
                $detalle =
                    $venta->detalles()->create([

                        'productos_id' => $item['productos_id'],
                        ...$this->snapshotProducto($producto),
                        'tipo_agarradors_id' => $producto->tipo_producto === 'simple' ? null : $item['tipo_agarradors_id'],
                        'tipo_papels_id' => $producto->tipo_producto === 'simple' ? null : $item['tipo_papels_id'],
                        'color_agarrador' => $producto->tipo_producto === 'simple' ? null : ( $item['color_agarrador'] ?? '' ),
                        'detalle_impresion' => $producto->tipo_producto === 'simple' ? null : ( $item['detalle_impresion'] ?? '' ),
                        'observaciones' => $item['observaciones'] ?? null,
                        'nombre_logo' => $producto->tipo_producto === 'simple' ? null : ( $item['nombre_logo'] ?? ''),
                        'archivo_diseno_path' => $item['archivo_diseno_path'] ?? null,
                        'promocion_aplicada' => $promocionAplicada,
                        'precio' => $precio,
                        'cantidad' => $item['cantidad'],
                        'total' => $totalItem,
                        'proceso_estado_produccions_id' => 1,
                    ]);

                /*
                |--------------------------------------------------------------------------
                | IMÁGENES
                |--------------------------------------------------------------------------
                */
                if (!empty($item['imagenes'])) {

                    foreach ($item['imagenes'] as $img) {

                        /*
                        |--------------------------------------------------------------------------
                        | EXISTENTE
                        |--------------------------------------------------------------------------
                        */
                        if (is_array($img) && !empty($img['uploaded'])) {

                            $detalle->imagenes()->create([
                                'path' => $img['path'],
                                'estado' => 1
                            ]);

                            continue;
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | NUEVA
                        |--------------------------------------------------------------------------
                        */
                        if ($img instanceof \Illuminate\Http\UploadedFile) {

                            $path = Storage::disk('s3')
                                ->putFile('ventas/detalles', $img);

                            $detalle->imagenes()->create([
                                'path' => $path,
                                'estado' => 1
                            ]);
                        }
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | FLUJO
                |--------------------------------------------------------------------------
                */
                $flujo = $producto->estadosProduccion->sortBy('pivot.orden')->values();
                $estadoInicial = $flujo->first();
                if (!$estadoInicial) {
                    throw new \Exception("El producto {$producto->nombre} no tiene flujo configurado");
                }
                /*
                |--------------------------------------------------------------------------
                | HISTORIAL
                |--------------------------------------------------------------------------
                */
                HistorialEstadoProduccion::create([
                    'detalle_ventas_id' => $detalle->id,
                    'estado_produccions_id' => $estadoInicial->id,
                    'proceso_estado_produccions_id' => null,
                    'users_id' => Auth::id(),
                    'fecha_inicio' => now(),
                    'tipo_evento' => 'entrada_estado',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | RELACIONES
            |--------------------------------------------------------------------------
            */
            $venta->load(
                'detalles.producto',
                'detalles.tipoAgarrador',
                'detalles.tipoPapel',
                'detalles.imagenes',
                'pagos'
            );

            $cliente = Cliente::find($data['clientes_id']);

            Mail::to($cliente->email)->send(new ConfirmarVentaMail($cliente, $venta));

            return response()->json([
                'message' =>
                'Venta actualizada correctamente',
                'venta' => $venta
            ]);
        });
    }

    private function promocionProducto(int $productoId): ?array
    {
        $promocion = Promocion::vigente()
            ->where('aplica_a', 'producto')
            ->whereHas('productos', function ($query) use ($productoId) {
                $query->where('productos.id', $productoId);
            })
            ->orderByDesc('id')
            ->first();

        if (!$promocion) {
            return null;
        }

        return [
            'id' => $promocion->id,
            'nombre' => $promocion->nombre,
            'tipo' => $promocion->tipo,
            'valor' => $promocion->valor,
        ];
    }

    private function aplicarPromocionProducto(float $total, int $productoId): float
    {
        return $this->aplicarPromocion($total, $this->promocionProducto($productoId));
    }

    private function aplicarPromocion(float $total, ?array $promocion): float
    {
        if (!$promocion) {
            return $total;
        }

        if ($promocion['tipo'] === 'porcentaje') {
            $total -= $total * ($promocion['valor'] / 100);
        } else {
            $total -= $promocion['valor'];
        }

        return max(0, $total);
    }

    private function snapshotProducto(Producto $producto): array
    {
        return [
            'producto_sku' => $producto->sku,
            'producto_nombre' => $producto->nombre,
            'producto_tipo' => $producto->tipo,
            'producto_pagina' => $producto->paginas?->nombre,
            'producto_tipo_producto' => $producto->tipo_producto,
            'producto_alto' => $producto->alto,
            'producto_ancho' => $producto->ancho,
            'producto_fuelle' => $producto->fuelle,
            'producto_descripcion' => $producto->descripcion,
        ];
    }

    public function exportContabilidad(Request $request)
    {
        return Excel::download(
            new VentasContabilidadExport(
                $request->fecha_inicio,
                $request->fecha_fin
            ),
            'ventas_contabilidad.xlsx'
        );
    }

    public function contabilidad(Request $request)
    {
        $query = DetalleVenta::with([
            'venta.cliente',
            'producto'
        ])
            ->whereHas('venta', function ($q) use ($request) {

                $q->where('estado', 'emitida');

                if ($request->fecha_inicio) {
                    $q->whereDate('created_at', '>=', $request->fecha_inicio);
                }

                if ($request->fecha_fin) {
                    $q->whereDate('created_at', '<=', $request->fecha_fin);
                }
            });

        return $query->get()->map(function ($d) {
            return [
                'fecha' => $d->venta->created_at->format('Y-m-d'),
                'cliente' => $d->venta->cliente->nombre,
                'producto' => $d->producto_nombre ?? $d->producto->nombre,
                'cantidad' => $d->cantidad,
                'total' => $d->total,
                'estado' => $d->venta->estado,
            ];
        });
    }

    public function generatePresignedUrl(Request $request)
    {
        $request->validate([
            'filename' => 'required|string',
            'content_type' => 'required|string',
        ]);

        $path = 'disenos/' . uniqid() . '_' . $request->filename;

        $disk = Storage::disk('s3');

        // OBTENER CLIENTE CORRECTAMENTE
        $client = $disk->getClient();

        $command = $client->getCommand('PutObject', [
            'Bucket' => config('filesystems.disks.s3.bucket'),
            'Key' => $path,
            'ContentType' => $request->content_type,
        ]);

        $requestPresigned = $client->createPresignedRequest($command, '+10 minutes');

        return response()->json([
            'url' => (string) $requestPresigned->getUri(),
            'path' => $path,
        ]);
    }

    public function guardarDiseno(Request $request, $id)
    {
        $request->validate([
            'path' => 'required|string'
        ]);

        $detalle = DetalleVenta::findOrFail($id);

        $detalle->update([
            'archivo_diseno_path' => $request->path
        ]);

        return response()->json([
            'message' => 'Archivo asociado correctamente'
        ]);
    }

    public function subirImagenes(Request $request, DetalleVenta $detalle)
    {
        $request->validate([
            'imagenes.*' => 'required|image|max:10240'
        ]);

        foreach ($request->file('imagenes') as $file) {

            $path = $file->store(
                'ventas/detalles',
                's3'
            );

            $detalle->imagenes()->create([
                'path' => $path,
                'estado' => 1
            ]);
        }

        return response()->json([
            'message' => 'Imágenes subidas'
        ]);
    }

    public function eliminarImagen(DetalleVentaImagen $imagen)
    {
        if ($imagen->path) {

            Storage::disk('s3')
                ->delete($imagen->path);
        }

        $imagen->delete();

        return response()->json([
            'message' => 'Imagen eliminada'
        ]);
    }

    public function eliminarDiseno(DetalleVenta $detalle)
    {
        if ($detalle->archivo_diseno_path) {

            Storage::disk('s3')
                ->delete(
                    $detalle->archivo_diseno_path
                );
        }

        $detalle->update([
            'archivo_diseno_path' => null
        ]);

        return response()->json([
            'message' => 'Diseño eliminado'
        ]);
    }

    public function deleteLogo(Request $request)
    {
        $path = $request->path;

        // Cambiamos el disco a 's3' para que busque y elimine allá
        if ($path && Storage::disk('s3')->exists($path)) {
            Storage::disk('s3')->delete($path);
        }

        DetalleVentaImagen::where(
            'path',
            $path
        )->delete();

        return response()->json(['message' => 'Eliminado']);
    }
}
