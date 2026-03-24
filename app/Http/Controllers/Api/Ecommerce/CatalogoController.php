<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\Pagina;
use App\Models\Producto;
use App\Models\Promocion;
use Illuminate\Http\Request;

class CatalogoController extends Controller
{
    public function home()
    {
        // MÁS VENDIDOS
        $masVendidos = Producto::with(['imagenes'])
            ->withCount('detalles')
            ->where('estado', 1)
            ->orderByDesc('detalles_count')
            ->take(8)
            ->get();

        // fallback si no hay ventas
        if ($masVendidos->sum('detalles_count') === 0) {
            $masVendidos = Producto::with('imagenes')
                ->where('estado', 1)
                ->latest()
                ->take(8)
                ->get();
        }

        // APLICAR PROMOCIONES
        $masVendidos = $masVendidos->map(fn($p) => $this->aplicarPromocion($p));

        // RECIENTES
        $recientes = Producto::with('imagenes')
            ->where('estado', 1)
            ->latest()
            ->take(8)
            ->get()
            ->map(fn($p) => $this->aplicarPromocion($p));

        $promosCarrito = Promocion::vigente()
            ->where('aplica_a', 'carrito')
            ->get()
            ->map(function ($promo) {
                return [
                    'id' => $promo->id,
                    'nombre' => $promo->nombre,
                    'tipo' => $promo->tipo,
                    'valor' => $promo->valor
                ];
            });

        return response()->json([
            'destacados' => $masVendidos,
            'recientes' => $recientes,
            'paginas' => Pagina::where('estado', 1)->get(),
            'promociones_carrito' => $promosCarrito
        ]);
    }

    private function aplicarPromocion($producto)
    {
        $promo = Promocion::vigente()
            ->where('aplica_a', 'producto')
            ->whereHas('productos', function ($q) use ($producto) {
                $q->where('productos.id', $producto->id);
            })
            ->first();

        if ($promo) {
            $producto->tiene_promocion = true;

            $producto->promocion = [
                'tipo' => $promo->tipo,
                'valor' => $promo->valor
            ];
        } else {
            $producto->tiene_promocion = false;
            $producto->promocion = null;
        }

        return $producto;
    }
}
