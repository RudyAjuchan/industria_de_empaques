<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Producto extends Model
{
    protected $table = 'productos';

    protected $fillable = [
        'nombre',
        'sku',
        'alto',
        'ancho',
        'fuelle',
        'tipo',
        'tipo_productos_id',
        'paginas_id',
        'estado',
        'tipo_producto',
        'precio_base',
        'descripcion',
        'ecommerce',
    ];

    public function imagenes()
    {
        return $this->hasMany(ProductoImagen::class, 'productos_id');
    }

    public function imagenPrincipal()
    {
        return $this->hasOne(ProductoImagen::class, 'productos_id')
                    ->where('is_main', true);
    }

    protected $appends = ['imagen_principal_url'];

    public function getImagenPrincipalUrlAttribute()
    {
        return $this->imagenPrincipal
            ? Storage::disk('s3')->url($this->imagenPrincipal->path)
            : null;
    }

    public function paginas()
    {
        return $this->belongsTo(Pagina::class, 'paginas_id');
    }

    public function tipoCatalogo()
    {
        return $this->belongsTo(TipoProducto::class, 'tipo_productos_id');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class, 'productos_id');
    }

    public function estadosProduccion()
    {
        return $this->belongsToMany(
            EstadoProduccion::class,
            'producto_estado_produccion',
            'productos_id',
            'estado_produccions_id'
        )
            ->withPivot('orden')
            ->orderBy('pivot_orden');
    }

    public function promociones()
    {
        return $this->belongsToMany(
            Promocion::class,
            'promocion_productos',
            'productos_id',
            'promocions_id'
        );
    }

    public function promocionVigente()
    {
        return $this->belongsToMany(
            Promocion::class,
            'promocion_productos',
            'productos_id',
            'promocions_id'
        )->vigente();
    }
}
