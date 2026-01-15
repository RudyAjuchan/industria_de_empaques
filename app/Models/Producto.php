<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'productos';

    protected $fillable = [
        'nombre',
        'alto',
        'ancho',
        'fuelle',
        'tipo',
        'paginas_id',
        'estado',
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
            ? asset('storage/' . $this->imagenPrincipal->path)
            : null;
    }

    public function paginas()
    {
        return $this->belongsTo(Pagina::class, 'paginas_id');
    }
}
