<?php

namespace App\Http\Controllers;

use App\Models\Promocion;
use Illuminate\Http\Request;

class PromocionController extends Controller
{
    public function index()
    {
        return Promocion::with('productos')->where('activo', true)->latest()->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required',
            'tipo' => 'required|in:porcentaje,monto',
            'valor' => 'required|numeric|min:0',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'aplica_a' => 'required|in:producto,carrito',
            'productos' => 'array'
        ]);

        $promo = Promocion::create($data);

        if ($data['aplica_a'] === 'producto' && !empty($data['productos'])) {
            $promo->productos()->sync($data['productos']);
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

        $data = $request->validate([
            'nombre' => 'required',
            'tipo' => 'required|in:porcentaje,monto',
            'valor' => 'required|numeric|min:0',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'aplica_a' => 'required|in:producto,carrito',
            'productos' => 'array'
        ]);

        $promo->update($data);

        if ($data['aplica_a'] === 'producto') {
            $promo->productos()->sync($data['productos'] ?? []);
        } else {
            $promo->productos()->detach();
        }

        return response()->json($promo->load('productos'));
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
}
