<?php

namespace App\Http\Controllers;

use App\Exports\TipoPapelExport;
use App\Models\TipoPapel;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class TipoPapelController extends Controller
{
    public function index()
    {
        return TipoPapel::where('estado', 1)
            ->orderBy('nombre')
            ->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:tipo_papels,nombre',
        ]);

        return TipoPapel::create([
            'nombre' => $request->nombre,
        ]);
    }

    public function update(Request $request, TipoPapel $tipoPapel)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:tipo_papels,nombre,' . $tipoPapel->id,
        ]);

        $tipoPapel->update([
            'nombre' => $request->nombre,
        ]);

        return response()->json(['message' => 'Actualizado']);
    }

    public function destroy(TipoPapel $tipoPapel)
    {
        $tipoPapel->update(['estado' => 0]);

        return response()->json(['message' => 'Eliminado']);
    }

    public function exportPdf(Request $request)
    {
        $search = ($request->input('search') === "null") ? null : $request->input('search');
        $tipo_papel = TipoPapel::
            where('nombre', 'LIKE', '%'.$search.'%')
            ->orderBy('nombre')
            ->get();
        return Pdf::loadView('pdf.tipo_papel', compact('tipo_papel', 'search'))
            ->setPaper('letter', 'portrait')
            ->stream('tipo_papel.pdf');
    }

    public function exportExcel(Request $request)
    {
        $search = $request->query('search');

        return Excel::download(
            new TipoPapelExport($search),
            'tipo_papel.xlsx'
        );
    }
}
