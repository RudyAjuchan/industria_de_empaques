<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'imagen',
        'tipo_redireccion',
        'productos_id',
        'tipo_producto',
        'promocions_id',
        'orden',
        'activo',
        'fecha_inicio',
        'fecha_fin'
    ];

    protected $casts = [
        'activo' => 'boolean',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
    ];

    protected $appends = [
        'imagen_url'
    ];

    /*
    |--------------------------------------------------------------------------
    | Accessor imagen
    |--------------------------------------------------------------------------
    */

    public function getImagenUrlAttribute()
    {
        if (!$this->imagen) {
            return null;
        }

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('s3');

        return $disk->url($this->imagen);
    }

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'productos_id');
    }

    public function promocion()
    {
        return $this->belongsTo(Promocion::class, 'promocions_id');
    }
}
