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
        'comprobante_path',
        'users_id',
        'bancos_id',
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'ventas_id');
    }

    public function banco(){
        return $this->belongsTo(Banco::class, 'bancos_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'users_id');
    }
}
