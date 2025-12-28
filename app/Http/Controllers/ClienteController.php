<?php

namespace App\Http\Controllers;

use App\Exports\ClienteExport;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class ClienteController extends Controller
{
    public function index()
    {
        return Cliente::where('estado', 1)->orderBy('nombre')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'genero' => 'required|string|max:20',
            'telefono' => 'nullable|string|max:50',
            'dpi' => 'nullable|string|digits:13',
            'email' => 'nullable|email|unique:clientes,email',
            'departamento' => 'nullable|string|max:255',
            'municipio' => 'nullable|string|max:255',
            'direccion' => 'nullable|string',
            'nit' => 'nullable|string|max:50',
        ]);

        $cliente = Cliente::create($data);

        return response()->json($cliente, 201);
    }

    public function show(Cliente $cliente)
    {
        return $cliente;
    }

    public function update(Request $request, Cliente $cliente)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'genero' => 'required|string|max:20',
            'telefono' => 'nullable|string|max:50',
            'dpi' => 'nullable|string|digits:13',
            'email' => 'nullable|email|unique:clientes,email,' . $cliente->id,
            'departamento' => 'nullable|string|max:255',
            'municipio' => 'nullable|string|max:255',
            'direccion' => 'nullable|string',
            'nit' => 'nullable|string|max:50',
        ]);

        $cliente->update($data);

        return response()->json($cliente);
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->update(['estado' => 0]);

        return response()->json([
            'message' => 'Cliente eliminado'
        ]);
    }

    public function exportPdf(Request $request)
    {
        $search = trim($request->query('search', ''));
        $search = ($search === "null") ? null : $search;

        $cliente = Cliente::when($search !== '', function ($q) use ($search) {
            $q->where(function ($sub) use ($search) {
                $sub->where('nombre', 'like', "%{$search}%")
                    ->orWhere('telefono', 'like', "%{$search}%")
                    ->orWhere('dpi', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('departamento', 'like', "%{$search}%")
                    ->orWhere('municipio', 'like', "%{$search}%")
                    ->orWhere('direccion', 'like', "%{$search}%")
                    ->orWhere('nit', 'like', "%{$search}%");
            });
        })
            ->where('estado', 1)
            ->orderBy('nombre')
            ->get();

        return Pdf::loadView('pdf.clientes', compact('cliente', 'search'))
            ->setPaper('letter', 'landscape')
            ->stream('clientes.pdf');
    }

    public function exportExcel(Request $request)
    {
        $search = $request->query('search');

        return Excel::download(
            new ClienteExport($search),
            'clientes.xlsx'
        );
    }
}
