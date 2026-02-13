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
    protected ?string $search;

    public function __construct($search = null)
    {
        $this->search = ($search === "null" || $search === '') ? null : $search;
    }

    public function collection(): Collection
    {
        return Venta::with(['cliente', 'vendedor'])
            ->when($this->search, function ($q) {

                $search = trim($this->search);

                $q->where(function ($sub) use ($search) {

                    if (str_contains($search, '-')) {

                        [$serie, $numero] = explode('-', $search);
                        $numero = ltrim($numero, '0');

                        $sub->where(function ($q2) use ($serie, $numero) {
                            $q2->where('serie', 'like', "%{$serie}%")
                                ->where('numero', $numero);
                        });
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
            })
            ->orderByDesc('created_at')
            ->get();
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
            'Estado',
            'Estado Producción',
            'Fecha Emisión',
            'Fecha Entrega',
        ];
    }

    public function map($venta): array
    {
        return [
            $venta->numero_completo,
            $venta->cliente->nombre ?? '-',
            $venta->vendedor->name ?? '-',

            $venta->subtotal,
            $venta->descuento,
            $venta->costo_envio,
            $venta->total,

            ucfirst($venta->estado),
            ucfirst(str_replace('_', ' ', $venta->estado_produccion)),

            optional($venta->created_at)->format('d/m/Y H:i'),
            optional($venta->fecha_entrega)->format('d/m/Y'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
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
        ];
    }
}
