<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente_telefono extends Model
{
    protected $fillable = [
        'clientes_id',
        'telefono_pais',
        'telefono_codigo_pais',
        'telefono_numero',
        'estado'
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'clientes_id');
    }
}
