<?php

use App\Http\Controllers\ForcePasswordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('login');
});
Route::get('/login', function () {
    return view('login');
})->name('login');;

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified', 'force.password'])->name('dashboard');

Route::middleware(['auth', 'force.password'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

//RUTAS PARA USUARIOS
Route::middleware(['auth', 'force.password'])->group(function () {
    Route::get('/usuarios', [UsuarioController::class, 'index']);
    Route::post('/usuarios', [UsuarioController::class, 'store']);
    Route::put('/usuarios/{user}', [UsuarioController::class, 'update']);
    Route::delete('/usuarios/{user}', [UsuarioController::class, 'destroy']);
    Route::get('/roles', [UsuarioController::class, 'roles']);
});

Route::middleware(['auth'])->group(function () {
    Route::get('/cambiar-password', [ForcePasswordController::class, 'edit'])
        ->name('password.force.edit');

    Route::post('/cambiar-password', [ForcePasswordController::class, 'update'])
        ->name('password.force.update');
});

require __DIR__.'/auth.php';
