<?php

namespace App\Http\Controllers\Produccion;

use App\Http\Controllers\Controller;
use App\Models\DetalleVenta;
use App\Models\EstadoProduccion;
use App\Models\Venta;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class HistorialProduccionController extends Controller
{
    public function trackingVenta(Venta $venta)
    {
        $estados = EstadoProduccion::orderBy('orden')->get();

        $detalles = $venta->detalles()->with([
            'producto',
            'historialEstados.estadoProduccion',
            'historialEstados.procesoEstado',
            'historialEstados.usuario'
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
            'historialEstados.usuario'
        ]);
    }

    public function exportTrackingPdf(Venta $venta)
    {
        $venta->load([
            'detalles.producto',
            'detalles.historialEstados.estadoProduccion',
            'detalles.historialEstados.procesoEstado',
            'detalles.historialEstados.usuario',
        ]);

        return Pdf::loadView('pdf.venta.tracking.detalle-venta', [
            'venta' => $venta
        ])
            ->setPaper('letter', 'landscape')
            ->stream("tracking-{$venta->numero_completo}.pdf");
    }
}
