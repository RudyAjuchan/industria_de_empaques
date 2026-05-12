<?php

namespace App\Exports\Dashboard;

use Illuminate\Support\Collection;

use Maatwebsite\Excel\Concerns\{
    FromCollection,
    WithHeadings,
    WithStyles,
    WithMapping,
    ShouldAutoSize,
    WithTitle
};

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TiposProductoSheet implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    ShouldAutoSize,
    WithTitle
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function title(): string
    {
        return 'Tipos producto';
    }

    public function collection(): Collection
    {
        $rows = collect($this->data['tipos_producto'])
        ->values()
            ->map(function ($item, $index) {
                $item['no'] = $index + 1;
                return $item;
            });

        $rows->push([
            'no' => 'TOTAL',
            'tipo' => '',
            'unidades' => $this->data['totales']['unidades'] ?? 0,
            'ventas' => $this->data['totales']['ventas'] ?? 0,
        ]);

        return $rows;
    }

    public function headings(): array
    {
        return [
            'No.',
            'Tipo',
            'Unidades',
            'Ventas',
        ];
    }

    public function map($item): array
    {
        return [
            $item['no'],
            $item['tipo'],
            (int) $item['unidades'],
            (int) $item['ventas'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();

        return [

            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],

                'fill' => [
                    'fillType' => 'solid',
                    'color' => ['rgb' => '167160']
                ],
            ],

            $lastRow => [
                'font' => [
                    'bold' => true
                ]
            ]
        ];
    }
}