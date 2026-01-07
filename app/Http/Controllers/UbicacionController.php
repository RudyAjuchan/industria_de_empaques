<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use Illuminate\Http\Request;

class UbicacionController extends Controller
{
    public function departamentos()
    {
        return Departamento::where('estado', 1)
            ->orderBy('nombre')
            ->get();
    }

    public function municipios(Departamento $departamento)
    {
        return $departamento->municipios()
            ->where('estado', 1)
            ->orderBy('nombre')
            ->get();
    }
}
