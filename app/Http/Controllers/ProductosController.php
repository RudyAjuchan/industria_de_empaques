<?php

namespace App\Http\Controllers;

use App\Exports\ProductosExport;
use App\Models\Pagina;
use App\Models\Producto;
use App\Models\ProductoImagen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ProductosController extends Controller
{
    /* LISTAR */
    public function index()
    {
        return Producto::with(['imagenes'])
            ->where('estado', 1)
            ->orderBy('id', 'desc')
            ->get();
    }

    public function getPaginas()
    {
        return Pagina::where('estado', 1)
            ->orderBy('nombre')
            ->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string',
            'alto' => 'required|numeric',
            'ancho' => 'required|numeric',
            'fuelle' => 'required|numeric',
            'tipo' => 'required|string',
            'paginas_id' => 'required|exists:paginas,id',
        ]);

        DB::beginTransaction();

        try {
            $producto = Producto::create($request->only([
                'nombre',
                'alto',
                'ancho',
                'fuelle',
                'tipo',
                'paginas_id',
            ]));

            if ($request->hasFile('imagenes_nuevas')) {
                $mainIndex = $request->input('main_index', 0);
                foreach ($request->file('imagenes_nuevas') as $index => $imagen) {
                    $path = $imagen->store('productos', 'public');

                    $producto->imagenes()->create([
                        'path' => $path,
                        'is_main' => $index == $mainIndex,
                        'orden' => $index,
                    ]);
                }
            }

            DB::commit();

            return response()->json(
                $producto->load('imagenes'),
                201
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al crear producto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Producto $producto)
    {
        return $producto->load([
            'imagenes' => fn($q) => $q->orderBy('orden')
        ]);
    }

    public function update(Request $request, Producto $producto)
    {
        $request->validate([
            'nombre' => 'required|string',
            'alto' => 'required|numeric',
            'ancho' => 'required|numeric',
            'fuelle' => 'required|numeric',
            'tipo' => 'required|string',
            'paginas_id' => 'required|exists:paginas,id',
            'main_index' => 'required|integer',
            'imagenes_ordenadas' => 'required|array',
        ]);

        // 1. Actualizar producto
        $producto->update($request->only([
            'nombre',
            'alto',
            'ancho',
            'fuelle',
            'tipo',
            'paginas_id'
        ]));

        // 2. Eliminar imágenes que ya no existen
        $idsExistentes = collect($request->imagenes_ordenadas)
            ->where('tipo', 'existente')
            ->pluck('id')
            ->toArray();

        $producto->imagenes()
            ->whereNotIn('id', $idsExistentes)
            ->each(function ($img) {
                Storage::disk('public')->delete($img->path);
                $img->delete();
            });

        // 3. Procesar orden + principal
        foreach ($request->imagenes_ordenadas as $index => $imgData) {

            // EXISTENTE
            if ($imgData['tipo'] === 'existente') {
                ProductoImagen::where('id', $imgData['id'])->update([
                    'orden' => $index,
                    'is_main' => ($index == $request->main_index),
                ]);
            }

            // NUEVA
            if ($imgData['tipo'] === 'nueva' && isset($imgData['file'])) {
                $producto->imagenes()->create([
                    'path' => $imgData['file']->store('productos', 'public'),
                    'orden' => $index,
                    'is_main' => ($index == $request->main_index),
                ]);
            }
        }

        return response()->json(['ok' => true]);
    }


    public function destroy(Producto $producto)
    {
        DB::beginTransaction();

        try {
            foreach ($producto->imagenes as $img) {
                Storage::disk('public')->delete($img->path);
            }

            $producto->delete();

            DB::commit();

            return response()->json(['message' => 'Producto eliminado']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al eliminar producto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function exportPdf()
    {
        $productos = Producto::with('imagenes')->get();

        $pdf = Pdf::loadView('productos.pdf', compact('productos'));
        return $pdf->download('productos.pdf');
    }

    public function exportExcel()
    {
        return Excel::download(new ProductosExport, 'productos.xlsx');
    }
}
