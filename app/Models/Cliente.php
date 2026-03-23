<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Cliente extends Authenticatable
{
    use Notifiable;

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

        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
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
