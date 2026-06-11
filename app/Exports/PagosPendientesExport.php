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
    protected $desde;
    protected $hasta;
    protected $estadoCredito;

    public function __construct($search = null, $desde = null, $hasta = null, $estadoCredito = 'vigentes')
    {
        $this->search = ($search === "null" || $search === "") ? null : $search;
        $this->desde = $desde;
        $this->hasta = $hasta;
        $this->estadoCredito = $estadoCredito ?: 'vigentes';
    }
    public function collection()
    {
        return Venta::with(['pagos.banco', 'cliente', 'vendedor', 'pagina', 'detalles'])
            ->withSum('pagos as total_pagado', 'monto')
            ->where('estado', 'emitida')
            ->when($this->desde, fn ($q) => $q->whereDate('created_at', '>=', $this->desde))
            ->when($this->hasta, fn ($q) => $q->whereDate('created_at', '<=', $this->hasta))
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
                        )
                        ->orWhereHas(
                            'pagina',
                            fn($p) =>
                            $p->where('nombre', 'like', "%{$this->search}%")
                        )
                        ->orWhereHas(
                            'detalles',
                            fn($d) =>
                            $d->where('nombre_logo', 'like', "%{$this->search}%")
                        );
                });
            })
            ->get()
            ->filter(function ($v) {
                return $this->filtrarPorEstadoCredito($v);
            })
            ->values();
    }

    private function filtrarPorEstadoCredito($venta): bool
    {
        $saldoPendiente = (float) $venta->saldo_pendiente;
        $creditoInicial = (float) ($venta->pendiente_pagar ?? 0);

        return match ($this->estadoCredito) {
            'generados' => $creditoInicial > 0,
            'pagados' => $creditoInicial > 0 && $saldoPendiente <= 0,
            'todos' => $creditoInicial > 0 || $saldoPendiente > 0,
            default => $saldoPendiente > 0,
        };
    }

    private function negocioLogotipo($venta): string
    {
        return $venta->detalles
            ->pluck('nombre_logo')
            ->filter()
            ->map(fn ($logo) => trim($logo))
            ->filter()
            ->unique()
            ->values()
            ->implode("\n") ?: '-';
    }

    public function headings(): array
    {
        return [
            'Número',
            'Cliente / negocio',
            'Negocio / logotipo',
            'Página',
            'Asesor',
            'Subtotal',
            'Descuento',
            'Costo Envío',
            'Total',
            'Total Pagado',
            'Pendiente',
            'Historial de Pagos',
            'Notas',
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

        $notas = $venta->pagos
            ->pluck('nota')
            ->filter()
            ->implode("\n");

        return [
            $venta->numero_completo,
            $venta->cliente->nombre ?? '-',
            $this->negocioLogotipo($venta),
            $venta->pagina->nombre ?? '-',
            $venta->vendedor->name ?? '-',

            $venta->subtotal,
            $venta->descuento,
            $venta->costo_envio,
            $venta->total,

            $pagado,
            $pendiente,
            $historial,
            $notas,

            ucfirst($venta->estado),
            ucfirst(str_replace('_', ' ', $venta->estado_produccion)),

            optional($venta->created_at)->format('d/m/Y H:i'),
            optional($venta->fecha_entrega)->format('d/m/Y'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('C:L')->getAlignment()->setWrapText(true);
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
            'F' => '"Q" #,##0.00',
            'G' => '"Q" #,##0.00',
            'H' => '"Q" #,##0.00',
            'I' => '"Q" #,##0.00',
            'J' => '"Q" #,##0.00',
            'K' => '"Q" #,##0.00',
        ];
    }
}
