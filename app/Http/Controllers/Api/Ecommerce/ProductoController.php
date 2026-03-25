<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Promocion;
use App\Models\TipoAgarrador;
use App\Models\TipoPapel;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $query = Producto::with(['imagenes', 'paginas'])
            ->where('estado', 1);

        if ($request->paginas_id) {
            $query->where('paginas_id', $request->paginas_id);
        }

        if ($request->tipo) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->search) {
            $query->where('nombre', 'like', '%' . $request->search . '%');
        }

        $productos = $query->paginate(12);

        // APLICAR PROMOCIONES
        $productos->getCollection()->transform(function ($producto) {

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
        });

        return $productos;
    }

    public function show($id)
    {
        $producto = Producto::with(['imagenes', 'paginas'])->findOrFail($id);

        // APLICAR PROMOCIÓN
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

        return response()->json([
            'producto' => $producto,
            'configuracion' => [
                'tipo_agarradores' => TipoAgarrador::where('estado', 1)->get(),
                'tipo_papeles' => TipoPapel::where('estado', 1)->get(),
            ]
        ]);
    }

    public function getPromos()
    {
        return Promocion::vigente()
            ->where('aplica_a', 'carrito')
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'nombre' => $p->nombre,
                'tipo' => $p->tipo,
                'valor' => $p->valor
            ]);
    }

    public function validarPromos(Request $request)
    {
        $items = $request->items;

        return collect($items)->map(function ($item) {

            $promo = Promocion::vigente()
                ->where('aplica_a', 'producto')
                ->whereHas('productos', function ($q) use ($item) {
                    $q->where('productos.id', $item['productos_id']);
                })
                ->first();

            return [
                'uuid' => $item['uuid'],
                'tiene_promocion' => $promo ? true : false,
                'promocion' => $promo ? [
                    'id' => $promo->id,
                    'nombre' => $promo->nombre,
                    'tipo' => $promo->tipo,
                    'valor' => $promo->valor
                ] : null
            ];
        });
    }

    public function promociones()
    {
        return Promocion::vigente()
            ->where('aplica_a', 'producto') // o quitar si quieres todas
            ->with('productos')
            ->get()
            ->map(function ($promo) {
                return [
                    'id' => $promo->id,
                    'nombre' => $promo->nombre,
                    'tipo' => $promo->tipo,
                    'valor' => $promo->valor,
                    'productos' => $promo->productos->map(function ($p) {
                        return [
                            'id' => $p->id,
                            'nombre' => $p->nombre,
                            'imagen' => $p->imagen_principal_url,
                            'tipo_producto' => $p->tipo_producto,
                            'precio_base' => $p->precio_base,
                        ];
                    })
                ];
            });
    }
}
