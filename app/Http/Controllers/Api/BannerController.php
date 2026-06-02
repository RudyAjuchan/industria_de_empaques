<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Listado
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $banners = Banner::with(['producto', 'promocion'])
            ->orderBy('orden')
            ->latest()
            ->get();

        return response()->json($banners);
    }

    /*
    |--------------------------------------------------------------------------
    | Crear
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        $request->validate([
            'imagen' => 'required|mimes:jpg,jpeg,png,webp|max:5120',
            'tipo_redireccion' => 'required|in:ninguno,producto,tipo,promocion',
            'producto_id' => 'nullable|required_if:tipo_redireccion,producto|exists:productos,id',
            'tipo_producto' => 'nullable|required_if:tipo_redireccion,tipo|string',
            'promocion_id' => 'nullable|required_if:tipo_redireccion,promocion|exists:promocions,id',
            'orden' => 'nullable|integer|min:0',
            'activo' => 'nullable|boolean',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Imagen S3
        |--------------------------------------------------------------------------
        */

        $path = null;

        $path = $request->file('imagen')
            ->store('banners', 's3');

        /*
        |--------------------------------------------------------------------------
        | Crear banner
        |--------------------------------------------------------------------------
        */
        $banner = Banner::create([
            'imagen' => $path,
            'tipo_redireccion' => $request->tipo_redireccion,
            'productos_id' => $request->tipo_redireccion === 'producto' ? $request->producto_id : null,
            'tipo_producto' => $request->tipo_redireccion === 'tipo' ? $request->tipo_producto : null,
            'promocions_id' => $request->tipo_redireccion === 'promocion' ? $request->promocion_id : null,
            'orden' => $request->orden ?? 0,
            'activo' => $request->boolean('activo', true),
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
        ]);

        return response()->json([
            'message' => 'Banner creado correctamente',
            'banner' => $banner
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Mostrar
    |--------------------------------------------------------------------------
    */
    public function show(Banner $banner)
    {
        return response()->json(
            $banner->load([
                'producto',
                'promocion'
            ])
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Actualizar
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, Banner $banner)
    {
        $request->validate([
            'imagen' => 'nullable|mimes:jpg,jpeg,png,webp|max:5120',
            'tipo_redireccion' => 'required|in:ninguno,producto,tipo,promocion',
            'producto_id' => 'nullable|required_if:tipo_redireccion,producto|exists:productos,id',
            'tipo_producto' => 'nullable|required_if:tipo_redireccion,tipo|string',
            'promocion_id' => 'nullable|required_if:tipo_redireccion,promocion|exists:promocions,id',
            'orden' => 'nullable|integer|min:0',
            'activo' => 'nullable|boolean',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Reemplazar imagen
        |--------------------------------------------------------------------------
        */
        if ($request->hasFile('imagen')) {

            if ($banner->imagen && Storage::disk('s3')->exists($banner->imagen)) {
                Storage::disk('s3')->delete($banner->imagen);
            }

            $banner->imagen = $request->file('imagen')
                ->store('banners', 's3');
        }

        /*
        |--------------------------------------------------------------------------
        | Actualizar
        |--------------------------------------------------------------------------
        */
        $banner->update([
            'imagen' => $banner->imagen,
            'tipo_redireccion' => $request->tipo_redireccion,
            'productos_id' => $request->tipo_redireccion === 'producto' ? $request->producto_id : null,
            'tipo_producto' => $request->tipo_redireccion === 'tipo' ? $request->tipo_producto : null,
            'promocions_id' => $request->tipo_redireccion === 'promocion' ? $request->promocion_id : null,
            'orden' => $request->orden ?? 0,
            'activo' => $request->boolean('activo', true),
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
        ]);

        return response()->json([
            'message' => 'Banner actualizado correctamente',
            'banner' => $banner
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Eliminar
    |--------------------------------------------------------------------------
    */
    public function destroy(Banner $banner)
    {
        /*
        |--------------------------------------------------------------------------
        | Eliminar imagen S3
        |--------------------------------------------------------------------------
        */
        if ($banner->imagen) {
            Storage::disk('s3')->delete($banner->imagen);
        }

        $banner->delete();

        return response()->json([
            'message' => 'Banner eliminado correctamente'
        ]);
    }

    public function buscar(Request $request)
    {
        $search = $request->search;

        $productos = Producto::query()
            ->leftJoin('paginas', 'paginas.id', '=', 'productos.paginas_id')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('productos.id', 'LIKE', "%{$search}%")
                        ->orWhere('productos.nombre', 'LIKE', "%{$search}%");
                });
            })
            ->selectRaw(" productos.id, CONCAT( productos.id, ' - ', productos.nombre, ' / ', productos.tipo, ' / ', paginas.nombre) as nombre, productos.tipo")
            ->orderBy('productos.nombre')
            ->limit(20)
            ->get();

        return response()->json($productos);
    }

    public function getTiposProd()
    {
        $tipos = Producto::query()
            ->whereNotNull('tipo')
            ->where('tipo', '!=', '')
            ->distinct()
            ->orderBy('tipo')
            ->pluck('tipo');

        return response()->json($tipos);
    }

    public function ecommerce()
    {
        $today = now()->toDateString();
        $banners = Banner::query()
            ->where('activo', true)
            ->where(function ($query) use ($today) {
                $query->whereNull('fecha_inicio')
                    ->orWhereDate('fecha_inicio', '<=', $today);
            })
            ->where(function ($query) use ($today) {
                $query->whereNull('fecha_fin')
                    ->orWhereDate('fecha_fin', '>=', $today);
            })
            ->orderBy('orden')
            ->get();

        return response()->json($banners);
    }
}
