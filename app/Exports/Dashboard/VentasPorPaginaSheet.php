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

class VentasPorPaginaSheet implements
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
        return 'Ventas por página';
    }

    public function collection(): Collection
    {
        $rows = collect($this->data['ventas_por_pagina'])
            ->values()
            ->map(function ($item, $index) {
                $item['no'] = $index + 1;
                return $item;
            });

        $rows->push([
            'no' => 'TOTAL',
            'nombre' => '',
            'venta' => $this->data['totales']['venta'] ?? 0,
            'envio' => $this->data['totales']['envio'] ?? 0,
            'total' => $this->data['totales']['total'] ?? 0,
        ]);

        return $rows;
    }

    public function headings(): array
    {
        return [
            'No',
            'Página',
            'Venta',
            'Envío',
            'Total',
        ];
    }

    public function map($item): array
    {
        return [
            $item['no'],
            $item['nombre'],
            (float) $item['venta'],
            (float) $item['envio'],
            (float) $item['total'],
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