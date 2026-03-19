<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialEstadoCampo extends Model
{
    protected $fillable = [
        'historial_estado_produccions_id',
        'detalle_estado_produccions_id',
        'valor_string',
        'valor_double',
        'valor_integer',
        'valor_date',
    ];

    public function historial()
    {
        return $this->belongsTo(HistorialEstadoProduccion::class);
    }

    public function campo()
    {
        return $this->belongsTo(DetalleEstadoProduccion::class, 'detalle_estado_produccions_id');
    }
}
