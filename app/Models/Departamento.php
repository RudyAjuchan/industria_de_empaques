<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
    protected $fillable = [
        'nombre',
        'estado',
    ];

    public function municipios()
    {
        return $this->hasMany(Municipio::class, 'departamentos_id');
    }
}
