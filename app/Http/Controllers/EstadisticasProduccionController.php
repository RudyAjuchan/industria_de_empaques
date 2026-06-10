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
use Illuminate\Support\Facades\Validator;

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
            'years' => $years->isNotEmpty()
                ? $years
                : collect([now()->year]),
            'meses' => $mesesPorYear
        ]);
    }

    public function exportPDF(Request $request)
    {
        $data = $this->obtenerDatos($request);
        $data2 = $this->obtenerVentasPorPaginaData($request);
        $data3 = $this->obtenerTiposProductoData($request);
        $data4 = $this->obtenerEstadisticasComercialesData($request);

        $pdf = Pdf::loadView('pdf.estadisticas', [
            'totales' => $data['totales'],
            'porEstado' => $data['por_estado'],

            'porPagina' => $data2['ventas_por_pagina'],
            'totalesPorPagina' => $data2['totales'],

            'porTipo' => $data3['tipos_producto'],
            'totalesPorTipo' => $data3['totales'],

            'comercial' => $data4,
            'charts' => $request->input('charts', []),

            'filtros' => $request->all()
        ]);

        return $pdf->stream('reporte-produccion.pdf');
    }

    private function obtenerDatos($request)
    {
        $filtros = $this->validarFiltros($request);
        $estados = EstadoProduccion::orderBy('orden')->get();

        /*
        |--------------------------------------------------------------------------
        | 1. FILTRAR DETALLES POR FECHA DE VENTA
        |--------------------------------------------------------------------------
        */
        $ventasQuery = DetalleVenta::with([
            'producto.estadosProduccion'
        ]);

        $this->aplicarFiltroVentaConfirmadaEnDetalle($ventasQuery, $filtros);

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
        $data4 = $this->obtenerEstadisticasComercialesData($request);

        return Excel::download(
            new EstadisticasProduccionExport($data, $data2, $data3, $data4),
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

    public function estadisticasComerciales(Request $request)
    {
        return response()->json(
            $this->obtenerEstadisticasComercialesData($request)
        );
    }

    private function obtenerVentasPorPaginaData($request)
    {
        $filtros = $this->validarFiltros($request);
        $query = Venta::with([
            'detalles.producto.paginas'
        ]);
        $query->where('estado', '<>', 'pendiente');

        /*
        |--------------------------------------------------------------------------
        | FILTROS
        |--------------------------------------------------------------------------
        */
        $this->aplicarFiltroFecha($query, $filtros);
        $this->aplicarFiltroTipoCliente($query, $filtros);

        $ventas = $query->get();

        /*
        |--------------------------------------------------------------------------
        | AGRUPAR POR PÁGINA (CORRECTO)
        |--------------------------------------------------------------------------
        */
        $agrupado = [];

        foreach ($ventas as $venta) {
            $paginasVenta = $venta->detalles
                ->map(fn($detalle) => $detalle->producto->paginas->nombre ?? 'Sin página')
                ->unique()
                ->values();

            $envioPorPagina = $paginasVenta->isNotEmpty()
                ? ($venta->costo_envio ?? 0) / $paginasVenta->count()
                : 0;

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

                // Envío distribuido una sola vez por venta entre sus páginas.
                if ($paginasVenta->contains($pagina)) {
                    $agrupado[$pagina]['envio'] += $envioPorPagina;
                    $paginasVenta = $paginasVenta->reject(fn($item) => $item === $pagina)->values();
                }
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
        $filtros = $this->validarFiltros($request);
        $query = DetalleVenta::with('producto.tipoCatalogo');

        /*
        |--------------------------------------------------------------------------
        | FILTROS (igual que todo tu sistema)
        |--------------------------------------------------------------------------
        */
        $this->aplicarFiltroVentaConfirmadaEnDetalle($query, $filtros, true);

        $detalles = $query->get();

        /*
        |--------------------------------------------------------------------------
        | AGRUPAR POR TIPO
        |--------------------------------------------------------------------------
        */
        $agrupado = $detalles->groupBy(function ($item) {
            return $item->producto_tipo
                ?? $item->producto?->tipoCatalogo?->nombre
                ?? $item->producto?->tipo
                ?? 'Sin tipo';
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

    private function validarFiltros(Request $request): array
    {
        $data = array_merge([
            'periodo' => 'mes',
            'year' => now()->year,
            'month' => now()->month,
            'fecha' => now()->toDateString(),
            'tipo_cliente' => 'todos',
        ], $request->only('periodo', 'year', 'month', 'fecha', 'tipo_cliente'));

        return Validator::make($data, [
            'periodo' => ['required', 'in:hoy,dia,mes,anio'],
            'fecha' => ['required_if:periodo,dia', 'date'],
            'year' => ['required_if:periodo,mes,anio', 'integer', 'min:2000', 'max:2100'],
            'month' => ['required_if:periodo,mes', 'integer', 'between:1,12'],
            'tipo_cliente' => ['nullable', 'in:todos,nuevo,existente'],
        ])->validate();
    }

    private function aplicarFiltroFecha($query, array $filtros): void
    {
        if ($filtros['periodo'] === 'hoy') {
            $query->whereDate('created_at', now()->toDateString());
        }

        if ($filtros['periodo'] === 'dia') {
            $query->whereDate('created_at', $filtros['fecha']);
        }

        if ($filtros['periodo'] === 'mes') {
            $query->whereYear('created_at', $filtros['year'])
                ->whereMonth('created_at', $filtros['month']);
        }

        if ($filtros['periodo'] === 'anio') {
            $query->whereYear('created_at', $filtros['year']);
        }
    }

    private function aplicarFiltroTipoCliente($query, array $filtros): void
    {
        if (($filtros['tipo_cliente'] ?? 'todos') === 'nuevo') {
            $query->where('es_cliente_nuevo', true);
        }

        if (($filtros['tipo_cliente'] ?? 'todos') === 'existente') {
            $query->where('es_cliente_nuevo', false);
        }
    }

    private function aplicarFiltroVentaConfirmadaEnDetalle($query, array $filtros, bool $filtrarTipoCliente = false): void
    {
        $query->whereHas('venta', function ($ventaQuery) use ($filtros, $filtrarTipoCliente) {
            $ventaQuery->where('estado', '<>', 'pendiente');
            $this->aplicarFiltroFecha($ventaQuery, $filtros);

            if ($filtrarTipoCliente) {
                $this->aplicarFiltroTipoCliente($ventaQuery, $filtros);
            }
        });
    }

    private function obtenerEstadisticasComercialesData(Request $request): array
    {
        $filtros = $this->validarFiltros($request);

        $ventasQuery = Venta::with([
            'cliente.municipio.departamento',
            'detalles.producto',
        ])->where('estado', '<>', 'pendiente');

        $this->aplicarFiltroFecha($ventasQuery, $filtros);
        $this->aplicarFiltroTipoCliente($ventasQuery, $filtros);

        $ventas = $ventasQuery->get();

        $tamanos = [];
        $generos = [];
        $tiposCliente = [];
        $departamentos = [];

        foreach ($ventas as $venta) {
            $totalVenta = (float) ($venta->total ?? 0);

            $genero = $this->normalizarEtiqueta($venta->cliente->genero ?? null, 'No especificado');
            $this->sumarAgrupado($generos, $genero, 'ventas', 1);
            $this->sumarAgrupado($generos, $genero, 'total', $totalVenta);

            $tipoCliente = $venta->es_cliente_nuevo ? 'Nuevo' : 'Existente';
            $this->sumarAgrupado($tiposCliente, $tipoCliente, 'ventas', 1);
            $this->sumarAgrupado($tiposCliente, $tipoCliente, 'total', $totalVenta);

            $departamento = $venta->cliente?->municipio?->departamento?->nombre ?: 'Internacional';
            $departamento = $this->normalizarEtiqueta($departamento, 'Internacional');
            $this->sumarAgrupado($departamentos, $departamento, 'ventas', 1);
            $this->sumarAgrupado($departamentos, $departamento, 'total', $totalVenta);

            foreach ($venta->detalles as $detalle) {
                $labelTamano = $this->labelTamano($detalle);

                if (!$labelTamano) {
                    continue;
                }

                $this->sumarAgrupado($tamanos, $labelTamano, 'unidades', (int) ($detalle->cantidad ?? 0));
                $this->sumarAgrupado($tamanos, $labelTamano, 'ventas', 1);
            }
        }

        return [
            'tamanos' => $this->ordenarAgrupado($tamanos, 'unidades', 'tamano'),
            'generos' => $this->ordenarAgrupado($generos, 'total', 'genero'),
            'tipos_cliente' => $this->ordenarAgrupado($tiposCliente, 'total', 'tipo'),
            'departamentos' => $this->ordenarAgrupado($departamentos, 'total', 'departamento'),
            'totales' => [
                'ventas' => $ventas->count(),
                'total' => round($ventas->sum('total'), 2),
                'unidades' => $ventas->sum(fn($venta) => $venta->detalles->sum('cantidad')),
            ],
        ];
    }

    private function labelTamano(DetalleVenta $detalle): ?string
    {
        $alto = $detalle->producto_alto ?? $detalle->producto?->alto;
        $ancho = $detalle->producto_ancho ?? $detalle->producto?->ancho;
        $fuelle = $detalle->producto_fuelle ?? $detalle->producto?->fuelle;

        $partes = collect([$alto, $ancho, $fuelle])
            ->filter(fn($valor) => $valor !== null && $valor !== '' && (float) $valor > 0)
            ->map(fn($valor) => rtrim(rtrim(number_format((float) $valor, 2, '.', ''), '0'), '.'))
            ->values();

        return $partes->isNotEmpty()
            ? $partes->join(' x ')
            : null;
    }

    private function normalizarEtiqueta(?string $valor, string $fallback): string
    {
        $valor = trim((string) $valor);

        if ($valor === '') {
            return $fallback;
        }

        return mb_convert_case($valor, MB_CASE_TITLE, 'UTF-8');
    }

    private function sumarAgrupado(array &$agrupado, string $label, string $campo, float|int $valor): void
    {
        if (!isset($agrupado[$label])) {
            $agrupado[$label] = [];
        }

        $agrupado[$label][$campo] = ($agrupado[$label][$campo] ?? 0) + $valor;
    }

    private function ordenarAgrupado(array $agrupado, string $orden, string $labelKey): array
    {
        return collect($agrupado)
            ->map(function ($valores, $label) use ($labelKey) {
                return array_merge([
                    $labelKey => $label,
                    'ventas' => 0,
                    'unidades' => 0,
                    'total' => 0,
                ], $valores);
            })
            ->sortByDesc($orden)
            ->values()
            ->map(function ($item, $index) {
                $item['no'] = $index + 1;
                $item['total'] = round((float) ($item['total'] ?? 0), 2);
                return $item;
            })
            ->all();
    }
}
