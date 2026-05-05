<?php

namespace App\Exports;

use App\Models\DetalleVenta;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping; // Importante para separar lógica
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Carbon;

class VentasContabilidadExport implements FromCollection, WithHeadings, WithStyles, WithEvents, WithMapping
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
        // Añadimos 'cliente.telefonos' para evitar N+1
        return DetalleVenta::with([
            'venta.cliente.municipio.departamento',
            'venta.cliente.telefonos', 
            'venta.vendedor',
            'venta.pagos',
            'venta.banco',
            'producto.paginas',
            'tipoPapel',
            'tipoAgarrador'
        ])
        ->whereHas('venta', function ($q) {
            $q->where('estado', 'emitida');
            if ($this->fechaInicio) $q->whereDate('created_at', '>=', $this->fechaInicio);
            if ($this->fechaFin) $q->whereDate('created_at', '<=', $this->fechaFin);
        })
        ->get();
    }

    /**
     * Usar WithMapping es más limpio para manejar datos nullables
     */
    public function map($d): array
    {
        $venta = $d->venta;
        $cliente = $venta->cliente ?? null;
        $producto = $d->producto ?? null;
        $pagos = $venta->pagos ?? collect();

        $documento = $venta->serie . '-' . str_pad($venta->numero, 6, '0', STR_PAD_LEFT);

        // PAGOS - Manejo seguro de nulos
        $totalPagado = $pagos->sum('monto');
        $saldoPendiente = ($venta->total ?? 0) - $totalPagado;

        $historialPagos = $pagos->map(function ($p) {
            $fecha = $p->created_at ? $p->created_at->format('d/m/Y') : 'N/A';
            return 'Q' . number_format($p->monto, 2) . " (" . ($p->metodo_pago ?? 'N/A') . ") " . $fecha;
        })->implode(" | ");

        return [
            // VENTA
            $venta->id,
            $venta->created_at ? $venta->created_at->format('Y-m-d') : '',
            $venta->updated_at ? $venta->updated_at->format('Y-m-d') : '',
            $venta->serie,
            $documento,
            $venta->estado,
            $venta->estado_produccion,
            $venta->es_cliente_nuevo ? 'Nuevo' : 'Existente',
            $venta->serie === 'WEB' ? 'Ecommerce' : 'Interno',
            $venta->fecha_entrega ? Carbon::parse($venta->fecha_entrega)->format('Y-m-d') : '',

            // CLIENTE (Uso de null-safe operator ?? para seguridad total)
            $cliente->nombre ?? 'N/A',
            $cliente->nit ?? 'C/F',
            $cliente->dpi ?? '',
            $cliente->email ?? '',
            ($cliente && $cliente->telefonos->first()) ? $cliente->telefonos->first()->telefono_numero : '',
            $cliente->genero ?? '',
            $cliente->pais ?? '',
            $cliente->estado_pais ?? '',
            $cliente->ciudad_pais ?? '',
            $cliente->municipio->departamento->nombre ?? '',
            $cliente->municipio->nombre ?? '',
            $cliente->direccion ?? '',

            // ASESOR
            $venta->vendedor->name ?? 'Sistema',

            // PRODUCTO
            $producto->nombre ?? 'Desconocido',
            $producto->tipo ?? '',
            $producto->tipo_producto ?? '',
            $producto->paginas->nombre ?? '',
            $producto->alto ?? 0,
            $producto->ancho ?? 0,
            $producto->fuelle ?? 0,
            $d->tipoPapel->nombre ?? '',
            $d->tipoAgarrador->nombre ?? '',
            $d->color_agarrador ?? '',
            $d->detalle_impresion ?? '',

            // DETALLE
            $d->nombre_logo ?? '',
            //$d->logo_path ? basename($d->logo_path) : '',
            //$d->archivo_diseno_path ? basename($d->archivo_diseno_path) : '',
            $d->cantidad,
            $d->precio,
            $d->total,

            // TOTALES VENTA
            $venta->subtotal,
            $venta->descuento,
            $venta->costo_envio,
            $venta->total,

            // PAGOS
            $totalPagado,
            $saldoPendiente,
            $pagos->count(),
            ($pagos->last() && $pagos->last()->created_at) ? $pagos->last()->created_at->format('Y-m-d') : '',
            $historialPagos,

            // INFO PAGO
            $venta->tipo_pago,
            $venta->banco->nombre ?? 'N/A',

            // PROMOCIÓN
            $d->promocion_aplicada['nombre'] ?? '',
            $d->promocion_aplicada['tipo'] ?? '',
            $d->promocion_aplicada['valor'] ?? '',
        ];
    }

    public function headings(): array
    {
        return [
            ['VENTA', '', '', '', '', '', '', '', '', '', 'CLIENTE', '', '', '', '', '', '', '', '', '', '', '', 'ASESOR', 'PRODUCTO', '', '', '', '', '', '', '', '', '', '', 'DETALLE', '', '', '', '', '', 'TOTALES', '', '', '', 'PAGOS', '', '', '', '', 'PAGO INFO', '', 'PROMOCIÓN', '', ''],
            ['ID', 'Fecha', 'Actualizado', 'Serie', 'Número', 'Estado', 'Est. Prod.', 'Tipo cliente', 'Origen', 'Entrega', 'Nombre', 'NIT', 'DPI', 'Email', 'Teléfono', 'Género', 'País', 'Estado', 'Ciudad', 'Depto', 'Municipio', 'Dirección', 'Asesor', 'Producto', 'Tipo', 'Categoría', 'Página', 'Alto', 'Ancho', 'Fuelle', 'Papel', 'Agarrador', 'Color', 'Impresión', 'Logo', 'Cant.', 'Precio', 'Subtotal L.', 'Subtotal V.', 'Desc.', 'Envío', 'Total V.', 'Pagado', 'Pendiente', 'Cant. Pagos', 'Últ. Pago', 'Historial', 'Tipo Pago', 'Banco', 'Nombre', 'Tipo', 'Valor']
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'alignment' => ['horizontal' => 'center'],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1E3A8A']],
            ],
            2 => [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => 'center', 'wrapText' => true],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'DBEAFE']],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // COMBINACIONES DE CELDAS (Merging)
                $sheet->mergeCells('A1:J1');   // Venta
                $sheet->mergeCells('K1:V1');   // Cliente
                $sheet->mergeCells('X1:AH1');  // Producto
                $sheet->mergeCells('AI1:AL1'); // Detalle
                $sheet->mergeCells('AM1:AP1'); // Totales
                $sheet->mergeCells('AQ1:AU1'); // Pagos
                $sheet->mergeCells('AV1:AW1'); // Pago Info
                $sheet->mergeCells('AX1:AZ1'); // Promoción

                // Auto-ancho para todas las columnas usadas
                foreach (range('A', 'Z') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
                foreach (range('A', 'B') as $prefix) {
                    foreach (range('A', 'Z') as $col) {
                        $sheet->getColumnDimension($prefix.$col)->setAutoSize(true);
                    }
                }
                
                // Bordes
                $highestRow = $sheet->getHighestRow();
                $highestCol = $sheet->getHighestColumn();
                $sheet->getStyle("A1:{$highestCol}{$highestRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => 'D1D5DB'],
                        ],
                    ],
                ]);
            },
        ];
    }
}