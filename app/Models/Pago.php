<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $fillable = [
        'ventas_id',
        'monto',
        'metodo_pago',
        'referencia',
        'users_id',
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'ventas_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'users_id');
    }
}