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

class VentaExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithColumnFormatting
{
    protected array $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function collection(): Collection
    {
        $query = Venta::with(['cliente', 'vendedor', 'banco', 'pagos.banco', 'pagina'])
            ->orderBy('id', 'desc');

        // 1. Filtro por Fechas
        if (!empty($this->filters['fecha_inicio']) && !empty($this->filters['fecha_fin'])) {
            $query->whereBetween('created_at', [
                $this->filters['fecha_inicio'] . ' 00:00:00',
                $this->filters['fecha_fin'] . ' 23:59:59'
            ]);
        }

        // 2. Filtro por Estado
        $estado = $this->filters['estado'] ?? null;
        if ($estado === 'Emitidas') {
            $query->where('estado', 'emitida');
        } elseif ($estado === 'Anuladas') {
            $query->where('estado', 'anulada');
        } elseif ($estado === 'En Produccion') {
            $query->where('estado_produccion', 'en_produccion');
        } elseif ($estado === 'Sin Iniciar') {
            $query->where('estado_produccion', 'sin_iniciar');
        } elseif ($estado === 'Finalizadas') {
            $query->where('estado_produccion', 'finalizada');
        }

        // 3. Filtro por Búsqueda (Search)
        $search = trim($this->filters['search'] ?? '');
        if (!empty($search) && $search !== 'null') {
            $query->where(function ($sub) use ($search) {
                if (str_contains($search, '-')) {
                    [$serie, $numero] = explode('-', $search);
                    $numero = ltrim($numero, '0');
                    $sub->where('serie', 'like', "%{$serie}%")
                        ->where('numero', $numero);
                } else {
                    $sub->where('serie', 'like', "%{$search}%")
                        ->orWhere('numero', 'like', "%{$search}%");
                }

                $sub->orWhereHas('cliente', function ($c) use ($search) {
                    $c->where('nombre', 'like', "%{$search}%");
                });
                $sub->orWhereHas('vendedor', function ($v) use ($search) {
                    $v->where('name', 'like', "%{$search}%");
                });
            });
        }

        return $query->get();
    }


    public function headings(): array
    {
        return [
            'Número',
            'Cliente',
            'Página',
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
                ' (Banco: ' . ($p->banco->nombre ?? 'N/A') . ') ' .
                ' (No. dep: ' . ($p->referencia ?? 'N/A') . ') ' .
                optional($p->created_at)->format('d/m/Y');
        })->implode("\n"); // salto de línea

        return [
            $venta->numero_completo,
            $venta->cliente->nombre ?? '-',
            $venta->pagina->nombre ?? '-',
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
        $sheet->getStyle('K')->getAlignment()->setWrapText(true);
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
            'E' => '"Q" #,##0.00',
            'F' => '"Q" #,##0.00',
            'G' => '"Q" #,##0.00',
            'H' => '"Q" #,##0.00',
            'I' => '"Q" #,##0.00',
            'J' => '"Q" #,##0.00',
        ];
    }
}
