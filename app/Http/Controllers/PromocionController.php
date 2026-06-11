<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Promocion;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PromocionController extends Controller
{
    public function index()
    {
        return Promocion::with('productos')->where('activo', true)->latest()->get();
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $productos = $data['productos'] ?? [];
        unset($data['productos']);

        $this->validarConflictosPromocion($data, $productos);

        $promo = Promocion::create($data);

        if ($data['aplica_a'] === 'producto' && !empty($productos)) {
            $promo->productos()->sync($productos);
        }

        return response()->json($promo->load('productos'));
    }

    public function show($id)
    {
        return Promocion::with('productos')->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $promo = Promocion::findOrFail($id);

        $data = $this->validatedData($request);
        $productos = $data['productos'] ?? [];
        unset($data['productos']);

        $this->validarConflictosPromocion($data, $productos, $promo->id);

        $promo->update($data);

        if ($data['aplica_a'] === 'producto') {
            $promo->productos()->sync($productos);
        } else {
            $promo->productos()->detach();
        }

        return response()->json($promo->load('productos'));
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'nombre' => 'required|string|max:255',
            'titulo' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'tipo' => 'required|in:porcentaje,monto',
            'valor' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->tipo === 'porcentaje' && ($value < 1 || $value > 100)) {
                        $fail('El porcentaje debe estar entre 1 y 100.');
                    }

                    if ($request->tipo === 'monto' && $value < 0.01) {
                        $fail('El monto debe ser mayor a 0.');
                    }
                },
            ],
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'aplica_a' => 'required|in:producto,carrito',
            'productos' => 'exclude_unless:aplica_a,producto|required|array|min:1',
            'productos.*' => 'exists:productos,id',
        ]);
    }

    private function validarConflictosPromocion(array $data, array $productos, ?int $promocionId = null): void
    {
        $query = Promocion::query()
            ->where('activo', true)
            ->where('aplica_a', $data['aplica_a'])
            ->whereDate('fecha_inicio', '<=', $data['fecha_fin'])
            ->whereDate('fecha_fin', '>=', $data['fecha_inicio']);

        if ($promocionId) {
            $query->whereKeyNot($promocionId);
        }

        if ($data['aplica_a'] === 'carrito') {
            if ($query->exists()) {
                throw ValidationException::withMessages([
                    'fecha_inicio' => 'Ya existe una promoción de carrito activa en un rango de fechas que se cruza.',
                ]);
            }

            return;
        }

        $productos = collect($productos)->map(fn($id) => (int) $id)->unique()->values();

        if ($productos->isEmpty()) {
            return;
        }

        $promocionesConflicto = $query
            ->with(['productos:id,nombre'])
            ->whereHas('productos', function ($productoQuery) use ($productos) {
                $productoQuery->whereIn('productos.id', $productos);
            })
            ->get();

        if ($promocionesConflicto->isEmpty()) {
            return;
        }

        $productosConflicto = $promocionesConflicto
            ->flatMap->productos
            ->whereIn('id', $productos)
            ->pluck('nombre')
            ->unique()
            ->values()
            ->take(6)
            ->join(', ');

        throw ValidationException::withMessages([
            'productos' => "Estos productos ya tienen una promoción activa en un rango de fechas que se cruza: {$productosConflicto}.",
        ]);
    }

    public function destroy($id)
    {
        // Buscamos la promoción o lanzamos 404 si no existe
        $promo = Promocion::findOrFail($id);

        // Actualizamos el estado
        $promo->update([
            'activo' => false,
        ]);

        return response()->noContent(); // Retorna 204
    }

    /**
     * HERO / SLIDER
     */
    public function getPromoEco()
    {
        $promociones = Promocion::query()
            ->vigente()
            ->withCount('productos')
            ->orderBy('fecha_fin')
            ->get()
            ->map(function ($promo) {

                return [
                    'id' => $promo->id,

                    // CARD
                    'nombre' => $promo->nombre,

                    // HERO
                    'titulo' => $promo->titulo,
                    'descripcion' => $promo->descripcion,

                    // DESCUENTO
                    'tipo' => $promo->tipo,
                    'valor' => $promo->valor,

                    // COUNTDOWN
                    'fecha_inicio' => $promo->fecha_inicio,
                    'fecha_fin' => $promo->fecha_fin,

                    // PRODUCTOS
                    'productos_count' => $promo->productos_count,
                ];
            });

        return response()->json($promociones);
    }

    /**
     * PRODUCTOS DE PROMOCIÓN
     */
    public function getProdPromoEco(Request $request, $id)
    {
        $promocion = Promocion::query()
            ->vigente()
            ->findOrFail($id);

        $productos = $promocion->productos()
            ->where('estado', 1)
            ->where('ecommerce', 1)
            ->with([
                'imagenPrincipal',
            ])
            ->paginate(12);

        $productos->getCollection()->transform(function ($producto) use ($promocion) {

            // PRECIO ORIGINAL
            $precioOriginal = $producto->precio_base;

            // PRECIO FINAL
            $precioFinal = $precioOriginal;
            if ($producto->tipo_producto === 'simple' && $precioOriginal) {
                if ($promocion->tipo === 'porcentaje') {
                    $precioFinal = $precioOriginal -
                        ($precioOriginal * ($promocion->valor / 100));
                } else {
                    $precioFinal = $precioOriginal - $promocion->valor;
                }
                // EVITAR NEGATIVOS
                $precioFinal = max($precioFinal, 0);
            }

            return [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'descripcion' => $producto->descripcion,
                'tipo_producto' => $producto->tipo_producto,
                'precio_base' => $precioOriginal,
                'precio_final' => round($precioFinal, 2),
                'imagen' => $producto->imagen_principal_url,

                /**
                 * PROMO
                 */
                'promocion' => [
                    'id' => $promocion->id,
                    'nombre' => $promocion->nombre,
                    'tipo' => $promocion->tipo,
                    'valor' => $promocion->valor,
                ]
            ];
        });

        return response()->json($productos);
    }

    /**
     * TODOS LOS PRODUCTOS EN PROMOCIÓN
     */
    public function getPromoEcoInit(Request $request)
    {
        $productos = Producto::query()
            ->where('estado', 1)
            ->where('ecommerce', 1)
            /**
             * SOLO PRODUCTOS
             * CON PROMOCIÓN VIGENTE
             */
            ->whereHas('promociones', function ($q) {
                $q->vigente();
            })
            ->with([
                'imagenPrincipal',
                /**
                 * SOLO PROMO VIGENTE
                 */
                'promociones' => function ($q) {
                    $q->vigente()
                        ->orderBy('fecha_fin');
                }
            ])
            ->paginate(12);
        $productos->getCollection()->transform(function ($producto) {
            /**
             * TOMAR PRIMERA PROMO
             */
            $promocion = $producto->promociones->first();
            if (!$promocion) {
                return null;
            }
            $precioOriginal = $producto->precio_base;
            $precioFinal = $precioOriginal;
            /**
             * CALCULAR DESCUENTO
             */
            if (
                $producto->tipo_producto === 'simple' &&
                $precioOriginal
            ) {
                if ($promocion->tipo === 'porcentaje') {
                    $precioFinal =
                        $precioOriginal -
                        ($precioOriginal *
                            ($promocion->valor / 100));
                } else {
                    $precioFinal =
                        $precioOriginal -
                        $promocion->valor;
                }

                $precioFinal = max($precioFinal, 0);
            }

            return [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'descripcion' => $producto->descripcion,
                'tipo_producto' => $producto->tipo_producto,
                'precio_base' => $precioOriginal,
                'precio_final' => round($precioFinal, 2),
                'imagen' => $producto->imagen_principal_url,
                /**
                 * PROMO
                 */
                'promocion' => [
                    'id' => $promocion->id,
                    'nombre' => $promocion->nombre,
                    'tipo' => $promocion->tipo,
                    'valor' => $promocion->valor,
                ]
            ];
        });

        /**
         * ELIMINAR NULLS
         */
        $productos->setCollection(
            $productos->getCollection()->filter()->values()
        );

        return response()->json($productos);
    }
}
