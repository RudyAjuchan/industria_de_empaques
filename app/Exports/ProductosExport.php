<?php

namespace App\Exports;

use App\Models\Producto;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    ShouldAutoSize
};

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductosExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected string|null $search;

    public function __construct($search = null)
    {
        $search = is_string($search) ? trim($search) : $search;
        $this->search = ($search === 'null' || $search === '') ? null : $search;
    }

    public function collection(): Collection
    {
        return Producto::query()
            ->with('paginas')
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('nombre', 'like', "%{$this->search}%")
                        ->orWhere('sku', 'like', "%{$this->search}%")
                        ->orWhere('tipo', 'like', "%{$this->search}%");
                });
            })
            ->where('estado', 1)
            ->orderBy('id')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Código',
            'SKU',
            'Nombre',
            'Alto',
            'Ancho',
            'Fuelle',
            'Tipo',
            'Página',
            'Creado',
            'Actualizado',
        ];
    }

    public function map($producto): array
    {
        return [
            $producto->id,
            $producto->sku,
            $producto->nombre,
            $producto->alto,
            $producto->ancho,
            $producto->fuelle,
            $producto->tipo,
            optional($producto->paginas)->nombre,
            $producto->created_at?->format('d/m/Y H:i'),
            $producto->updated_at?->format('d/m/Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [ // fila de encabezados
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => 'solid',
                    'color' => ['rgb' => '167160'], // verde oscuro
                ],
            ],
        ];
    }
}
