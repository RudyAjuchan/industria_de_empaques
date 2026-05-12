<?php

namespace App\Http\Controllers;

use App\Models\EstadoProduccion;
use App\Models\HistorialEstadoProduccion;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\EstadisticasProduccionExport;
use App\Models\DetalleVenta;
use App\Models\Venta;

class EstadisticasProduccionController extends Controller
{
    public function estadisticasProduccion(Request $request)
    {
        return response()->json(
            $this->obtenerDatos($request)
        );
    }

    public function filtrosProduccion()
    {
        $fechas = HistorialEstadoProduccion::where('tipo_evento', 'finalizacion_estado')
            ->selectRaw('YEAR(fecha_inicio) as year, MONTH(fecha_inicio) as month')
            ->distinct()
            ->orderBy('year', 'desc')
            ->orderBy('month')
            ->get();

        $years = $fechas->pluck('year')->unique()->values();

        $mesesPorYear = $fechas->groupBy('year')->map(function ($items) {
            return $items->pluck('month')->unique()->values();
        });

        return response()->json([
            'years' => $years,
            'meses' => $mesesPorYear
        ]);
    }

    public function exportPDF(Request $request)
    {
        $data = $this->obtenerDatos($request);
        $data2 = $this->obtenerVentasPorPaginaData($request);
        $data3 = $this->obtenerTiposProductoData($request);

        $pdf = Pdf::loadView('pdf.estadisticas', [
            'totales' => $data['totales'],
            'porEstado' => $data['por_estado'],

            'porPagina' => $data2['ventas_por_pagina'],
            'totalesPorPagina' => $data2['totales'],

            'porTipo' => $data3['tipos_producto'],
            'totalesPorTipo' => $data3['totales'], // 🔥 corregido nombre

            'filtros' => $request->all()
        ]);

        return $pdf->stream('reporte-produccion.pdf');
    }

