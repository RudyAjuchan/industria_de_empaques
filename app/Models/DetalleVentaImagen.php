<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class DetalleVentaImagen extends Model
{
    protected $table = 'detalle_venta_imagens';

    protected $appends = ['url'];

    protected $fillable = [
        'detalle_ventas_id',
        'path',
        'estado',
    ];

    public function DetalleVenta()
    {
        return $this->belongsTo(DetalleVenta::class, 'detalle_ventas_id');
    }

    public function getUrlAttribute()
    {
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('s3');

        return $disk->url($this->path);
    }
}
