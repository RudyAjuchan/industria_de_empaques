<?php

namespace App\Http\Controllers\Produccion;

use App\Exports\TrackingVentaExport;
use App\Http\Controllers\Controller;
use App\Models\DetalleVenta;
use App\Models\EstadoProduccion;
use App\Models\Venta;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class HistorialProduccionController extends Controller
{
    public function trackingVenta(Venta $venta)
    {
        $estados = EstadoProduccion::orderBy('orden')->get();

        $detalles = $venta->detalles()->with([
            'producto',
            'historialEstados.estadoProduccion',
            'historialEstados.procesoEstado',
            'historialEstados.usuario',
            'historialEstados.campos.campo'
        ])->get();

        return response()->json([
            'estados' => $estados,
            'detalles' => $detalles,
            'venta' => $venta,
        ]);
    }


    public function trackingDetalle(DetalleVenta $detalleVenta)
    {
        return $detalleVenta->load([
            'producto',
            'historialEstados.estadoProduccion',
            'historialEstados.procesoEstado',
            'historialEstados.usuario',
            'historialEstados.campos.campo'
        ]);
    }

    public function exportTrackingPdf(Venta $venta)
    {
        $venta->load([
            'detalles.producto',
            'detalles.historialEstados.estadoProduccion',
            'detalles.historialEstados.procesoEstado',
            'detalles.historialEstados.usuario',
            'detalles.historialEstados.campos.campo'
        ]);

        return Pdf::loadView('pdf.venta.tracking.detalle-venta', [
            'venta' => $venta
        ])
            ->setPaper('letter', 'landscape')
            ->stream("tracking-{$venta->numero_completo}.pdf");
    }

    public function exportTrackingExcel(Venta $venta)
    {
        $venta->load([
            'detalles.producto',
            'detalles.historialEstados.estadoProduccion',
            'detalles.historialEstados.procesoEstado',
            'detalles.historialEstados.usuario',
        ]);

        //return $venta;

        return Excel::download(
            new TrackingVentaExport($venta),
            "tracking-{$venta->numero_completo}.xlsx"
        );
    }
}
