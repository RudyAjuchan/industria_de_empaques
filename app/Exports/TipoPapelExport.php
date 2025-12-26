<?php

namespace App\Exports;

use App\Models\TipoPapel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{
    FromCollection,
    WithHeadings,
    WithStyles,
    WithMapping,
    ShouldAutoSize
};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TipoPapelExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected string|null $search;

    public function __construct($search = null)
    {
        $this->search = $search;
    }

    public function collection(): Collection
    {
        return TipoPapel::when($this->search, function ($q) {
                $q->where('nombre', 'like', "%{$this->search}%");
            })
            ->orderBy('nombre')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Nombre',
            'Creado',
            'Actualizado',
        ];
    }

    public function map($tipo_papel): array
    {
        return [
            $tipo_papel->nombre,
            $tipo_papel->created_at,
            $tipo_papel->updated_at,
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
