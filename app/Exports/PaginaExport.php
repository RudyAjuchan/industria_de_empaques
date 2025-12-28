<?php

namespace App\Exports;

use App\Models\Pagina;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{
    FromCollection,
    WithHeadings,
    WithStyles,
    WithMapping,
    ShouldAutoSize
};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PaginaExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected string|null $search;

    public function __construct($search = null)
    {
        $this->search = ($search === "null") ? null : $search;
    }

    public function collection(): Collection
    {
        return Pagina::when($this->search, function ($q) {
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

    public function map($pagina): array
    {
        return [
            $pagina->nombre,
            $pagina->created_at,
            $pagina->updated_at,
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