    private function obtenerDatos($request)
    {
        $estados = EstadoProduccion::orderBy('orden')->get();

        /*
        |--------------------------------------------------------------------------
        | 1. FILTRAR DETALLES POR FECHA DE VENTA
        |--------------------------------------------------------------------------
        */
        $ventasQuery = DetalleVenta::with([
            'producto.estadosProduccion'
        ]);

        if ($request->periodo === 'hoy') {
            $ventasQuery->whereDate('created_at', now());
        }

        if ($request->periodo === 'dia') {
            $ventasQuery->whereDate('created_at', $request->fecha);
        }

        if ($request->periodo === 'mes') {
            $ventasQuery->whereYear('created_at', $request->year)
                ->whereMonth('created_at', $request->month);
        }

        if ($request->periodo === 'anio') {
            $ventasQuery->whereYear('created_at', $request->year);
        }

        $ventas = $ventasQuery->get();

        $ventasIds = $ventas->pluck('id');

        /*
        |--------------------------------------------------------------------------
        | 2. HISTORIAL DE FINALIZACIONES
        |--------------------------------------------------------------------------
        */
        $registros = HistorialEstadoProduccion::with([
            'estadoProduccion',
            'detalleVenta.producto.estadosProduccion',
            'campos.campo'
        ])
            ->where('tipo_evento', 'finalizacion_estado')
            ->whereIn('detalle_ventas_id', $ventasIds)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | 3. TRANSFORMAR HISTORIAL
        |--------------------------------------------------------------------------
        */
        $data = $registros->map(function ($item) {

            $finalizadas = 0;
            $desechadas = 0;
            $extras = 0;

            foreach ($item->campos as $campo) {

                $nombre = strtolower($campo->campo->nombre);

                if ($nombre === 'finalizadas') {
                    $finalizadas = $campo->valor_integer ?? 0;
                }

                if ($nombre === 'desechadas') {
                    $desechadas = $campo->valor_integer ?? 0;
                }

                if ($nombre === 'extras') {
                    $extras = $campo->valor_integer ?? 0;
                }
            }

            return [
                'detalle_ventas_id' => $item->detalle_ventas_id,

                'estado_id' => $item->estadoProduccion->id,
                'estado' => $item->estadoProduccion->nombre,

                'finalizadas' => $finalizadas,
                'desechadas' => $desechadas,
                'extras' => $extras,
            ];
        });

        /*
        |--------------------------------------------------------------------------
        | 4. AGRUPAR POR ESTADO
        |--------------------------------------------------------------------------
        */
        $porEstado = $estados->map(function ($estado) use ($data, $ventas) {

            /*
            |--------------------------------------------------------------------------
            | SOLO PRODUCTOS QUE USAN ESTE ESTADO
            |--------------------------------------------------------------------------
            */
            $ventasEstado = $ventas->filter(function ($detalle) use ($estado) {

                return $detalle->producto
                    ->estadosProduccion
                    ->contains('id', $estado->id);
            });

            /*
            |--------------------------------------------------------------------------
            | PEDIDO REAL DEL ESTADO
            |--------------------------------------------------------------------------
            */
            $pedido = $ventasEstado->sum('cantidad');

            /*
            |--------------------------------------------------------------------------
            | HISTORIAL DEL ESTADO
            |--------------------------------------------------------------------------
            */
            $items = $data->where(
                'estado_id',
                $estado->id
            );

            $finalizadas = $items->sum('finalizadas');

            $desechadas = $items->sum('desechadas');

            $extras = $items->sum('extras');

            return [
                'estado' => $estado->nombre,
                'estado_id' => $estado->id,

                'pedido' => $pedido,

                'finalizadas' => $finalizadas,

                'desechadas' => $desechadas,

                'extras' => $extras,

                'pendiente' => max(
                    $pedido - $finalizadas,
                    0
                ),
            ];
        });

        /*
        |--------------------------------------------------------------------------
        | 5. TOTALES GENERALES
        |--------------------------------------------------------------------------
        */

        $pedidoTotal = $ventas->sum('cantidad');

        /*
        |--------------------------------------------------------------------------
        | SOLO FINALIZACIONES DE ÚLTIMO ESTADO DEL PRODUCTO
        |--------------------------------------------------------------------------
        */
        $finales = $registros->filter(function ($item) {

            $flujo = $item->detalleVenta
                ->producto
                ->estadosProduccion
                ->sortBy('pivot.orden')
                ->values();

            $ultimoEstado = $flujo->last();

            return $ultimoEstado &&
                $ultimoEstado->id ==
                $item->estado_produccions_id;
        });

        $produccionTotal = 0;
        $desechadasTotal = 0;
        $extrasTotal = 0;

        foreach ($finales as $item) {

            foreach ($item->campos as $campo) {

                $nombre = strtolower($campo->campo->nombre);

                if ($nombre === 'finalizadas') {
                    $produccionTotal +=
                        $campo->valor_integer ?? 0;
                }

                if ($nombre === 'desechadas') {
                    $desechadasTotal +=
                        $campo->valor_integer ?? 0;
                }

                if ($nombre === 'extras') {
                    $extrasTotal +=
                        $campo->valor_integer ?? 0;
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 6. RESPUESTA FINAL
        |--------------------------------------------------------------------------
        */
        return [
            'totales' => [
                'pedido' => $pedidoTotal,

                'finalizadas' => $produccionTotal,

                'desechadas' => $desechadasTotal,

                'extras' => $extrasTotal,

                'pendiente' => max(
                    $pedidoTotal - $produccionTotal,
                    0
                ),
            ],

            'por_estado' => $porEstado
        ];
    }

    public function exportExcel(Request $request)
    {
        $data = $this->obtenerDatos($request);
        $data2 = $this->obtenerVentasPorPaginaData($request);
        $data3 = $this->obtenerTiposProductoData($request);

        return Excel::download(
            new EstadisticasProduccionExport($data, $data2, $data3),
            'reporte-produccion.xlsx'
        );
    }


    public function ventasPorPagina(Request $request)
    {
        return response()->json(
            $this->obtenerVentasPorPaginaData($request)
        );
    }

    public function estadisticasPorTipo(Request $request)
    {
        return response()->json(
            $this->obtenerTiposProductoData($request)
        );
    }

    private function obtenerVentasPorPaginaData($request)
    {
        $query = Venta::with([
            'detalles.producto.paginas'
        ]);

        /*
        |--------------------------------------------------------------------------
        | FILTROS
        |--------------------------------------------------------------------------
        */
        if ($request->periodo === 'hoy') {
            $query->whereDate('created_at', now());
        }

        if ($request->periodo === 'dia') {
            $query->whereDate('created_at', $request->fecha);
        }

        if ($request->periodo === 'mes') {
            $query->whereYear('created_at', $request->year)
                ->whereMonth('created_at', $request->month);
        }

        if ($request->periodo === 'anio') {
            $query->whereYear('created_at', $request->year);
        }

        $ventas = $query->get();

        /*
        |--------------------------------------------------------------------------
        | AGRUPAR POR PÁGINA (CORRECTO)
        |--------------------------------------------------------------------------
        */
        $agrupado = [];

        foreach ($ventas as $venta) {
            foreach ($venta->detalles as $detalle) {

                $pagina = $detalle->producto->paginas->nombre ?? 'Sin página';

                if (!isset($agrupado[$pagina])) {
                    $agrupado[$pagina] = [
                        'venta' => 0,
                        'envio' => 0,
                    ];
                }

                // Venta sin envío
                $agrupado[$pagina]['venta'] +=
                    ($detalle->cantidad ?? 0) * ($detalle->precio ?? 0);

                // Envío (solo una vez por venta, no por detalle)
                $agrupado[$pagina]['envio'] += $venta->costo_envio ?? 0;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | FORMATEAR RESULTADO
        |--------------------------------------------------------------------------
        */
        $resultado = [];
        $contador = 1;

        foreach ($agrupado as $pagina => $valores) {

            $total = $valores['venta'] + $valores['envio'];

            $resultado[] = [
                'no' => $contador++,
                'nombre' => $pagina,
                'venta' => round($valores['venta'], 2),
                'envio' => round($valores['envio'], 2),
                'total' => round($total, 2),
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | ORDENAR
        |--------------------------------------------------------------------------
        */
        $resultado = collect($resultado)
            ->sortByDesc('total')
            ->values();

        /*
        |--------------------------------------------------------------------------
        | TOTALES
        |--------------------------------------------------------------------------
        */
        $totales = [
            'venta' => collect($resultado)->sum('venta'),
            'envio' => collect($resultado)->sum('envio'),
            'total' => collect($resultado)->sum('total'),
        ];

        return [
            'ventas_por_pagina' => $resultado,
            'totales' => $totales
        ];
    }

    private function obtenerTiposProductoData($request)
    {
        $query = DetalleVenta::with('producto');

        /*
        |--------------------------------------------------------------------------
        | FILTROS (igual que todo tu sistema)
        |--------------------------------------------------------------------------
        */
        if ($request->periodo === 'hoy') {
            $query->whereDate('created_at', now());
        }

        if ($request->periodo === 'dia') {
            $query->whereDate('created_at', $request->fecha);
        }

        if ($request->periodo === 'mes') {
            $query->whereYear('created_at', $request->year)
                ->whereMonth('created_at', $request->month);
        }

        if ($request->periodo === 'anio') {
            $query->whereYear('created_at', $request->year);
        }

        $detalles = $query->get();

        /*
        |--------------------------------------------------------------------------
        | AGRUPAR POR TIPO
        |--------------------------------------------------------------------------
        */
        $agrupado = $detalles->groupBy(function ($item) {
            return $item->producto->tipo ?? 'Sin tipo';
        });

        $resultado = [];
        $contador = 1;

        foreach ($agrupado as $tipo => $items) {

            $unidades = $items->sum('cantidad');
            $ventas = $items->count();

            $resultado[] = [
                'no' => $contador++,
                'tipo' => $tipo,
                'unidades' => $unidades,
                'ventas' => $ventas
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | ORDENAR
        |--------------------------------------------------------------------------
        */
        $resultado = collect($resultado)
            ->sortByDesc('unidades')
            ->values();
        return [
            'tipos_producto' => $resultado,
            'totales' => [
                'unidades' => collect($resultado)->sum('unidades'),
                'ventas' => collect($resultado)->sum('ventas'),
            ]
        ];
    }
}
