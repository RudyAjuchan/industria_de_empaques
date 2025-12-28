<?php

namespace App\Http\Controllers;

use App\Exports\BancoExport;
use App\Models\Banco;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class BancoController extends Controller
{
    public function index()
    {
        return Banco::where('estado', 1)
            ->orderBy('nombre')
            ->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        return Banco::create([
            'nombre' => $request->nombre,
        ]);
    }

    public function update(Request $request, Banco $banco)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        $banco->update([
            'nombre' => $request->nombre,
        ]);

        return response()->json(['message' => 'Actualizado']);
    }

    public function destroy(Banco $banco)
    {
        $banco->update(['estado' => 0]);

        return response()->json(['message' => 'Eliminado']);
    }

    public function exportPdf(Request $request)
    {
        $search = ($request->input('search') === "null") ? null : $request->input('search');
        $banco = Banco::where('nombre', 'LIKE', '%' . $search . '%')
            ->where('estado', 1)
            ->orderBy('nombre')
            ->get();
        return Pdf::loadView('pdf.banco', compact('banco', 'search'))
            ->setPaper('letter', 'portrait')
            ->stream('bancos.pdf');
    }

    public function exportExcel(Request $request)
    {
        $search = $request->query('search');

        return Excel::download(
            new BancoExport($search),
            'bancos.xlsx'
        );
    }
}
