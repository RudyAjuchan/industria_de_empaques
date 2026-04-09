<?php

namespace App\Http\Controllers;

use App\Exports\VentaExport;
use App\Exports\VentasContabilidadExport;
use App\Http\Requests\StoreVentaRequest;
use App\Mail\ConfirmarVentaMail;
use App\Models\Cliente;
use App\Models\DetalleVenta;
use App\Models\EstadoProduccion;
use App\Models\HistorialEstadoProduccion;
use App\Models\Pagina;
use App\Models\ProcesoEstadoProduccion;
use App\Models\Producto;
use App\Models\Venta;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class VentaController extends Controller
{
    /**
     * Listar ventas
     */
    public function index()
    {
        return Venta::with(['cliente', 'vendedor', 'banco'])
            ->where('estado', 'emitida')
            ->orderBy('id', 'desc')
            ->get();
    }

    public function cotizaciones()
    {
        return Venta::with(['cliente', 'vendedor', 'banco'])
            ->where('estado', 'pendiente')
            ->orderBy('id', 'desc')
            ->get();
    }

    public function ventas_activas(){
        return Venta::with(['cliente', 'vendedor', 'banco'])
            ->where('estado_produccion','<>', 'finalizada')
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

            // SUBTOTAL CORRECTO (según tipo de producto)
            $subtotal = collect($data['detalle'])->sum(function ($item) {

                $producto = Producto::find($item['productos_id']);

                $precio = $producto->tipo_producto === 'simple'
                    ? $producto->precio_base
                    : $item['precio'];

                $total = $precio * $item['cantidad'];

                if (!empty($item['promocion_aplicada'])) {
                    $promo = $item['promocion_aplicada'];

                    if ($promo['tipo'] === 'porcentaje') {
                        $total -= $total * ($promo['valor'] / 100);
                    } else {
                        $total -= $promo['valor'];
                    }
                }

                return $total;
            });

            $costoLogo   = $data['costo_logo'] ?? 0;
            $costoEnvio  = $data['costo_envio'] ?? 0;
            $descuento   = $data['descuento'] ?? 0;
            $deposito    = $data['cantidad_deposito'] ?? 0;

            // PROMOCIÓN CARRITO
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

                'estado' => 'emitida',
            ]);

            // ESTADO INICIAL
            $estadoInicial = EstadoProduccion::orderBy('orden')->first();

            if (!$estadoInicial) {
                throw new \Exception('No existe un estado de producción inicial');
            }

            foreach ($data['detalle'] as $item) {

                $producto = Producto::find($item['productos_id']);

                $precio = $producto->tipo_producto === 'simple'
                    ? $producto->precio_base
                    : $item['precio'];

                $totalItem = $precio * $item['cantidad'];

                if (!empty($item['promocion_aplicada'])) {
                    $promo = $item['promocion_aplicada'];

                    if ($promo['tipo'] === 'porcentaje') {
                        $totalItem -= $totalItem * ($promo['valor'] / 100);
                    } else {
                        $totalItem -= $promo['valor'];
                    }
                }

                $detalle = $venta->detalles()->create([
                    'productos_id' => $item['productos_id'],

                    'tipo_agarradors_id' => $producto->tipo_producto === 'simple' ? null : $item['tipo_agarradors_id'],
                    'tipo_papels_id' => $producto->tipo_producto === 'simple' ? null : $item['tipo_papels_id'],

                    'color_agarrador' => $producto->tipo_producto === 'simple' ? null : ($item['color_agarrador'] ?? ''),
                    'detalle_impresion' => $producto->tipo_producto === 'simple' ? null : ($item['detalle_impresion'] ?? ''),
                    'nombre_logo' => $producto->tipo_producto === 'simple' ? null : ($item['nombre_logo'] ?? ''),

                    'logo_path' => $item['logo_path'] ?? null,

                    'promocion_aplicada' => $item['promocion_aplicada'] ?? null,

                    'precio' => $precio,
                    'cantidad' => $item['cantidad'],
                    'total' => $totalItem,

                    'proceso_estado_produccions_id' => 1,
                ]);

                HistorialEstadoProduccion::create([
                    'detalle_ventas_id' => $detalle->id,
                    'estado_produccions_id' => $estadoInicial->id,
                    'proceso_estado_produccions_id' => null,
                    'users_id' => Auth::user()->id,
                    'fecha_inicio' => now(),
                    'tipo_evento' => 'entrada_estado',
                ]);
            }

            $venta->load('detalles.producto', 'detalles.tipoAgarrador', 'detalles.tipoPapel');

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
            'detalles.producto.paginas',
            'detalles.tipoAgarrador',
            'detalles.tipoPapel',
            'vendedor',
        ]);
        $nombresPaginas = $venta->detalles
            ->map(function ($detalle) {
                return $detalle->producto->paginas->nombre ?? null;
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
        $venta->update([
            'estado' => 'anulada'
        ]);

        return response()->json([
            'message' => 'Venta anulada correctamente'
        ]);
    }

    public function exportPdf(Request $request)
    {
        $search = trim($request->query('search', ''));
        $search = ($search === 'null' || $search === '') ? null : $search;

        $ventas = Venta::with(['cliente', 'vendedor'])
            ->when($search, function ($q) use ($search) {

                $q->where(function ($sub) use ($search) {

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
            })
            ->where('estado', 'emitida')
            ->orderByDesc('created_at')
            ->get();

        return Pdf::loadView('pdf.venta.venta-lista', [
            'ventas' => $ventas,
            'search' => $search
        ])
            ->setPaper('letter', 'landscape')
            ->stream('venta.venta-lista.pdf');
    }


    public function exportExcel(Request $request)
    {
        $search = $request->query('search');

        return Excel::download(
            new VentaExport($search),
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
        ]);
        $nombresPaginas = $venta->detalles
            ->map(function ($detalle) {
                return $detalle->producto->paginas->nombre ?? null;
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
            'detalles.producto',
            'detalles.tipoAgarrador',
            'detalles.tipoPapel'
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
            ];
        });

        return $venta;
    }

    public function update(Request $request, $id)
    {
        return DB::transaction(function () use ($request, $id) {

            $venta = Venta::with('detalles')->findOrFail($id);
            $data = $request->all();

            // SUBTOTAL
            $subtotal = collect($data['detalle'])->sum(function ($item) {

                $total = $item['precio'] * $item['cantidad'];

                if (!empty($item['promocion_aplicada'])) {
                    $promo = $item['promocion_aplicada'];

                    if ($promo['tipo'] === 'porcentaje') {
                        $total -= $total * ($promo['valor'] / 100);
                    } else {
                        $total -= $promo['valor'];
                    }
                }

                return $total;
            });

            $costoLogo   = $data['costo_logo'] ?? 0;
            $costoEnvio  = $data['costo_envio'] ?? 0;
            $descuento   = $data['descuento'] ?? 0;
            $deposito    = $data['cantidad_deposito'] ?? 0;

            // 🧠 PROMO CARRITO
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

            // ELIMINAR DETALLE
            $venta->detalles()->delete();

            $estadoInicial = EstadoProduccion::orderBy('orden')->first();

            if (!$estadoInicial) {
                throw new \Exception('No existe un estado de producción inicial');
            }

            foreach ($data['detalle'] as $item) {

                $totalItem = $item['precio'] * $item['cantidad'];

                if (!empty($item['promocion_aplicada'])) {
                    $promo = $item['promocion_aplicada'];

                    if ($promo['tipo'] === 'porcentaje') {
                        $totalItem -= $totalItem * ($promo['valor'] / 100);
                    } else {
                        $totalItem -= $promo['valor'];
                    }
                }

                $detalle = $venta->detalles()->create([
                    'productos_id' => $item['productos_id'],
                    'tipo_agarradors_id' => $item['tipo_agarradors_id'],
                    'tipo_papels_id' => $item['tipo_papels_id'],
                    'color_agarrador' => $item['color_agarrador'] ?? '',
                    'detalle_impresion' => $item['detalle_impresion'] ?? '',
                    'nombre_logo' => $item['nombre_logo'] ?? '',
                    'logo_path' => $item['logo_path'] ?? null,

                    'promocion_aplicada' => $item['promocion_aplicada'] ?? null,

                    'precio' => $item['precio'],
                    'cantidad' => $item['cantidad'],
                    'total' => $totalItem,
                    'proceso_estado_produccions_id' => 1,
                ]);

                HistorialEstadoProduccion::create([
                    'detalle_ventas_id' => $detalle->id,
                    'estado_produccions_id' => $estadoInicial->id,
                    'proceso_estado_produccions_id' => null,
                    'users_id' => Auth::user()->id,
                    'fecha_inicio' => now(),
                    'tipo_evento' => 'entrada_estado',
                ]);
            }

            $venta->load('detalles.producto', 'detalles.tipoAgarrador', 'detalles.tipoPapel');

            $cliente = Cliente::find($data['clientes_id']);

            Mail::to($cliente->email)
                ->send(new ConfirmarVentaMail($cliente, $venta));

            return response()->json([
                'message' => 'Venta actualizada correctamente'
            ]);
        });
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
                'producto' => $d->producto->nombre,
                'cantidad' => $d->cantidad,
                'total' => $d->total,
                'estado' => $d->venta->estado,
            ];
        });
    }
}
