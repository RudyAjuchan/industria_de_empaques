<?php

namespace App\Http\Controllers;

use App\Models\TipoProducto;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TipoProductoController extends Controller
{
    public function index()
    {
        return TipoProducto::where('estado', 1)
            ->orderBy('nombre')
            ->get();
    }

    public function store(Request $request)
    {
        $request->merge([
            'codigo' => $this->normalizeCodigo((string) $request->codigo),
        ]);

        $data = $request->validate([
            'nombre' => 'required|string|max:255|unique:tipo_productos,nombre',
            'codigo' => 'required|string|min:2|max:10|regex:/^[A-Z0-9]+$/|unique:tipo_productos,codigo',
        ], $this->messages());

        return TipoProducto::create($data);
    }

    public function update(Request $request, TipoProducto $tipoProducto)
    {
        $request->merge([
            'codigo' => $this->normalizeCodigo((string) $request->codigo),
        ]);

        $data = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tipo_productos', 'nombre')->ignore($tipoProducto->id),
            ],
            'codigo' => [
                'required',
                'string',
                'min:2',
                'max:10',
                'regex:/^[A-Z0-9]+$/',
                Rule::unique('tipo_productos', 'codigo')->ignore($tipoProducto->id),
            ],
        ], $this->messages());

        $tipoProducto->update($data);

        return response()->json($tipoProducto);
    }

    public function destroy(TipoProducto $tipoProducto)
    {
        $tipoProducto->update([
            'nombre' => substr($tipoProducto->nombre, 0, 230) . ' - eliminado ' . $tipoProducto->id,
            'codigo' => substr($tipoProducto->codigo . 'X' . $tipoProducto->id, 0, 10),
            'estado' => 0,
        ]);

        return response()->json(['message' => 'Eliminado']);
    }

    private function normalizeCodigo(string $codigo): string
    {
        return strtoupper(preg_replace('/[^A-Z0-9]/', '', $codigo));
    }

    private function messages(): array
    {
        return [
            'codigo.regex' => 'El código solo puede contener letras mayúsculas y números, sin espacios.',
            'codigo.unique' => 'Ya existe un tipo con ese código.',
            'nombre.unique' => 'Ya existe un tipo con ese nombre.',
        ];
    }
}
