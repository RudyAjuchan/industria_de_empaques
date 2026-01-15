<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductoImagen extends Model
{
    protected $table = 'producto_imagens';

    protected $fillable = [
        'productos_id',
        'path',
        'is_main',
        'orden',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'productos_id');
    }
}
