<?php

namespace App\Exports;

use App\Models\Cliente;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{
    FromCollection,
    WithHeadings,
    WithStyles,
    WithMapping,
    ShouldAutoSize
};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ClienteExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected ?string $search;

    public function __construct($search = null)
    {
        $this->search = ($search === "null" || $search === '') ? null : $search;
    }

    public function collection(): Collection
    {
        return Cliente::with([
            'emails',
            'telefonos',
            'municipio.departamento'
        ])
            ->when($this->search, function ($q) {
                $search = $this->search;

                $q->where(function ($sub) use ($search) {
                    $sub->where('nombre', 'like', "%{$search}%")
                        ->orWhere('dpi', 'like', "%{$search}%")
                        ->orWhere('nit', 'like', "%{$search}%")
                        ->orWhere('direccion', 'like', "%{$search}%")

                        // Emails
                        ->orWhereHas('emails', function ($e) use ($search) {
                            $e->where('email', 'like', "%{$search}%");
                        })

                        // Teléfonos
                        ->orWhereHas('telefonos', function ($t) use ($search) {
                            $t->where('telefono_numero', 'like', "%{$search}%")
                                ->orWhere('telefono_codigo_pais', 'like', "%{$search}%");
                        })

                        // Municipio / Departamento
                        ->orWhereHas('municipio', function ($m) use ($search) {
                            $m->where('nombre', 'like', "%{$search}%")
                                ->orWhereHas('departamento', function ($d) use ($search) {
                                    $d->where('nombre', 'like', "%{$search}%");
                                });
                        });
                });
            })
            ->where('estado', 1)
            ->orderBy('nombre')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Nombre',
            'Correos',
            'Teléfonos',
            'DPI',
            'Ubicación',
            'Dirección',
            'NIT',
            'Creado',
            'Actualizado',
        ];
    }

    public function map($cliente): array
    {
        return [
            $cliente->nombre,

            // Correos
            $cliente->emails->pluck('email')->implode(', '),

            // Teléfonos
            $cliente->telefonos
                ->map(fn($t) => "{$t->telefono_codigo_pais} {$t->telefono_numero}")
                ->implode(', '),

            $cliente->dpi,

            // Ubicación
            $cliente->municipio
                ? "{$cliente->municipio->nombre}, {$cliente->municipio->departamento->nombre}"
                : "{$cliente->ciudad_pais} - {$cliente->estado_pais}",

            $cliente->direccion,
            $cliente->nit,
            optional($cliente->created_at)->format('d/m/Y H:i'),
            optional($cliente->updated_at)->format('d/m/Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => 'solid',
                    'color' => ['rgb' => '167160'],
                ],
            ],
        ];
    }
}
