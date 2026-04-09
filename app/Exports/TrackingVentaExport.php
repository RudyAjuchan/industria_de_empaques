<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\{
    FromCollection,
    WithHeadings,
    WithStyles,
    WithMapping,
    ShouldAutoSize
};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TrackingVentaExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $venta;

    public function __construct($venta)
    {
        $this->venta = $venta;
    }

    public function collection()
    {
        return collect($this->venta->detalles)
            ->flatMap(function ($detalle) {
                return $detalle->historialEstados->map(function ($h) use ($detalle) {

                    return [
                        'Venta' => $this->venta->numero_completo,
                        'Producto' => $detalle->producto->nombre,
                        'Cantidad' => $detalle->cantidad,
                        'Evento' => $h->tipo_evento,
                        'Estado' => $h->estado_produccion->nombre ?? '-',
                        'Proceso' => $h->proceso_estado->nombre ?? '-',
                        'Responsable' => $h->usuario->name ?? 'Sistema',
                        'Inicio' => $h->fecha_inicio,
                        'Fin' => $h->fecha_fin,
                        'Observación' => $h->observacion,
                    ];

                });

            });
    }

    public function headings(): array
    {
        return [
            'Venta',
            'Producto',
            'Cantidad',
            'Evento',
            'Estado',
            'Proceso',
            'Responsable',
            'Inicio',
            'Fin',
            'Observación',
        ];
    }
}
