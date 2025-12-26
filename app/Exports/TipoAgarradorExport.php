<?php

namespace App\Exports;

use App\Models\TipoAgarrador;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{
    FromCollection,
    WithHeadings,
    WithStyles,
    WithMapping,
    ShouldAutoSize
};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TipoAgarradorExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected string|null $search;

    public function __construct($search = null)
    {
        $this->search = ($search === "null") ? null : $search;
    }

    public function collection(): Collection
    {
        return TipoAgarrador::when($this->search, function ($q) {
                $q->where('nombre', 'like', "%{$this->search}%");
            })
            ->where('estado', 1)
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

    public function map($tipo_agarrador): array
    {
        return [
            $tipo_agarrador->nombre,
            $tipo_agarrador->created_at,
            $tipo_agarrador->updated_at,
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
