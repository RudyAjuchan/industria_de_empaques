<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\Pagina;
use App\Models\Producto;
use Illuminate\Http\Request;

class CatalogoController extends Controller
{
    public function home()
    {
        // Productos más vendidos
        $masVendidos = Producto::with(['imagenes'])
            ->withCount('detalles')
            ->where('estado', 1)
            ->orderByDesc('detalles_count')
            ->take(8)
            ->get();

        // Si no hay ventas aún
        if ($masVendidos->sum('detalles_count') === 0) {
            $masVendidos = Producto::with('imagenes')
                ->where('estado', 1)
                ->latest()
                ->take(8)
                ->get();
        }

        return response()->json([
            'destacados' => $masVendidos,

            'recientes' => Producto::with('imagenes')
                ->where('estado', 1)
                ->latest()
                ->take(8)
                ->get(),

            'paginas' => Pagina::where('estado', 1)->get()
        ]);
    }
}
