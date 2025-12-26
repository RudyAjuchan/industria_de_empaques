<?php

use App\Http\Controllers\ForcePasswordController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\TipoAgarradorController;
use App\Http\Controllers\TipoPapelController;
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
    Route::get('/usuarios/export/pdf', [UsuarioController::class, 'exportPdf'])
    ->middleware(['auth', 'permission:usuario.reporte']);
    Route::get('/usuarios/export/excel', [UsuarioController::class, 'exportExcel'])
    ->middleware(['auth', 'permission:usuario.reporte']);
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
    Route::get('/roles/export/pdf', [RoleController::class, 'exportPdf'])
    ->middleware(['auth', 'permission:rol.reporte']);
    Route::get('/roles/export/excel', [RoleController::class, 'exportExcel'])
    ->middleware(['auth', 'permission:rol.reporte']);
});

/* RUTAS PARA TIPO PAPEL */
Route::middleware(['auth', 'force.password'])->group(function () {
    Route::get('/tipo-papel', [TipoPapelController::class, 'index'])->middleware('permission:tipo_papel.ver');
    Route::post('/tipo-papel', [TipoPapelController::class, 'store'])->middleware('permission:tipo_papel.crear');
    Route::put('/tipo-papel/{tipoPapel}', [TipoPapelController::class, 'update'])->middleware('permission:tipo_papel.editar');
    Route::delete('/tipo-papel/{tipoPapel}', [TipoPapelController::class, 'destroy'])->middleware('permission:tipo_papel.borrar');
    Route::get('/tipo-papel/export/pdf', [TipoPapelController::class, 'exportPdf'])
    ->middleware('permission:tipo_papel.reporte');
    Route::get('/tipo-papel/export/excel', [TipoPapelController::class, 'exportExcel'])
    ->middleware('permission:tipo_papel.reporte');
});

/* RUTAS PARA TIPO AGARRADOR */
Route::middleware(['auth', 'force.password'])->group(function () {
    Route::get('/agarrador', [TipoAgarradorController::class, 'index'])->middleware('permission:agarrador.ver');
    Route::post('/agarrador', [TipoAgarradorController::class, 'store'])->middleware('permission:agarrador.crear');
    Route::put('/agarrador/{tipoAgarrador}', [TipoAgarradorController::class, 'update'])->middleware('permission:agarrador.editar');
    Route::delete('/agarrador/{tipoAgarrador}', [TipoAgarradorController::class, 'destroy'])->middleware('permission:agarrador.borrar');
    Route::get('/agarrador/export/pdf', [TipoAgarradorController::class, 'exportPdf'])
    ->middleware('permission:agarrador.reporte');
    Route::get('/agarrador/export/excel', [TipoAgarradorController::class, 'exportExcel'])
    ->middleware('permission:agarrador.reporte');
});


require __DIR__.'/auth.php';
