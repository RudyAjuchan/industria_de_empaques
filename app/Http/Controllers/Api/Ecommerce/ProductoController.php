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
        $query = Producto::with(['imagenes'])
            ->where('estado', 1)
            ->where('ecommerce', 1);

        if ($request->tipo) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->search) {
            $query->where(function ($sub) use ($request) {
                $sub->where('nombre', 'like', '%' . $request->search . '%')
                    ->orWhere('sku', 'like', '%' . $request->search . '%');
            });
        }

        $productos = $query->paginate(12);

        // APLICAR PROMOCIONES
        $productos->getCollection()->transform(function ($producto) {

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
        });

        return $productos;
    }

    public function show($id)
    {
        $producto = Producto::with(['imagenes'])->findOrFail($id);

        if ($producto->estado != 1 || $producto->ecommerce != 1) {
            return response()->json([
                'message' => 'Producto no disponible'
            ], 404);
        }

        // APLICAR PROMOCIÓN
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
            ->carrito()
            ->orderBy('fecha_fin')
            ->orderBy('id')
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
                ->producto()
                ->whereHas('productos', function ($q) use ($item) {
                    $q->where('productos.id', $item['productos_id']);
                })
                ->orderBy('fecha_fin')
                ->orderBy('id')
                ->first();

            return [
                'uuid' => $item['uuid'],
                'tiene_promocion' => $promo ? true : false,
                'promocion' => $promo ? [
                    'id' => $promo->id,
                    'nombre' => $promo->nombre,
                    'titulo' => $promo->titulo,
                    'descripcion' => $promo->descripcion,
                    'tipo' => $promo->tipo,
                    'valor' => $promo->valor
                ] : null
            ];
        });
    }

    public function promociones()
    {
        return Promocion::vigente()
            ->producto()
            ->with(['productos' => function ($q) {
                $q->where('estado', 1)
                    ->where('ecommerce', 1);
            }])
            ->orderBy('fecha_fin')
            ->orderBy('id')
            ->get()
            ->map(function ($promo) {
                return [
                    'id' => $promo->id,
                    'nombre' => $promo->nombre,
                    'titulo' => $promo->titulo,
                    'descripcion' => $promo->descripcion,
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

    public function buscarProductoEcommerce(Request $request)
    {
        $search = $request->search;
        $productos = Producto::query()
            ->where('ecommerce', 1)
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('id', 'LIKE', "%{$search}%")
                        ->orWhere('sku', 'LIKE', "%{$search}%")
                        ->orWhere('nombre', 'LIKE', "%{$search}%")
                        ->orWhere('tipo', 'LIKE', "%{$search}%");
                });
            })
            ->select(
                'id',
                'sku',
                'nombre',
                'tipo',
                'precio_base'
            )
            ->limit(8)
            ->get()
            ->map(function ($producto) {
                return [
                    'id' => $producto->id,
                    'sku' => $producto->sku,
                    'nombre' => $producto->nombre,
                    'tipo' => $producto->tipo,
                    'precio' => $producto->precio_base,
                    'imagen' => $producto->imagen_principal_url ?? null
                ];
            });

        return response()->json($productos);
    }
}
