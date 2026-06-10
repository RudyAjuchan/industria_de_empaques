<?php

namespace App\Exports;

use App\Models\DetalleVenta;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping; // Importante para separar lógica
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Carbon;

class VentasContabilidadExport implements FromCollection, WithHeadings, WithStyles, WithEvents, WithMapping, WithColumnFormatting, ShouldAutoSize
{
    protected $fechaInicio;
    protected $fechaFin;
    protected array $ventasMostradas = [];

    public function __construct($fechaInicio = null, $fechaFin = null)
    {
        $this->fechaInicio = $this->normalizarFecha($fechaInicio);
        $this->fechaFin = $this->normalizarFecha($fechaFin);
    }

    public function collection()
    {
        // Añadimos 'cliente.telefonos' para evitar N+1
        return DetalleVenta::with([
            'venta.cliente.municipio.departamento',
            'venta.cliente.telefonos', 
            'venta.vendedor',
            'venta.pagos.banco',
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
        ->orderBy('ventas_id')
        ->orderBy('id')
        ->get();
    }

    private function normalizarFecha($fecha): ?string
    {
        if (!$fecha || in_array($fecha, ['null', 'undefined'], true)) {
            return null;
        }

        return $fecha;
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
        $primeraFilaVenta = !isset($this->ventasMostradas[$venta->id]);
        $this->ventasMostradas[$venta->id] = true;

        $documento = $venta->serie . '-' . str_pad($venta->numero, 6, '0', STR_PAD_LEFT);

        // PAGOS - Manejo seguro de nulos
        $totalPagado = $pagos->sum('monto');
        $saldoPendiente = ($venta->total ?? 0) - $totalPagado;
        $subtotalLinea = (float) $d->precio * (float) $d->cantidad;
        $totalLinea = (float) $d->total;
        $descuentoLinea = max(0, $subtotalLinea - $totalLinea);
        $descuentoVenta = (float) ($venta->descuento ?? 0) + $this->promocionCarritoMonto($venta);

        $historialPagos = $pagos->map(function ($p) {
            $fecha = $p->created_at ? $p->created_at->format('d/m/Y') : 'N/A';
            return 'Q' . number_format($p->monto, 2) 
            . " (" . ($p->metodo_pago ?? 'N/A') . ") " 
            . " (Banco: " . ($p->banco->nombre ?? 'N/A') . ") " 
            . " (No. dep: " . ($p->referencia ?? 'N/A') . ") " 
            . $fecha;
        })->implode("\n");

        $comprobantes = $pagos
            ->filter(fn($p) => $p->comprobante_path)
            ->map(fn($p) => route('pagos.comprobante', $p))
            ->implode("\n");

        return [
            // VENTA
            $documento,
            $venta->id,
            $venta->created_at ? $venta->created_at->format('Y-m-d') : '',
            $venta->updated_at ? $venta->updated_at->format('Y-m-d') : '',
            $venta->serie,
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
            $d->producto_nombre ?? $producto->nombre ?? 'Desconocido',
            $d->producto_tipo ?? $producto->tipo ?? '',
            $d->producto_tipo_producto ?? $producto->tipo_producto ?? '',
            $d->producto_pagina ?? $producto->paginas->nombre ?? '',
            $d->producto_alto ?? $producto->alto ?? 0,
            $d->producto_ancho ?? $producto->ancho ?? 0,
            $d->producto_fuelle ?? $producto->fuelle ?? 0,
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
            $subtotalLinea,
            $descuentoLinea,
            $totalLinea,

            // TOTALES VENTA
            $primeraFilaVenta ? $venta->subtotal : '',
            $primeraFilaVenta ? $descuentoVenta : '',
            $primeraFilaVenta ? $venta->costo_envio : '',
            $primeraFilaVenta ? $venta->total : '',

            // PAGOS
            $primeraFilaVenta ? $totalPagado : '',
            $primeraFilaVenta ? $saldoPendiente : '',
            $primeraFilaVenta ? $pagos->count() : '',
            $primeraFilaVenta && $pagos->last() && $pagos->last()->created_at ? $pagos->last()->created_at->format('Y-m-d') : '',
            $primeraFilaVenta ? $historialPagos : '',
            $primeraFilaVenta ? $comprobantes : '',

            // INFO PAGO
            $primeraFilaVenta ? $venta->tipo_pago : '',
            $primeraFilaVenta ? $venta->banco->nombre ?? 'N/A' : '',

            // PROMOCIÓN
            $d->promocion_aplicada['nombre'] ?? '',
            $d->promocion_aplicada['tipo'] ?? '',
            $d->promocion_aplicada['valor'] ?? '',
        ];
    }

    private function promocionCarritoMonto($venta): float
    {
        $promocion = $venta->promociones;

        if (!$promocion) {
            return 0;
        }

        if (($promocion['tipo'] ?? null) === 'porcentaje') {
            return (float) $venta->subtotal * ((float) ($promocion['valor'] ?? 0) / 100);
        }

        return (float) ($promocion['valor'] ?? 0);
    }

    public function headings(): array
    {
        return [
            array_merge(
                ['VENTA'], array_fill(0, 9, ''),
                ['CLIENTE'], array_fill(0, 11, ''),
                ['ASESOR'],
                ['PRODUCTO'], array_fill(0, 10, ''),
                ['DETALLE'], array_fill(0, 5, ''),
                ['TOTALES'], array_fill(0, 3, ''),
                ['PAGOS'], array_fill(0, 5, ''),
                ['PAGO INFO'], [''],
                ['PROMOCIÓN'], array_fill(0, 2, ''),
            ),
            ['Número', 'ID', 'Fecha', 'Actualizado', 'Serie', 'Estado', 'Est. Prod.', 'Tipo cliente', 'Origen', 'Entrega', 'Nombre', 'NIT', 'DPI', 'Email', 'Teléfono', 'Género', 'País', 'Estado', 'Ciudad', 'Depto', 'Municipio', 'Dirección', 'Asesor', 'Producto', 'Tipo', 'Categoría', 'Página', 'Alto', 'Ancho', 'Fuelle', 'Papel', 'Agarrador', 'Color', 'Impresión', 'Logo', 'Cant.', 'Precio', 'Subtotal L.', 'Desc. L.', 'Total L.', 'Subtotal V.', 'Desc. V.', 'Envío', 'Total V.', 'Pagado', 'Pendiente', 'Cant. Pagos', 'Últ. Pago', 'Historial', 'Comprobantes', 'Tipo Pago', 'Banco', 'Nombre', 'Tipo', 'Valor']
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Aplicamos el ajuste de texto específicamente al rango de la columna J
        // desde la fila 2 hasta la última con datos.
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle('AW2:AX' . $lastRow)->getAlignment()->setWrapText(true);

        // Alineación vertical arriba para que se vea ordenado
        $sheet->getStyle('AW2:AX' . $lastRow)->getAlignment()->setVertical('top');
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
                $sheet->mergeCells('AI1:AN1'); // Detalle
                $sheet->mergeCells('AO1:AR1'); // Totales
                $sheet->mergeCells('AS1:AX1'); // Pagos
                $sheet->mergeCells('AY1:AZ1'); // Pago Info
                $sheet->mergeCells('BA1:BC1'); // Promoción
                $sheet->freezePane('B3');

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

                $ventaActual = null;
                $colorIndex = 0;
                $coloresVenta = ['FFFFFF', 'E8F1FB'];

                for ($row = 3; $row <= $highestRow; $row++) {
                    $numeroVenta = $sheet->getCell("A{$row}")->getValue();

                    if ($numeroVenta !== $ventaActual) {
                        $ventaActual = $numeroVenta;
                        $colorIndex++;
                    }

                    $sheet->getStyle("A{$row}:{$highestCol}{$row}")->applyFromArray([
                        'fill' => [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => ['rgb' => $coloresVenta[$colorIndex % 2]],
                        ],
                    ]);
                }

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


    public function columnFormats(): array
    {
        return [
            'AK' => '"Q" #,##0.00',
            'AL' => '"Q" #,##0.00',
            'AM' => '"Q" #,##0.00',
            'AN' => '"Q" #,##0.00',
            'AO' => '"Q" #,##0.00',
            'AP' => '"Q" #,##0.00',
            'AQ' => '"Q" #,##0.00',
            'AR' => '"Q" #,##0.00',
            'AS' => '"Q" #,##0.00',
            'AT' => '"Q" #,##0.00',
        ];
    }
}
