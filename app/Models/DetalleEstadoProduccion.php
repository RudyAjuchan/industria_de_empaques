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
        'label',
        'requerido',
    ];

    protected $casts = [
        'requerido' => 'boolean',
    ];

    // Estado al que pertenece
    public function estadoProduccion()
    {
        return $this->belongsTo(EstadoProduccion::class, 'estado_produccions_id');
    }
}
