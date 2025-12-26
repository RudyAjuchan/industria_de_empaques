<?php

namespace App\Exports;

use Spatie\Permission\Models\Role;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    ShouldAutoSize
};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RoleExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected ?string $search;

    public function __construct($search = null)
    {
        $this->search = trim((string) $search);
    }

    public function collection(): Collection
    {
        return Role::with('permissions')
            ->when($this->search !== '', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            })
            ->orderBy('name')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Rol',
            'Cantidad de permisos',
            'Permisos'
        ];
    }

    public function map($role): array
    {
        return [
            $role->name,
            $role->permissions->count(),
            $role->permissions->pluck('name')->join(', ')
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
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
