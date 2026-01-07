<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Municipio extends Model
{
    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'departamentos_id');
    }
}
