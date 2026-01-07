<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $fillable = [
        'nombre',
        'genero',
        'dpi',
        'municipios_id',
        'pais',
        'estado_pais',
        'ciudad_pais',
        'direccion',
        'nit',
        'estado',
    ];

    public function emails()
    {
        return $this->hasMany(Cliente_email::class, 'clientes_id');
    }

    public function telefonos()
    {
        return $this->hasMany(Cliente_telefono::class, 'clientes_id');
    }

    public function municipio()
    {
        return $this->belongsTo(Municipio::class, 'municipios_id');
    }
}
