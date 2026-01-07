<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente_email extends Model
{
    protected $fillable = ['clientes_id', 'email', 'estado'];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'clientes_id');
    }
}
