<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoAgarrador extends Model
{
    protected $fillable = ['nombre', 'estado'];

    protected $casts = [
        'estado' => 'integer',
    ];
}
