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

        $query = Producto::with(['paginas'])->where('estado', 1);

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

    public function productosProm(Request $request)
    {
        $productos = Producto::where('estado', 1)->get();

        return $productos;
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
            'alto' => 'nullable|numeric',
            'ancho' => 'nullable|numeric',
            'fuelle' => 'nullable|numeric',
            'tipo' => 'required|string',
            'paginas_id' => 'required|exists:paginas,id',

            'tipo_producto' => 'required|in:personalizado,simple',
            'precio_base' => 'nullable|numeric',
            'descripcion' => 'nullable|string',

            'main_index' => 'required|integer',
        ]);

        DB::beginTransaction();
        try {
            $data = $request->only(['nombre', 'alto', 'ancho', 'fuelle', 'tipo', 'paginas_id', 'tipo_producto', 'precio_base', 'descripcion']);

            if ($data['tipo_producto'] === 'simple') {
                $data['alto'] = $data['ancho'] = $data['fuelle'] = null;
            } else {
                $data['precio_base'] = null;
            }

            $producto = Producto::create($data);

            if ($request->has('imagenes_ordenadas')) {
                foreach ($request->imagenes_ordenadas as $index => $imgData) {
                    // Forma correcta de obtener el archivo en un array de inputs
                    $file = $request->file("imagenes_ordenadas.{$index}.file");

                    if ($imgData['tipo'] === 'nueva' && $file) {
                        // Quitamos 'public' porque el bucket es privado
                        $path = Storage::disk('s3')->putFile('productos', $file);

                        $producto->imagenes()->create([
                            'path' => $path,
                            'orden' => $index,
                            'is_main' => ($index == $request->main_index),
                        ]);
                    }
                }
            }

            DB::commit();
            return response()->json($producto->load('imagenes'), 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al crear producto',
                'error' => $e->getMessage(), // Esto te dirá exactamente qué falla
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
            'alto' => 'nullable|numeric',
            'ancho' => 'nullable|numeric',
            'fuelle' => 'nullable|numeric',
            'tipo' => 'nullable|string',
            'paginas_id' => 'required|exists:paginas,id',
            'tipo_producto' => 'required|in:personalizado,simple',
            'precio_base' => 'nullable|numeric',
            'descripcion' => 'nullable|string',
            'main_index' => 'required|integer',
        ]);

        DB::beginTransaction();
        try {
            $data = $request->only([
                'nombre',
                'alto',
                'ancho',
                'fuelle',
                'tipo',
                'paginas_id',
                'tipo_producto',
                'precio_base',
                'descripcion'
            ]);

            // Lógica de tipo de producto
            if ($data['tipo_producto'] === 'simple') {
                $data['alto'] = $data['ancho'] = $data['fuelle'] = null;
            } else {
                $data['precio_base'] = null;
            }

            $producto->update($data);

            // ================= IMÁGENES =================
            if ($request->has('imagenes_ordenadas')) {

                // 1. Identificar y eliminar las que ya no están en el request
                $idsExistentes = collect($request->imagenes_ordenadas)
                    ->where('tipo', 'existente')
                    ->pluck('id')
                    ->filter()
                    ->toArray();

                $imagenesABorrar = $producto->imagenes()->whereNotIn('id', $idsExistentes)->get();

                foreach ($imagenesABorrar as $img) {
                    Storage::disk('s3')->delete($img->path);
                    $img->delete();
                }

                // 2. Procesar el orden y las nuevas subidas
                foreach ($request->imagenes_ordenadas as $index => $imgData) {

                    $esMain = ($index == $request->main_index);

                    if ($imgData['tipo'] === 'existente') {
                        ProductoImagen::where('id', $imgData['id'])->update([
                            'orden' => $index,
                            'is_main' => $esMain,
                        ]);
                    }

                    if ($imgData['tipo'] === 'nueva') {
                        // Acceso correcto al archivo dentro del array
                        $file = $request->file("imagenes_ordenadas.{$index}.file");

                        if ($file) {
                            $path = Storage::disk('s3')->putFile('productos', $file);

                            $producto->imagenes()->create([
                                'path' => $path,
                                'orden' => $index,
                                'is_main' => $esMain,
                            ]);
                        }
                    }
                }
            }

            DB::commit();
            return response()->json(['ok' => true]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al actualizar producto',
                'error' => $e->getMessage(),
            ], 500);
        }
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

    public function search(Request $request)
    {
        return Producto::where('estado', 1)->where('paginas_id', $request->id)->get();
    }
}
