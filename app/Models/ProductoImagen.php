<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProductoImagen extends Model
{
    protected $table = 'producto_imagens';

    protected $fillable = [
        'productos_id',
        'path',
        'is_main',
        'orden',
    ];

    // IMPORTANTE
    protected $appends = ['url'];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'productos_id');
    }

    // ESTE ES EL CAMBIO CLAVE
    public function getUrlAttribute()
    {
        return Storage::disk('s3')->url($this->path);
    }
}