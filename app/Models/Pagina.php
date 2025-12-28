<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pagina extends Model
{
    protected $fillable = ['nombre', 'estado'];

    protected $casts = [
        'estado' => 'integer',
    ];
}
