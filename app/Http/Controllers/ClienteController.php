<?php

namespace App\Http\Controllers;

use App\Exports\ClienteExport;
use App\Models\Cliente;
use App\Models\Cliente_email;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Password;
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

            'emails' => 'required|array|min:1',
            'emails.*' => $this->emailRules(),

            'telefonos' => 'required|array',
            'telefonos.*.telefono_pais' => 'nullable|string',
            'telefonos.*.telefono_pais_nombre' => 'nullable|string',
            'telefonos.*.telefono_codigo_pais' => 'nullable|string|max:5',
            'telefonos.*.telefono_numero' => $this->telefonoRules($request),
        ], $this->validationMessages());

        $data['email'] = $data['emails'][0];

        $cliente = Cliente::create(Arr::except($data, ['emails', 'telefonos']));

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

        $cliente = Cliente::with([
            'emails',
            'telefonos',
            'municipio.departamento'
        ])->find($cliente->id);

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

            'emails' => 'required|array|min:1',
            'emails.*' => $this->emailRules($cliente),

            'telefonos' => 'required|array',
            'telefonos.*.telefono_pais' => 'nullable|string',
            'telefonos.*.telefono_pais_nombre' => 'nullable|string',
            'telefonos.*.telefono_codigo_pais' => 'nullable|string|max:5',
            'telefonos.*.telefono_numero' => $this->telefonoRules($request),
        ], $this->validationMessages());

        $data['email'] = $data['emails'][0];

        $cliente->update(Arr::except($data, ['emails', 'telefonos']));

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

    public function sendPasswordReset(Cliente $cliente)
    {
        if (!$cliente->email) {
            return response()->json([
                'message' => 'El cliente no tiene correo principal.'
            ], 422);
        }

        if (!$cliente->estado) {
            return response()->json([
                'message' => 'No se puede enviar el restablecimiento a un cliente inactivo.'
            ], 422);
        }

        $status = Password::broker('clientes')->sendResetLink([
            'email' => $cliente->email,
        ]);

        if ($status !== Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => __($status)
            ], 422);
        }

        return response()->json([
            'message' => 'Correo de restablecimiento enviado.'
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
        $q = trim($request->get('q'));

        if (!$q || strlen($q) < 2) {
            return [];
        }

        return Cliente::with([
            'emails',
            'telefonos',
            'municipio.departamento'
        ])
        ->where(function ($query) use ($q) {
            $query->where('nombre', 'like', "%{$q}%")
                ->orWhere('nit', 'like', "%{$q}%");

            // Buscar por ID solo si es numérico
            if (is_numeric($q)) {
                $query->orWhere('id', (int)$q);
            }
        })
        ->limit(15)
        ->get([
            'id',
            'nombre',
            'nit',
            'municipios_id',
            'direccion',
            'ciudad_pais',
            'estado_pais'
        ]);
    }

    private function emailRules(?Cliente $cliente = null): array
    {
        return [
            'required',
            'email',
            'max:255',
            'distinct',
            'regex:/^[^\s@]+@[^\s@]+\.[^\s@]+$/',
            function (string $attribute, mixed $value, \Closure $fail) use ($cliente) {
                $email = mb_strtolower(trim((string) $value));

                $existsInClientes = Cliente::query()
                    ->whereRaw('LOWER(email) = ?', [$email])
                    ->when($cliente, fn ($query) => $query->whereKeyNot($cliente->id))
                    ->exists();

                $existsInClienteEmails = Cliente_email::query()
                    ->whereRaw('LOWER(email) = ?', [$email])
                    ->when($cliente, fn ($query) => $query->where('clientes_id', '!=', $cliente->id))
                    ->exists();

                if ($existsInClientes || $existsInClienteEmails) {
                    $fail('Ya existe un cliente registrado con este correo.');
                }
            },
        ];
    }

    private function telefonoRules(Request $request): array
    {
        return [
            'nullable',
            'digits_between:8,20',
            function (string $attribute, mixed $value, \Closure $fail) use ($request) {
                if ($value === null || $value === '') {
                    return;
                }

                preg_match('/telefonos\.(\d+)\.telefono_numero/', $attribute, $matches);
                $index = $matches[1] ?? null;
                $telefono = $index !== null ? $request->input("telefonos.$index", []) : [];

                $pais = mb_strtolower((string) ($telefono['telefono_pais'] ?? ''));
                $codigo = preg_replace('/\s+/', '', (string) ($telefono['telefono_codigo_pais'] ?? ''));

                if ($pais === 'guatemala' || $codigo === '+502' || $codigo === '502') {
                    if (!preg_match('/^\d{8}$/', (string) $value)) {
                        $fail('El teléfono de Guatemala debe tener exactamente 8 dígitos.');
                    }
                }
            },
        ];
    }

    private function validationMessages(): array
    {
        return [
            'emails.required' => 'Debes agregar al menos un correo.',
            'emails.min' => 'Debes agregar al menos un correo.',
            'emails.*.required' => 'El correo es obligatorio.',
            'emails.*.email' => 'Ingresa un correo válido.',
            'emails.*.regex' => 'Ingresa un correo válido con dominio completo, por ejemplo cliente@dominio.com.',
            'emails.*.distinct' => 'No puedes agregar el mismo correo más de una vez.',
            'telefonos.*.digits_between' => 'El teléfono debe contener solo números y tener entre 8 y 20 dígitos.',
        ];
    }

}
