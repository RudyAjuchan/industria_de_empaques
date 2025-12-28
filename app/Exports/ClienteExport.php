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
    protected string|null $search;

    public function __construct($search = null)
    {
        $this->search = ($search === "null") ? null : $search;
    }

    public function collection(): Collection
    {
        return Cliente::when($this->search !== '', function ($q) {
            $q->where(function ($sub) {
                $sub->where('nombre', 'like', "%{$this->search}%")
                    ->orWhere('telefono', 'like', "%{$this->search}%")
                    ->orWhere('dpi', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%")
                    ->orWhere('departamento', 'like', "%{$this->search}%")
                    ->orWhere('municipio', 'like', "%{$this->search}%")
                    ->orWhere('direccion', 'like', "%{$this->search}%")
                    ->orWhere('nit', 'like', "%{$this->search}%");
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
            'Teléfono',
            'DPI',
            'Correo',
            'Departamento',
            'Municipio',
            'Dirección',
            'Nit',
            'Creado',
            'Actualizado',
        ];
    }

    public function map($cliente): array
    {
        return [
            $cliente->nombre,
            $cliente->telefono,
            $cliente->dpi,
            $cliente->email,
            $cliente->departamento,
            $cliente->municipio,
            $cliente->direccion,
            $cliente->nit,
            $cliente->created_at,
            $cliente->updated_at,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [ // encabezado
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => 'solid',
                    'color' => ['rgb' => '167160'],
                ],
                'font' => [
                    'color' => ['rgb' => 'FFFFFF'],
                ],
            ],
        ];
    }
}
