<?php

namespace App\Exports\Dashboard;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductosPorPaginaSheet implements FromArray, WithStyles, ShouldAutoSize, WithTitle
{
    public function __construct(private array $data)
    {
    }

    public function title(): string
    {
        return 'Control ventas';
    }

    public function array(): array
    {
        $rows = [
            ['CONTROL DE VENTAS - INDUSTRIA DE EMPAQUES S.A.'],
            ['Página', 'Producto', 'Tipo', 'Unidades', 'Ventas', 'Total'],
        ];

        foreach ($this->data as $pagina) {
            $rows[] = [
                $pagina['nombre'],
                'TOTAL PÁGINA',
                '',
                (int) ($pagina['unidades'] ?? 0),
                (int) ($pagina['ventas'] ?? 0),
                (float) ($pagina['total'] ?? 0),
            ];

            foreach ($pagina['productos'] ?? [] as $producto) {
                $rows[] = [
                    $pagina['nombre'],
                    $producto['nombre'],
                    $producto['tipo'],
                    (int) ($producto['unidades'] ?? 0),
                    (int) ($producto['ventas'] ?? 0),
                    (float) ($producto['total'] ?? 0),
                ];
            }

            $rows[] = [];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        $styles = [
            1 => [
                'font' => ['bold' => true, 'size' => 13],
            ],
            2 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['rgb' => '167160'],
                ],
            ],
        ];

        foreach ($sheet->toArray() as $index => $row) {
            $rowNumber = $index + 1;

            if (($row[1] ?? '') === 'TOTAL PÁGINA') {
                $styles[$rowNumber] = [
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['rgb' => 'E2F0ED'],
                    ],
                ];
            }
        }

        return $styles;
    }
}
