<?php

namespace App\Http\Controllers\Api\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\Pagina;
use Illuminate\Http\Request;

class PaginaController extends Controller
{
    public function index()
    {
        return Pagina::where('estado', 1)->get();
    }
}
