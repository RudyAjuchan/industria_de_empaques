<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleEstadoProduccion extends Model
{
    protected $table = 'detalle_estado_produccions';

    protected $fillable = [
        'estado_produccions_id',
        'tipo',
        'nombre',
        'valor_string',
        'valor_double',
        'valor_integer',
        'valor_date',
    ];

    protected $casts = [
        'nombre_date' => 'date',
    ];

    // Estado al que pertenece
    public function estadoProduccion()
    {
        return $this->belongsTo(EstadoProduccion::class, 'estado_produccions_id');
    }
}
