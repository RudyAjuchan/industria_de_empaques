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

class ProduccionSheet implements
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
        return 'Producción';
    }

    public function collection(): Collection
    {
        return collect($this->data['por_estado'])
            ->values()
            ->map(function ($item, $index) {
                $item['no'] = $index + 1;
                return $item;
            });
    }

    public function headings(): array
    {
        return [
            'No.',
            'Estado',
            'Pedido',
            'Producción',
            'Extras',
            'Desechadas',
            'Pendiente',
        ];
    }

    public function map($item): array
    {
        return [
            $item['no'],
            $item['estado'],
            (int) $item['pedido'],
            (int) $item['finalizadas'],
            (int) $item['extras'],
            (int) $item['desechadas'],
            (int) $item['pendiente'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();

        return [

            // Header
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

            // Totales fila final
            $lastRow => [
                'font' => [
                    'bold' => true
                ]
            ]
        ];
    }
}
