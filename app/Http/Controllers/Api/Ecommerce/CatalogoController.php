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
            ->where('ecommerce', 1)
            ->orderByDesc('detalles_count')
            ->take(8)
            ->get();

        // fallback si no hay ventas
        if ($masVendidos->sum('detalles_count') === 0) {
            $masVendidos = Producto::with('imagenes')
                ->where('estado', 1)
                ->where('ecommerce', 1)
                ->latest()
                ->take(8)
                ->get();
        }

        // APLICAR PROMOCIONES
        $masVendidos = $masVendidos->map(fn($p) => $this->aplicarPromocion($p));

        // RECIENTES
        $recientes = Producto::with('imagenes')
            ->where('estado', 1)
            ->where('ecommerce', 1)
            ->latest()
            ->take(8)
            ->get()
            ->map(fn($p) => $this->aplicarPromocion($p));

        // PROMOCIONES
        $productosPROMO = Producto::with([
            'imagenes',
            'promocionVigente'
        ])
            ->where('estado', 1)
            ->whereHas('promocionVigente')
            ->latest()
            ->take(12)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Transformar promociones
        |--------------------------------------------------------------------------
        */
        $productosPROMO->transform(function ($producto) {

            $promo = $producto->promocionVigente->first();

            $producto->tiene_promocion = false;
            $producto->promocion = null;
            $producto->precio_promocion = null;

            if ($promo) {

                $producto->tiene_promocion = true;

                $producto->promocion = [
                    'id' => $promo->id,
                    'nombre' => $promo->nombre,
                    'titulo' => $promo->titulo,
                    'descripcion' => $promo->descripcion,
                    'tipo' => $promo->tipo,
                    'valor' => $promo->valor,
                ];

                /*
                |--------------------------------------------------------------------------
                | Calcular precio promocional
                |--------------------------------------------------------------------------
                */
                if (
                    $producto->tipo_producto === 'simple' &&
                    $producto->precio_base
                ) {

                    if ($promo->tipo === 'porcentaje') {

                        $producto->precio_promocion =
                            round(
                                $producto->precio_base -
                                    (
                                        $producto->precio_base *
                                        ($promo->valor / 100)
                                    ),
                                2
                            );
                    } else {

                        $producto->precio_promocion =
                            max(
                                0,
                                round(
                                    $producto->precio_base - $promo->valor,
                                    2
                                )
                            );
                    }
                }
            }

            return $producto;
        });


        return response()->json([
            'destacados' => $masVendidos,
            'recientes' => $recientes,
            'paginas' => Pagina::where('estado', 1)->get(),
            'promociones_carrito' => $productosPROMO
        ]);
    }

    private function aplicarPromocion($producto)
    {
        $promo = Promocion::vigente()
            ->producto()
            ->whereHas('productos', function ($q) use ($producto) {
                $q->where('productos.id', $producto->id);
            })
            ->orderBy('fecha_fin')
            ->orderBy('id')
            ->first();

        if ($promo) {
            $producto->tiene_promocion = true;

            $producto->promocion = [
                'id' => $promo->id,
                'nombre' => $promo->nombre,
                'titulo' => $promo->titulo,
                'descripcion' => $promo->descripcion,
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
