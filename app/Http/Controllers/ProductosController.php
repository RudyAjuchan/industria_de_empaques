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
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search = $request->input('search');
        $sortBy = $request->input('sort_by', 'id');
        $sortOrder = $request->input('sort_order', 'desc');

        $query = Producto::with(['paginas']);

        if ($search) {
            $query->where('nombre', 'like', "%{$search}%");
        }

        $allowedSorts = ['id', 'nombre', 'created_at', 'updated_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'id';
        }

        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage);
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
            //'imagenes_ordenadas' => 'required|array',
            'main_index' => 'required|integer',
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
            if($request->imagenes_ordenadas ){
                foreach ($request->imagenes_ordenadas as $index => $imgData) {
    
                    if ($imgData['tipo'] === 'nueva' && isset($imgData['file'])) {
                        $producto->imagenes()->create([
                            'path' => $imgData['file']->store('productos', 'public'),
                            'orden' => $index,
                            'is_main' => ($index == $request->main_index),
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json(
                $producto->load('imagenes'),
                201
            );
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Error al crear producto',
                'error' => $e->getMessage(),
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
            //'imagenes_ordenadas' => 'required|array',
        ]);

        $producto->update($request->only([
            'nombre',
            'alto',
            'ancho',
            'fuelle',
            'tipo',
            'paginas_id'
        ]));

        if($request->imagenes_ordenadas ){
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
        }


        return response()->json(['ok' => true]);
    }


    public function destroy(Producto $producto)
    {
        DB::beginTransaction();

        try {
            $producto->update(['estado' => 0]);

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

    public function exportPdf(Request $request)
    {
        $search = $request->input('search');
        $search = ($search === 'null' || $search === '') ? null : $search;

        $productos = Producto::query()
            ->with(['imagenes' => function ($q) {
                $q->where('is_main', true);
            }])
            ->when($search, function ($query) use ($search) {
                $query->where('nombre', 'like', '%' . $search . '%')
                    ->orWhere('tipo', 'like', '%' . $search . '%');
            })
            ->where('estado', 1)
            ->orderBy('id')
            ->get();

        $pdf = Pdf::loadView('pdf.productos', [
            'productos' => $productos,
            'search'    => $search,
        ])->setPaper('letter', 'portrait');

        return $pdf->stream('productos.pdf');
    }

    public function exportExcel(Request $request)
    {
        $search = $request->query('search');

        return Excel::download(
            new ProductosExport($search),
            'productos.xlsx'
        );
    }

    public function search(Request $request){
        return Producto::where('estado', 1)->where('paginas_id', $request->id)->get();
    }
}
