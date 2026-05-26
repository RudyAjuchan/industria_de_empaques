<?php

namespace App\Http\Controllers;

use App\Exports\PaginaExport;
use App\Models\Pagina;
use App\Models\Producto;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class PaginaController extends Controller
{
    public function index()
    {
        return Pagina::where('estado', 1)
            ->orderBy('nombre')
            ->get();
    }
    public function getTipos()
    {
        return Producto::where('estado', 1)
            ->where('ecommerce', 1)
            ->select('tipo')
            ->distinct()
            ->pluck('tipo');
    }
    public function getTiposProducts()
    {
        return Producto::where('estado', 1)
            ->where('ecommerce', 1)
            ->select('tipo as nombre', DB::raw('count(*) as total'))
            ->groupBy('tipo')
            ->get();
    }

    public function getTiposFooter()
    {
        $tipos = Producto::query()
            ->whereNotNull('tipo')
            ->where('tipo', '!=', '')
            ->where('estado', 1)
            ->where('ecommerce', 1)
            ->distinct()
            ->orderBy('tipo')
            ->limit(5)
            ->pluck('tipo');
        return response()->json($tipos);
    }

    public function getTiposSlider()
    {
        $tipos = Producto::query()
            ->selectRaw('tipo, COUNT(*) as total')
            ->whereNotNull('tipo')
            ->where('estado', 1)
            ->where('ecommerce', 1)
            ->groupBy('tipo')
            ->orderBy('tipo')
            ->get();

        return response()->json($tipos);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:paginas,nombre',
        ]);

        return Pagina::create([
            'nombre' => $request->nombre,
        ]);
    }

    public function update(Request $request, Pagina $pagina)
    {
        $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('paginas', 'nombre')->ignore($pagina->id),
            ],
        ]);

        $pagina->update([
            'nombre' => $request->nombre,
        ]);

        return response()->json(['message' => 'Actualizado']);
    }

    public function destroy(Pagina $pagina)
    {
        $pagina->update(['estado' => 0]);

        return response()->json(['message' => 'Eliminado']);
    }

    public function exportPdf(Request $request)
    {
        $search = ($request->input('search') === "null") ? null : $request->input('search');
        $pagina = Pagina::where('nombre', 'LIKE', '%' . $search . '%')
            ->where('estado', 1)
            ->orderBy('nombre')
            ->get();
        return Pdf::loadView('pdf.pagina', compact('pagina', 'search'))
            ->setPaper('letter', 'portrait')
            ->stream('paginas.pdf');
    }

    public function exportExcel(Request $request)
    {
        $search = $request->query('search');

        return Excel::download(
            new PaginaExport($search),
            'paginas.xlsx'
        );
    }
}
