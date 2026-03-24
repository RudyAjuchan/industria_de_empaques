<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\CheckoutClienteMail;
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

            $venta = Venta::create([
                'serie' => $serie,
                'numero' => $numero,
                'vendedor_id' => 1,
                'clientes_id' => $cliente->id,
                'fecha_entrega' => now()->addDays(5),
                'tipo_pago' => 'cotizacion',
                'subtotal' => 0,
                'descuento' => 0,
                'promociones' => 0,
                'costo_envio' => 0,
                'total' => 0,
                'estado' => 'pendiente',
            ]);

            foreach ($data['detalle'] as $item) {

                // BUSCAR PROMO REAL
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
                }

                $venta->detalles()->create([
                    'productos_id' => $item['productos_id'],
                    'tipo_agarradors_id' => $item['tipo_agarradors_id'],
                    'tipo_papels_id' => $item['tipo_papels_id'],
                    'color_agarrador' => $item['color_agarrador'] ?? '',
                    'detalle_impresion' => $item['detalle_impresion'] ?? '',
                    'nombre_logo' => $item['nombre_logo'] ?? '',
                    'logo_path' => $item['logo_path'] ?? null,

                    // PROMO GUARDADA
                    'promocion_aplicada' => $promocionAplicada,

                    'precio' => 0,
                    'cantidad' => $item['cantidad'],
                    'total' => 0,
                    'proceso_estado_produccions_id' => 1,
                ]);
            }

            // PROMO GLOBAL (CARRITO)
            $promoCarrito = Promocion::vigente()
                ->where('aplica_a', 'carrito')
                ->first();

            if ($promoCarrito) {
                $venta->promociones = [
                    'id' => $promoCarrito->id,
                    'nombre' => $promoCarrito->nombre,
                    'tipo' => $promoCarrito->tipo,
                    'valor' => $promoCarrito->valor
                ];

                $venta->save(); // TE FALTA ESTO
            }

            // CARGAR DETALLES
            $venta->load('detalles.producto', 'detalles.tipoAgarrador', 'detalles.tipoPapel');

            // ENVIAR CORREO
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
