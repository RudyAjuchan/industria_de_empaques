<?php

namespace App\Http\Controllers;

use App\Models\EstadoProduccion;
use App\Models\HistorialEstadoProduccion;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\EstadisticasProduccionExport;

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

        $pdf = Pdf::loadView('pdf.estadisticas', [
            'totales' => $data['totales'],
            'porEstado' => $data['por_estado'],
            'filtros' => $request->all()
        ]);

        return $pdf->stream('reporte-produccion.pdf');
    }

    private function obtenerDatos($request)
    {
        $estados = EstadoProduccion::orderBy('orden')->get();

        $query = HistorialEstadoProduccion::with([
            'detalleVenta',
            'estadoProduccion',
            'campos.campo'
        ])->where('tipo_evento', 'finalizacion_estado');

        // FILTROS
        if ($request->periodo === 'hoy') {
            $query->whereDate('fecha_inicio', now());
        }

        if ($request->periodo === 'mes') {
            $query->whereYear('fecha_inicio', $request->year)
                ->whereMonth('fecha_inicio', $request->month);
        }

        if ($request->periodo === 'anio') {
            $query->whereYear('fecha_inicio', $request->year);
        }

        $registros = $query->get();

        // TRANSFORMACIÓN
        $data = $registros->map(function ($item) {

            $finalizadas = 0;
            $desechadas = 0;

            foreach ($item->campos as $campo) {
                $nombre = strtolower($campo->campo->nombre);

                if ($nombre === 'finalizadas') {
                    $finalizadas = $campo->valor_integer ?? 0;
                }

                if ($nombre === 'desechadas') {
                    $desechadas = $campo->valor_integer ?? 0;
                }
            }

            return [
                'estado' => $item->estadoProduccion->nombre,
                'estado_id' => $item->estadoProduccion->id,
                'pedido' => $item->detalleVenta->cantidad ?? 0,
                'finalizadas' => $finalizadas,
                'desechadas' => $desechadas,
            ];
        });

        // AGRUPACIÓN
        $porEstado = $estados->map(function ($estado) use ($data) {

            $items = $data->where('estado_id', $estado->id);

            $pedido = $items->sum('pedido');
            $finalizadas = $items->sum('finalizadas');
            $desechadas = $items->sum('desechadas');

            return [
                'estado' => $estado->nombre,
                'estado_id' => $estado->id,
                'pedido' => $pedido,
                'finalizadas' => $finalizadas,
                'desechadas' => $desechadas,
                'extras' => max($finalizadas - $pedido, 0),
                'pendiente' => max($pedido - $finalizadas, 0),
            ];
        });

        // TOTALES
        $ultimoEstado = EstadoProduccion::orderByDesc('orden')->first();

        $finales = $data->where('estado_id', $ultimoEstado->id);

        $totalPedido = $finales->sum('pedido');
        $totalFinalizadas = $finales->sum('finalizadas');
        $totalDesechadas = $finales->sum('desechadas');

        return [
            'totales' => [
                'pedido' => $totalPedido,
                'finalizadas' => $totalFinalizadas,
                'desechadas' => $totalDesechadas,
                'extras' => max($totalFinalizadas - $totalPedido, 0),
                'pendiente' => max($totalPedido - $totalFinalizadas, 0),
            ],
            'por_estado' => $porEstado
        ];
    }

    public function exportExcel(Request $request)
    {
        $data = $this->obtenerDatos($request);

        return Excel::download(
            new EstadisticasProduccionExport($data),
            'reporte-produccion.xlsx'
        );
    }
}
