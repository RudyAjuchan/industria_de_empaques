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

class CheckoutController extends Controller
{

    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {

            $cliente = auth('cliente')->user();

            if (!$cliente) {
                throw new \Exception('No autenticado');
            }

            $data = $request->all();
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
                'fecha_entrega' => now()->addDays(5),
                'tipo_pago' => 'cotizacion',
                'subtotal' => 0,
                'descuento' => 0,
                'promociones' => null,
                'costo_envio' => 0,
                'total' => 0,
                'estado' => 'pendiente',
            ]);

            foreach ($data['detalle'] as $item) {

                $producto = Producto::find($item['productos_id']);

                // PRECIO CORRECTO
                $precio = $producto->tipo_producto === 'simple'
                    ? $producto->precio_base
                    : 0;

                $totalItem = $precio * $item['cantidad'];

                // PROMO PRODUCTO
                $promo = Promocion::vigente()
                    ->where('aplica_a', 'producto')
                    ->whereHas('productos', function ($q) use ($item) {
                        $q->where('productos.id', $item['productos_id']);
                    })
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
                }

                $subtotal += $totalItem;

                $venta->detalles()->create([
                    'productos_id' => $item['productos_id'],

                    'tipo_agarradors_id' => $producto->tipo_producto === 'simple' ? null : $item['tipo_agarradors_id'],
                    'tipo_papels_id' => $producto->tipo_producto === 'simple' ? null : $item['tipo_papels_id'],

                    'color_agarrador' => $producto->tipo_producto === 'simple' ? null : ($item['color_agarrador'] ?? ''),
                    'detalle_impresion' => $producto->tipo_producto === 'simple' ? null : ($item['detalle_impresion'] ?? ''),
                    'nombre_logo' => $producto->tipo_producto === 'simple' ? null : ($item['nombre_logo'] ?? ''),

                    'logo_path' => $item['logo_path'] ?? null,

                    'promocion_aplicada' => $promocionAplicada,

                    'precio' => $precio,
                    'cantidad' => $item['cantidad'],
                    'total' => $totalItem,

                    'proceso_estado_produccions_id' => 1,
                ]);
            }

            // PROMO CARRITO
            $promoCarrito = Promocion::vigente()
                ->where('aplica_a', 'carrito')
                ->first();

            if ($promoCarrito) {

                if ($promoCarrito->tipo === 'porcentaje') {
                    $subtotal -= $subtotal * ($promoCarrito->valor / 100);
                } else {
                    $subtotal -= $promoCarrito->valor;
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
            $venta->total = $subtotal;
            $venta->save();

            $venta->load('detalles.producto');

            // EMAIL
            Mail::to($cliente->email)
                ->send(new CheckoutClienteMail($cliente, $venta));

            Mail::to('rudyajuchansec44@gmail.com')
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
            $path = $request->file('logo')->store('logos', 'public');

            return response()->json([
                'path' => $path,
                'url' => asset('storage/' . $path)
            ]);
        }

        return response()->json(['error' => 'No file'], 400);
    }

    public function deleteLogo(Request $request)
    {
        $path = $request->path;

        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        return response()->json(['message' => 'Eliminado']);
    }
}
