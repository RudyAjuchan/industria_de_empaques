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
        $request->merge([
            'codigo' => $this->normalizeCodigo($request->codigo),
        ]);

        $request->validate([
            'nombre' => 'required|string|max:255|unique:paginas,nombre',
            'codigo' => 'nullable|string|min:2|max:10|regex:/^[A-Z0-9]+$/|unique:paginas,codigo',
        ], $this->messages());

        return Pagina::create([
            'nombre' => $request->nombre,
            'codigo' => $request->codigo,
        ]);
    }

    public function update(Request $request, Pagina $pagina)
    {
        $request->merge([
            'codigo' => $this->normalizeCodigo($request->codigo),
        ]);

        $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('paginas', 'nombre')->ignore($pagina->id),
            ],
            'codigo' => [
                'nullable',
                'string',
                'min:2',
                'max:10',
                'regex:/^[A-Z0-9]+$/',
                Rule::unique('paginas', 'codigo')->ignore($pagina->id),
            ],
        ], $this->messages());

        $pagina->update([
            'nombre' => $request->nombre,
            'codigo' => $request->codigo,
        ]);

        return response()->json($pagina);
    }

    public function destroy(Pagina $pagina)
    {
        $pagina->update([
            'nombre' => substr($pagina->nombre, 0, 230) . ' - eliminado ' . $pagina->id,
            'codigo' => substr(($pagina->codigo ?? 'PG') . 'X' . $pagina->id, 0, 10),
            'estado' => 0,
        ]);

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

    private function normalizeCodigo($codigo): ?string
    {
        $codigo = strtoupper(preg_replace('/[^A-Z0-9]/', '', (string) $codigo));

        return $codigo !== '' ? $codigo : null;
    }

    private function messages(): array
    {
        return [
            'codigo.regex' => 'El código solo puede contener letras mayúsculas y números, sin espacios.',
            'codigo.unique' => 'Ya existe una página con ese código.',
            'nombre.unique' => 'Ya existe una página con ese nombre.',
        ];
    }
}
