<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVentaRequest;
use App\Models\DetalleVenta;
use App\Models\EstadoProduccion;
use App\Models\HistorialEstadoProduccion;
use App\Models\Pagina;
use App\Models\ProcesoEstadoProduccion;
use App\Models\Venta;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

            $subtotal = collect($data['detalle'])->sum(
                fn($item) =>
                $item['precio'] * $item['cantidad']
            );

            $costoLogo   = $data['costo_logo'] ?? 0;
            $costoEnvio  = $data['costo_envio'] ?? 0;
            $descuento   = $data['descuento'] ?? 0;
            $promociones = $data['promociones'] ?? 0;
            $deposito    = $data['cantidad_deposito'] ?? 0;

            $total = $subtotal + $costoLogo + $costoEnvio - $descuento - $promociones;
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
                'promociones' => $promociones,
                'costo_envio' => $costoEnvio,
                'total' => $total,

                // SOLO como resumen visual (opcional)
                'proceso_estado_produccions_id' => 1,
                'estado' => 'emitida',
            ]);

            // Obtener estado y proceso inicial UNA SOLA VEZ
            $estadoInicial = EstadoProduccion::orderBy('orden')->first();

            if (!$estadoInicial) {
                throw new \Exception('No existe un estado de producción inicial');
            }

            $procesoInicial = ProcesoEstadoProduccion::where(
                'estado_produccions_id',
                $estadoInicial->id
            )->orderBy('id')->first();

            // Crear detalles y el historial inicial
            foreach ($data['detalle'] as $item) {

                $detalle = $venta->detalles()->create([
                    'productos_id' => $item['productos_id'],
                    'tipo_agarradors_id' => $item['tipo_agarradors_id'],
                    'tipo_papels_id' => $item['tipo_papels_id'],
                    'color_agarrador' => $item['color_agarrador'] ?? '',
                    'detalle_impresion' => $item['detalle_impresion'] ?? '',
                    'nombre_logo' => $item['nombre_logo'] ?? '',
                    'precio' => $item['precio'],
                    'cantidad' => $item['cantidad'],
                    'total' => $item['precio'] * $item['cantidad'],
                    'proceso_estado_produccions_id' => 1,
                ]);

                // TRACKING
                HistorialEstadoProduccion::create([
                    'detalle_ventas_id' => $detalle->id,
                    'estado_produccions_id' => $estadoInicial->id,
                    'proceso_estado_produccions_id' => $procesoInicial?->id,
                    'users_id' => Auth::user()->id,
                    'fecha_inicio' => now(),
                ]);
            }

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

    public function exportPdf()
    {
        return Venta::all();
    }

    public function exportExcel()
    {
        return Venta::all();
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
}
