<?php

namespace App\Exports;

use App\Exports\Dashboard\ProduccionSheet;
use App\Exports\Dashboard\TiposProductoSheet;
use App\Exports\Dashboard\VentasPorPaginaSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class EstadisticasProduccionExport implements WithMultipleSheets
{
    protected $data;
    protected $data2;
    protected $data3;

    public function __construct($data, $data2, $data3)
    {
        $this->data = $data;
        $this->data2 = $data2;
        $this->data3 = $data3;
    }

    public function sheets(): array
    {
        return [

            new ProduccionSheet($this->data),

            new VentasPorPaginaSheet($this->data2),

            new TiposProductoSheet($this->data3),

        ];
    }
}