<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\CheckoutClienteMail;
use App\Models\Producto;
use App\Models\Promocion;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{

    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {

            $cliente = auth('cliente')->user();

            if (!$cliente) {
                throw new \Exception('No autenticado');
            }

            $data = $request->validate([
                'paginas_id' => ['nullable', 'integer', 'exists:paginas,id'],
                'detalle' => ['required', 'array', 'min:1'],
                'detalle.*.productos_id' => ['required', 'integer', 'exists:productos,id'],
                'detalle.*.cantidad' => ['required', 'integer', 'gt:0'],
                'detalle.*.tipo_agarradors_id' => ['nullable', 'exists:tipo_agarradors,id'],
                'detalle.*.tipo_papels_id' => ['nullable', 'exists:tipo_papels,id'],
                'detalle.*.color_agarrador' => ['nullable', 'string', 'max:255'],
                'detalle.*.detalle_impresion' => ['nullable', 'string'],
                'detalle.*.nombre_logo' => ['nullable', 'string', 'max:255'],
                'detalle.*.imagenes' => ['nullable', 'array'],
                'detalle.*.imagenes.*' => ['string', 'max:2048'],
            ]);
            $serie = 'WEB';

            $ultimoNumero = Venta::where('serie', $serie)
                ->lockForUpdate()
                ->max('numero');

            $numero = ($ultimoNumero ?? 0) + 1;

            $subtotal = 0;

            $venta = Venta::create([
                'serie' => $serie,
                'numero' => $numero,
                'vendedor_id' => 1,
                'clientes_id' => $cliente->id,
                'paginas_id' => $data['paginas_id'] ?? null,
                'fecha_entrega' => now()->addDays(5),
                'tipo_pago' => 'cotizacion',
                'subtotal' => 0,
                'descuento' => 0,
                'promociones' => null,
                'costo_envio' => 0,
                'total' => 0,
                'estado' => 'pendiente',
            ]);

            foreach ($data['detalle'] as $index => $item) {

                $producto = Producto::where('estado', 1)
                    ->where('ecommerce', 1)
                    ->find($item['productos_id']);

                if (!$producto) {
                    throw ValidationException::withMessages([
                        "detalle.$index.productos_id" => 'El producto no está disponible en ecommerce.',
                    ]);
                }

                if ($producto->tipo_producto === 'personalizado') {
                    $camposRequeridos = [
                        'tipo_agarradors_id' => 'El tipo de agarrador es obligatorio.',
                        'tipo_papels_id' => 'El tipo de papel es obligatorio.',
                        'color_agarrador' => 'El color del agarrador es obligatorio.',
                        'detalle_impresion' => 'El detalle de impresión es obligatorio.',
                        'nombre_logo' => 'El nombre del logo es obligatorio.',
                    ];

                    foreach ($camposRequeridos as $campo => $mensaje) {
                        if (empty($item[$campo])) {
                            throw ValidationException::withMessages([
                                "detalle.$index.$campo" => $mensaje,
                            ]);
                        }
                    }
                }

                // PRECIO CORRECTO
                $precio = $producto->tipo_producto === 'simple'
                    ? $producto->precio_base
                    : 0;

                $totalItem = $precio * $item['cantidad'];

                // PROMO PRODUCTO
                $promo = Promocion::vigente()
                    ->producto()
                    ->whereHas('productos', function ($q) use ($item) {
                        $q->where('productos.id', $item['productos_id']);
                    })
                    ->orderBy('fecha_fin')
                    ->orderBy('id')
                    ->first();

                $promocionAplicada = null;

                if ($promo) {
                    $promocionAplicada = [
                        'id' => $promo->id,
                        'nombre' => $promo->nombre,
                        'tipo' => $promo->tipo,
                        'valor' => $promo->valor
                    ];

                    // aplicar descuento
                    if ($promo->tipo === 'porcentaje') {
                        $totalItem -= $totalItem * ($promo->valor / 100);
                    } else {
                        $totalItem -= $promo->valor;
                    }

                    $totalItem = max($totalItem, 0);
                }

                $subtotal += $totalItem;

                $detalle = $venta->detalles()->create([
                    'productos_id' => $item['productos_id'],
                    'producto_sku' => $producto->sku,
                    'producto_nombre' => $producto->nombre,
                    'producto_tipo' => $producto->tipo,
                    'producto_tipo_producto' => $producto->tipo_producto,
                    'producto_alto' => $producto->alto,
                    'producto_ancho' => $producto->ancho,
                    'producto_fuelle' => $producto->fuelle,
                    'producto_descripcion' => $producto->descripcion,

                    'tipo_agarradors_id' => $producto->tipo_producto === 'simple' ? null : $item['tipo_agarradors_id'],
                    'tipo_papels_id' => $producto->tipo_producto === 'simple' ? null : $item['tipo_papels_id'],

                    'color_agarrador' => $producto->tipo_producto === 'simple' ? null : ($item['color_agarrador'] ?? ''),
                    'detalle_impresion' => $producto->tipo_producto === 'simple' ? null : ($item['detalle_impresion'] ?? ''),
                    'nombre_logo' => $producto->tipo_producto === 'simple' ? null : ($item['nombre_logo'] ?? ''),

                    'promocion_aplicada' => $promocionAplicada,

                    'precio' => $precio,
                    'cantidad' => $item['cantidad'],
                    'total' => $totalItem,

                    'proceso_estado_produccions_id' => 1,
                ]);

                /*
                |--------------------------------------------------------------------------
                | IMÁGENES
                |--------------------------------------------------------------------------
                */
                if (!empty($item['imagenes'])) {

                    foreach ($item['imagenes'] as $path) {

                        $detalle->imagenes()->create([
                            'path' => $path,
                            'estado' => 1
                        ]);
                    }
                }
            }

            // PROMO CARRITO
            $promocionCarritoMonto = 0;
            $promoCarrito = Promocion::vigente()
                ->carrito()
                ->orderBy('fecha_fin')
                ->orderBy('id')
                ->first();

            if ($promoCarrito) {

                if ($promoCarrito->tipo === 'porcentaje') {
                    $promocionCarritoMonto = $subtotal * ($promoCarrito->valor / 100);
                } else {
                    $promocionCarritoMonto = $promoCarrito->valor;
                }

                $venta->promociones = [
                    'id' => $promoCarrito->id,
                    'nombre' => $promoCarrito->nombre,
                    'tipo' => $promoCarrito->tipo,
                    'valor' => $promoCarrito->valor
                ];
            }

            // ACTUALIZAR TOTALES
            $venta->subtotal = $subtotal;
            $venta->total = max(0, $subtotal - $promocionCarritoMonto);
            $venta->save();

            $venta->load('detalles.producto', 'pagos');

            // EMAIL
            Mail::to($cliente->email)
                ->send(new CheckoutClienteMail($cliente, $venta));

            Mail::to(config('mail.admin_address'))
                ->send(new CheckoutClienteMail($cliente, $venta));

            return response()->json([
                'message' => 'Solicitud enviada correctamente',
                'venta_id' => $venta->id
            ]);
        });
    }

    public function uploadLogo(Request $request)
    {
        if ($request->hasFile('logo')) {
            // 1. Cambiamos el disco a 's3' 
            // 2. Eliminamos el parámetro 'public' (ya que el bucket es privado)
            $path = $request->file('logo')->store('ventas/detalles', 's3');

            return response()->json([
                'path' => $path,
                'url' => Storage::disk('s3')->url($path)
            ]);
        }

        return response()->json(['error' => 'No file'], 400);
    }

    public function deleteLogo(Request $request)
    {
        $path = $request->path;

        // Cambiamos el disco a 's3' para que busque y elimine allá
        if ($path && Storage::disk('s3')->exists($path)) {
            Storage::disk('s3')->delete($path);
        }

        return response()->json(['message' => 'Eliminado']);
    }
}
