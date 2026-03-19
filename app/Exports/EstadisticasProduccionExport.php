<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class EstadisticasProduccionExport implements FromArray, WithStrictNullComparison
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $rows = [];

        // TÍTULO
        $rows[] = ['REPORTE DE PRODUCCIÓN'];
        $rows[] = [];

        // TOTALES
        $rows[] = ['Totales'];
        $rows[] = ['Pedido', (int) ($this->data['totales']['pedido'] ?: 0)];
        $rows[] = ['Producción', (int) ($this->data['totales']['finalizadas'] ?: 0)];
        $rows[] = ['Extras', (int) ($this->data['totales']['extras'] ?: 0)];
        $rows[] = ['Desechadas', (int) ($this->data['totales']['desechadas'] ?: 0)];
        $rows[] = ['Pendiente', (int) ($this->data['totales']['pendiente'] ?: 0)];
        $rows[] = [];

        // ENCABEZADOS TABLA
        $rows[] = [
            'Estado',
            'Pedido',
            'Producción',
            'Extras',
            'Desechadas',
            'Pendiente'
        ];

        // DATOS
        foreach ($this->data['por_estado'] as $item) {
            $rows[] = [
                $item['estado'],
                (int) ($item['pedido'] ?: 0),
                (int) ($item['finalizadas'] ?: 0),
                (int) ($item['extras'] ?: 0),
                (int) ($item['desechadas'] ?: 0),
                (int) ($item['pendiente'] ?: 0),
            ];
        }

        return $rows;
    }
}