<?php

namespace App\Exports;

use App\Models\DetalleVenta;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class VentasContabilidadExport implements FromCollection, WithHeadings, WithStyles, WithEvents
{
    protected $fechaInicio;
    protected $fechaFin;

    public function __construct($fechaInicio = null, $fechaFin = null)
    {
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
    }
    
    public function collection()
    {
        $query = DetalleVenta::with([
            'venta.cliente.municipio.departamento',
            'venta.vendedor',
            'producto',
            'tipoPapel',
            'tipoAgarrador'
        ])
        ->whereHas('venta', function ($q) {
            $q->where('estado', 'emitida');

            if ($this->fechaInicio) {
                $q->whereDate('created_at', '>=', $this->fechaInicio);
            }

            if ($this->fechaFin) {
                $q->whereDate('created_at', '<=', $this->fechaFin);
            }
        });

        $detalles = $query->get();

        return $detalles->map(function ($d) {

            $venta = $d->venta;
            $cliente = $venta->cliente;
            $producto = $d->producto;
            $documento = $venta->serie . '-' . str_pad($venta->numero, 6, '0', STR_PAD_LEFT);
            return [

                // VENTA
                $venta->id,
                optional($venta->created_at)->format('Y-m-d'),
                $venta->serie,
                $documento,
                $venta->estado,
                $venta->serie === 'WEB' ? 'Ecommerce' : 'Interno',

                // CLIENTE
                $cliente->nombre,
                $cliente->nit ?? '',
                $cliente->email ?? '',
                optional($cliente->telefonos->first())->telefono_numero ?? '',
                optional($cliente->municipio->departamento)->nombre ?? '',
                optional($cliente->municipio)->nombre ?? '',
                $cliente->direccion ?? '',

                // ASESOR
                optional($venta->vendedor)->name ?? '',

                // PRODUCTO
                $producto->nombre,
                $producto->tipo_producto,

                $producto->tipo_producto === 'personalizado' ? $producto->alto : '',
                $producto->tipo_producto === 'personalizado' ? $producto->ancho : '',
                $producto->tipo_producto === 'personalizado' ? $producto->fuelle : '',

                $producto->tipo_producto === 'personalizado'
                    ? optional($d->tipoPapel)->nombre
                    : '',

                $producto->tipo_producto === 'personalizado'
                    ? optional($d->tipoAgarrador)->nombre
                    : '',

                $producto->tipo_producto === 'personalizado'
                    ? $d->color_agarrador
                    : '',

                $producto->tipo_producto === 'personalizado'
                    ? $d->detalle_impresion
                    : '',

                // DETALLE
                $d->cantidad,
                $d->precio,
                $d->total,

                // TOTALES VENTA
                $venta->subtotal,
                $venta->descuento,
                $venta->costo_envio,
                $venta->total,

                // PAGO
                $venta->tipo_pago,
                $venta->no_deposito ?? '',
                optional($venta->banco)->nombre ?? '',

                // PROMOCIÓN
                $d->promocion_aplicada['nombre'] ?? '',
                $d->promocion_aplicada['tipo'] ?? '',
                $d->promocion_aplicada['valor'] ?? '',
            ];
        });
    }

    public function headings(): array
    {
        return [
            [
                'VENTA', '', '', '', '', '',               // A1:F1 (6)
                'CLIENTE', '', '', '', '', '', '',         // G1:M1 (7) <-- Aumentado
                'ASESOR',                                  // N1 (1)
                'PRODUCTO', '', '', '', '', '', '', '', '', // O1:W1 (9)
                'DETALLE', '', '',                         // X1:Z1 (3)
                'TOTALES', '', '', '',                     // AA1:AD1 (4)
                'PAGO', '', '',                            // AE1:AG1 (3)
                'PROMOCIÓN', '', ''                        // AH1:AJ1 (3)
            ],
            [
                'ID Venta', 'Fecha', 'Serie', 'Número', 'Estado', 'Origen',
                'Cliente', 'NIT', 'Email', 'Teléfono', 'Departamento', 'Municipio', 'Dirección', // CLIENTE
                'Asesor',
                'Producto', 'Tipo Producto', 'Alto', 'Ancho', 'Fuelle', 'Tipo Papel', 'Tipo Agarrador', 'Color', 'Detalle Impresión',
                'Cantidad', 'Precio Unitario', 'Total Línea',
                'Subtotal Venta', 'Descuento', 'Costo Envío', 'Total Venta',
                'Tipo Pago', 'No. Referencia', 'Banco',
                'Promoción Nombre', 'Promoción Tipo', 'Promoción Valor'
            ]
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1E3A8A']],
            ],
            2 => [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'DBEAFE']],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // REAJUSTE DE COMBINACIONES (Letras movidas por las 2 nuevas columnas)
                $sheet->mergeCells('A1:F1');   // VENTA
                $sheet->mergeCells('G1:M1');   // CLIENTE (Ahora hasta M)
                $sheet->mergeCells('N1:N1');   // ASESOR
                $sheet->mergeCells('O1:W1');   // PRODUCTO
                $sheet->mergeCells('X1:Z1');   // DETALLE
                $sheet->mergeCells('AA1:AD1'); // TOTALES
                $sheet->mergeCells('AE1:AG1'); // PAGO
                $sheet->mergeCells('AH1:AJ1'); // PROMOCIÓN

                $highestRow = $sheet->getHighestRow();
                // AJUSTE DE BORDES (Hasta AJ)
                $sheet->getStyle("A1:AJ{$highestRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => 'D1D5DB'],
                        ],
                    ],
                ]);

                // Auto-size corregido para el nuevo ancho
                foreach (range('A', 'Z') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
                foreach (range('A', 'J') as $col) {
                    $sheet->getColumnDimension('A' . $col)->setAutoSize(true);
                }
            },
        ];
    }
}
