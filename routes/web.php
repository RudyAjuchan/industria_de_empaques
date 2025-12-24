<?php

use App\Http\Controllers\ForcePasswordController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RolePermissionController;
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
    Route::get('/usuarios', [UsuarioController::class, 'index'])->middleware('permission:usuario.ver');
    Route::post('/usuarios', [UsuarioController::class, 'store'])->middleware('permission:usuario.crear');
    Route::put('/usuarios/{user}', [UsuarioController::class, 'update'])->middleware('permission:usuario.editar');
    Route::delete('/usuarios/{user}', [UsuarioController::class, 'destroy'])->middleware('permission:usuario.eliminar');
    Route::get('/usuario/roles', [UsuarioController::class, 'roles'])->middleware('permission:usuario.ver');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/cambiar-password', [ForcePasswordController::class, 'edit'])
        ->name('password.force.edit');

    Route::post('/cambiar-password', [ForcePasswordController::class, 'update'])
        ->name('password.force.update');
});

/* RUTAS PARA ROLES */

Route::middleware(['auth', 'force.password'])->group(function () {

    // Roles CRUD
    Route::get('/roles', [RoleController::class, 'index'])->middleware('permission:rol.ver');
    Route::post('/roles', [RoleController::class, 'store'])->middleware('permission:rol.crear');
    Route::get('/roles/{role}', [RoleController::class, 'show'])->middleware('permission:rol.ver');
    Route::put('/roles/{role}', [RoleController::class, 'update'])->middleware('permission:rol.editar');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->middleware('permission:rol.borrar');

    // Permisos (solo lectura)
    Route::get('/permissions', [RolePermissionController::class, 'index'])->middleware('permission:rol.ver');

    // Permisos por rol
    Route::get('/roles/{role}/permissions', [RolePermissionController::class, 'show'])->middleware('permission:rol.permisos');
    Route::post('/roles/{role}/permissions', [RolePermissionController::class, 'sync'])->middleware('permission:rol.permisos');
    Route::get('/permissions', [PermissionController::class, 'index'])->middleware('permission:rol.permisos');
});

require __DIR__.'/auth.php';
