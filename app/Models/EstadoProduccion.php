<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoProduccion extends Model
{
    protected $table = 'estado_produccions';

    protected $fillable = [
        'nombre',
        'users_id',
        'orden',
        'estado',
    ];

    // Usuario responsable del estado
    public function usuario()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    // Procesos que usan este estado
    public function procesos()
    {
        return $this->hasMany(ProcesoEstadoProduccion::class, 'estado_produccions_id');
    }

    // Detalles dinámicos del estado
    public function detalles()
    {
        return $this->hasMany(DetalleEstadoProduccion::class, 'estado_produccions_id');
    }
}
