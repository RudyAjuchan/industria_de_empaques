<?php

namespace App\Http\Controllers;

use App\Exports\TipoAgarradorExport;
use App\Models\TipoAgarrador;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class TipoAgarradorController extends Controller
{
    public function index()
    {
        return TipoAgarrador::where('estado', 1)
            ->orderBy('nombre')
            ->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        return TipoAgarrador::create([
            'nombre' => $request->nombre,
        ]);
    }

    public function update(Request $request, TipoAgarrador $tipoAgarrador)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        $tipoAgarrador->update([
            'nombre' => $request->nombre,
        ]);

        return response()->json(['message' => 'Actualizado']);
    }

    public function destroy(TipoAgarrador $tipoAgarrador)
    {
        $tipoAgarrador->update(['estado' => 0]);

        return response()->json(['message' => 'Eliminado']);
    }

    public function exportPdf(Request $request)
    {
        $search = ($request->input('search') === "null") ? null : $request->input('search');
        $tipo_agarrador = TipoAgarrador::
            where('nombre', 'LIKE', '%'.$search.'%')
            ->where('estado', 1)
            ->orderBy('nombre')
            ->get();
        return Pdf::loadView('pdf.tipo_agarrador', compact('tipo_agarrador', 'search'))
            ->setPaper('letter', 'portrait')
            ->stream('tipo_agarrador.pdf');
    }

    public function exportExcel(Request $request)
    {
        $search = $request->query('search');

        return Excel::download(
            new TipoAgarradorExport($search),
            'tipo_agarrador.xlsx'
        );
    }
}
