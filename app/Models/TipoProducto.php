<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoProducto extends Model
{
    protected $fillable = [
        'nombre',
        'codigo',
        'estado',
    ];

    protected $casts = [
        'estado' => 'integer',
    ];

    public function productos()
    {
        return $this->hasMany(Producto::class, 'tipo_productos_id');
    }
}
