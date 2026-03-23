<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\Producto;
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

        return $query->paginate(12);
    }

    public function show($id)
    {
        return response()->json([
            'producto' => Producto::with(['imagenes', 'paginas'])->findOrFail($id),

            'configuracion' => [
                'tipo_agarradores' => TipoAgarrador::where('estado', 1)->get(),
                'tipo_papeles' => TipoPapel::where('estado', 1)->get(),
            ]
        ]);
    }
}
