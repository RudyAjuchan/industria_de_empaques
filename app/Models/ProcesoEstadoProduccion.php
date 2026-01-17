<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcesoEstadoProduccion extends Model
{
    protected $table = 'proceso_estado_produccions';

    protected $fillable = [
        'nombre',
        'estado_produccions_id',
        'estado',
    ];

    // Estado base del proceso (inicio o actual)
    public function estadoProduccion()
    {
        return $this->belongsTo(EstadoProduccion::class, 'estado_produccions_id');
    }

    // Ventas asociadas a este proceso
    public function ventas()
    {
        return $this->hasMany(Venta::class, 'proceso_estado_produccions_id');
    }
}
