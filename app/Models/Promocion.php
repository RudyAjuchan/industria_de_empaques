<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promocion extends Model
{
    protected $fillable = [
        'nombre',
        'tipo',
        'valor',
        'fecha_inicio',
        'fecha_fin',
        'aplica_a',
        'activo'
    ];

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'promocion_productos', 'promocions_id', 'productos_id');
    }

    public function scopeVigente($query)
    {
        return $query->where('activo', 1)
            ->where(function ($q) {
                $q->whereNull('fecha_inicio')
                    ->orWhere('fecha_inicio', '<=', today());
            })
            ->where(function ($q) {
                $q->whereNull('fecha_fin')
                    ->orWhere('fecha_fin', '>=', today());
            });
    }
}
