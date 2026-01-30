<?php

namespace App\Http\Controllers\Produccion;

use App\Http\Controllers\Controller;
use App\Models\EstadoProduccion;
use App\Models\HistorialEstadoProduccion;
use Illuminate\Http\Request;

class ColaProduccionController extends Controller
{
    public function colaPorEstado(EstadoProduccion $estado)
    {
        return HistorialEstadoProduccion::whereNull('fecha_fin')
            ->where('estado_produccions_id', $estado->id)
            ->with([
                'detalleVenta.producto',
                'detalleVenta.venta',
                'usuario'
            ])
            ->get();
    }
}
