<?php

namespace App\Http\Controllers;

use App\Exports\ClienteExport;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->query('search', ''));

        $clientes = Cliente::with([
            'emails',
            'telefonos',
            'municipio.departamento'
        ])
            ->where('estado', 1)
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {

                    // Cliente
                    $sub->where('nombre', 'like', "%{$search}%")
                        ->orWhere('dpi', 'like', "%{$search}%")
                        ->orWhere('nit', 'like', "%{$search}%")
                        ->orWhere('direccion', 'like', "%{$search}%")
                        ->orWhere('estado_pais', 'like', "%{$search}%")
                        ->orWhere('ciudad_pais', 'like', "%{$search}%");

                    // Emails
                    $sub->orWhereHas('emails', function ($q) use ($search) {
                        $q->where('email', 'like', "%{$search}%");
                    });

                    // Teléfonos
                    $sub->orWhereHas('telefonos', function ($q) use ($search) {
                        $q->where('telefono_codigo_pais', 'like', "%{$search}%")
                            ->orWhere('telefono_numero', 'like', "%{$search}%");
                    });

                    // Municipio / Departamento
                    $sub->orWhereHas('municipio', function ($q) use ($search) {
                        $q->where('nombre', 'like', "%{$search}%")
                            ->orWhereHas('departamento', function ($d) use ($search) {
                                $d->where('nombre', 'like', "%{$search}%");
                            });
                    });
                });
            })
            ->orderBy('nombre')
            ->get();

        return response()->json($clientes);
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'genero' => 'required|string|max:20',

            'dpi' => 'nullable|digits:13',
            'municipios_id' => 'nullable|exists:municipios,id',

            'pais' => 'nullable|string',
            'estado_pais' => 'nullable|string|max:255',
            'ciudad_pais' => 'nullable|string|max:255',
            'direccion' => 'nullable|string',
            'nit' => 'nullable|string|max:50',

            'emails' => 'nullable|array',
            'emails.*' => 'required|email|max:255',

            'telefonos' => 'nullable|array',
            'telefonos.*.telefono_pais' => 'nullable|string',
            'telefonos.*.telefono_pais_nombre' => 'nullable|string',
            'telefonos.*.telefono_codigo_pais' => 'nullable|string|max:5',
            'telefonos.*.telefono_numero' => 'nullable|digits_between:8,20',
        ]);

        $cliente = Cliente::create($data);

        // Emails
        if (!empty($data['emails'])) {
            foreach ($data['emails'] as $email) {
                $cliente->emails()->create([
                    'email' => $email
                ]);
            }
        }

        // Teléfonos
        if (!empty($data['telefonos'])) {
            foreach ($data['telefonos'] as $tel) {
                $cliente->telefonos()->create($tel);
            }
        }

        return response()->json($cliente, 201);
    }

    public function show(Cliente $cliente)
    {
        return $cliente->load([
            'emails',
            'telefonos',
            'municipio.departamento',
        ]);
    }

    public function update(Request $request, Cliente $cliente)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'genero' => 'required|string|max:20',

            'dpi' => 'nullable|digits:13',
            'municipios_id' => 'nullable|exists:municipios,id',

            'pais' => 'nullable|string',
            'estado_pais' => 'nullable|string|max:255',
            'ciudad_pais' => 'nullable|string|max:255',
            'direccion' => 'nullable|string',
            'nit' => 'nullable|string|max:50',

            'emails' => 'nullable|array',
            'emails.*' => 'required|email|max:255',

            'telefonos' => 'nullable|array',
            'telefonos.*.telefono_pais' => 'nullable|string',
            'telefonos.*.telefono_pais_nombre' => 'nullable|string',
            'telefonos.*.telefono_codigo_pais' => 'nullable|string|max:5',
            'telefonos.*.telefono_numero' => 'nullable|digits_between:8,20',
        ]);

        $cliente->update($data);

        $cliente->emails()->delete();
        foreach ($data['emails'] ?? [] as $email) {
            $cliente->emails()->create([
                'email' => $email,
            ]);
        }

        $cliente->telefonos()->delete();
        foreach ($data['telefonos'] ?? [] as $tel) {
            $cliente->telefonos()->create($tel);
        }

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
        $search = ($search === 'null' || $search === '') ? null : $search;

        $clientes = Cliente::with([
            'emails',
            'telefonos',
            'municipio.departamento'
        ])
            ->where('estado', 1)
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {

                    // Cliente
                    $sub->where('nombre', 'like', "%{$search}%")
                        ->orWhere('dpi', 'like', "%{$search}%")
                        ->orWhere('nit', 'like', "%{$search}%")
                        ->orWhere('direccion', 'like', "%{$search}%")
                        ->orWhere('estado_pais', 'like', "%{$search}%")
                        ->orWhere('ciudad_pais', 'like', "%{$search}%");

                    // Emails
                    $sub->orWhereHas('emails', function ($q) use ($search) {
                        $q->where('email', 'like', "%{$search}%");
                    });

                    // Teléfonos
                    $sub->orWhereHas('telefonos', function ($q) use ($search) {
                        $q->where('telefono_codigo_pais', 'like', "%{$search}%")
                            ->orWhere('telefono_numero', 'like', "%{$search}%");
                    });

                    // Municipio / Departamento
                    $sub->orWhereHas('municipio', function ($q) use ($search) {
                        $q->where('nombre', 'like', "%{$search}%")
                            ->orWhereHas('departamento', function ($d) use ($search) {
                                $d->where('nombre', 'like', "%{$search}%");
                            });
                    });
                });
            })
            ->orderBy('nombre')
            ->get();

        return Pdf::loadView('pdf.clientes', [
            'clientes' => $clientes,
            'search' => $search
        ])
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

    public function search(Request $request)
    {
        $q = $request->get('q');

        if (!$q || strlen($q) < 2) {
            return [];
        }

        return Cliente::where('nombre', 'like', "%{$q}%")
            ->orWhere('nit', 'like', "%{$q}%")
            ->limit(15)
            ->get(['id', 'nombre', 'nit']);
    }

}
