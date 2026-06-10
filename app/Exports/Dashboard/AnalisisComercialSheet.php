<?php

namespace App\Exports\Dashboard;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AnalisisComercialSheet implements FromArray, WithStyles, ShouldAutoSize, WithTitle
{
    public function __construct(private array $data)
    {
    }

    public function title(): string
    {
        return 'Análisis comercial';
    }

    public function array(): array
    {
        $rows = [];

        $rows[] = ['Tamaños más comercializados'];
        $rows[] = ['No.', 'Tamaño', 'Unidades', 'Ventas'];
        foreach ($this->data['tamanos'] ?? [] as $item) {
            $rows[] = [$item['no'], $item['tamano'], (int) $item['unidades'], (int) $item['ventas']];
        }

        $rows[] = [];
        $rows[] = ['Compras por género'];
        $rows[] = ['No.', 'Género', 'Ventas', 'Monto'];
        foreach ($this->data['generos'] ?? [] as $item) {
            $rows[] = [$item['no'], $item['genero'], (int) $item['ventas'], (float) $item['total']];
        }

        $rows[] = [];
        $rows[] = ['Ventas por departamento'];
        $rows[] = ['No.', 'Departamento', 'Ventas', 'Monto'];
        foreach ($this->data['departamentos'] ?? [] as $item) {
            $rows[] = [$item['no'], $item['departamento'], (int) $item['ventas'], (float) $item['total']];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        $styles = [];

        foreach ($sheet->toArray() as $index => $row) {
            $rowNumber = $index + 1;
            $firstCell = $row[0] ?? '';

            if (in_array($firstCell, [
                'Tamaños más comercializados',
                'Compras por género',
                'Ventas por departamento',
            ], true)) {
                $styles[$rowNumber] = [
                    'font' => ['bold' => true, 'size' => 13],
                ];
            }

            if ($firstCell === 'No.') {
                $styles[$rowNumber] = [
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['rgb' => '167160'],
                    ],
                ];
            }
        }

        return $styles;
    }
}
