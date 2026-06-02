<?php

namespace App\Exports;

use App\Models\Venta;
use Illuminate\Support\Collection;

use Maatwebsite\Excel\Concerns\{
    FromCollection,
    WithHeadings,
    WithStyles,
    WithMapping,
    ShouldAutoSize,
    WithColumnFormatting
};

use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PagosPendientesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithColumnFormatting
{
    /**
     * @return \Illuminate\Support\Collection
     */
    protected $search;

    public function __construct($search = null)
    {
        $this->search = ($search === "null" || $search === "") ? null : $search;
    }
    public function collection()
    {
        return Venta::with(['pagos.banco', 'cliente', 'vendedor'])
            ->withSum('pagos as total_pagado', 'monto')
            ->where('estado', 'emitida')
            ->when($this->search, function ($q) {
                $q->where(function ($q2) {
                    $q2->where('numero', 'like', "%{$this->search}%")
                        ->orWhereHas(
                            'cliente',
                            fn($c) =>
                            $c->where('nombre', 'like', "%{$this->search}%")
                        )
                        ->orWhereHas(
                            'vendedor',
                            fn($v) =>
                            $v->where('name', 'like', "%{$this->search}%")
                        );
                });
            })
            ->get()
            ->filter(function ($v) {
                return $v->saldo_pendiente > 0;
            })
            ->values();
    }

    public function headings(): array
    {
        return [
            'Número',
            'Cliente',
            'Vendedor',
            'Subtotal',
            'Descuento',
            'Costo Envío',
            'Total',
            'Total Pagado',
            'Pendiente',
            'Historial de Pagos',
            'Estado',
            'Estado Producción',
            'Fecha Emisión',
            'Fecha Entrega',
        ];
    }

    public function map($venta): array
    {
        $pagado = $venta->pagos->sum('monto');
        $pendiente = $venta->total - $pagado;

        // HISTORIAL FORMATEADO
        $historial = $venta->pagos->map(function ($p) {
            return 'Q' . number_format($p->monto, 2) .
                ' (' . ($p->metodo_pago ?? 'N/A') . ') ' .
                optional($p->created_at)->format('d/m/Y') . ' Banco: '. $p->banco?->nombre . ' No. dep: '.$p->referencia;
        })->implode("\n"); // salto de línea

        return [
            $venta->numero_completo,
            $venta->cliente->nombre ?? '-',
            $venta->vendedor->name ?? '-',

            $venta->subtotal,
            $venta->descuento,
            $venta->costo_envio,
            $venta->total,

            $pagado,
            $pendiente,
            $historial,

            ucfirst($venta->estado),
            ucfirst(str_replace('_', ' ', $venta->estado_produccion)),

            optional($venta->created_at)->format('d/m/Y H:i'),
            optional($venta->fecha_entrega)->format('d/m/Y'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('J')->getAlignment()->setWrapText(true);
        $sheet->getDefaultRowDimension()->setRowHeight(-1);
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => 'solid',
                    'color' => ['rgb' => '167160'],
                ],
            ],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'D' => '"Q" #,##0.00',
            'E' => '"Q" #,##0.00',
            'F' => '"Q" #,##0.00',
            'G' => '"Q" #,##0.00',
            'H' => '"Q" #,##0.00',
            'I' => '"Q" #,##0.00',
        ];
    }
}
