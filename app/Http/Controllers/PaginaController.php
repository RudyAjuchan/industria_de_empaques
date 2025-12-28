<?php

namespace App\Http\Controllers;

use App\Exports\PaginaExport;
use App\Models\Pagina;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class PaginaController extends Controller
{
    public function index()
    {
        return Pagina::where('estado', 1)
            ->orderBy('nombre')
            ->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        return Pagina::create([
            'nombre' => $request->nombre,
        ]);
    }

    public function update(Request $request, Pagina $pagina)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
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
        $pagina = Pagina::
            where('nombre', 'LIKE', '%'.$search.'%')
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
