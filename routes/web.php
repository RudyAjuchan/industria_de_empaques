<?php

use App\Http\Controllers\BancoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ForcePasswordController;
use App\Http\Controllers\PaginaController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProductosController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\TipoAgarradorController;
use App\Http\Controllers\TipoPapelController;
use App\Http\Controllers\UbicacionController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\VentaController;
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
    Route::get('/tipo-papel', [TipoPapelController::class, 'index'])->middleware('permission:tipo_papel.ver|venta.ver');
    Route::post('/tipo-papel', [TipoPapelController::class, 'store'])->middleware('permission:tipo_papel.crear|venta.crear');
    Route::put('/tipo-papel/{tipoPapel}', [TipoPapelController::class, 'update'])->middleware('permission:tipo_papel.editar');
    Route::delete('/tipo-papel/{tipoPapel}', [TipoPapelController::class, 'destroy'])->middleware('permission:tipo_papel.borrar');
    Route::get('/tipo-papel/export/pdf', [TipoPapelController::class, 'exportPdf'])
    ->middleware('permission:tipo_papel.reporte');
    Route::get('/tipo-papel/export/excel', [TipoPapelController::class, 'exportExcel'])
    ->middleware('permission:tipo_papel.reporte');
});

/* RUTAS PARA TIPO AGARRADOR */
Route::middleware(['auth', 'force.password'])->group(function () {
    Route::get('/agarrador', [TipoAgarradorController::class, 'index'])->middleware('permission:agarrador.ver|venta.ver');
    Route::post('/agarrador', [TipoAgarradorController::class, 'store'])->middleware('permission:agarrador.crear|venta.crear');
    Route::put('/agarrador/{tipoAgarrador}', [TipoAgarradorController::class, 'update'])->middleware('permission:agarrador.editar');
    Route::delete('/agarrador/{tipoAgarrador}', [TipoAgarradorController::class, 'destroy'])->middleware('permission:agarrador.borrar');
    Route::get('/agarrador/export/pdf', [TipoAgarradorController::class, 'exportPdf'])
    ->middleware('permission:agarrador.reporte');
    Route::get('/agarrador/export/excel', [TipoAgarradorController::class, 'exportExcel'])
    ->middleware('permission:agarrador.reporte');
});

/* RUTAS PARA PÁGINAS */
Route::middleware(['auth', 'force.password'])->group(function () {
    Route::get('/pagina', [PaginaController::class, 'index'])->middleware('permission:pagina.ver|venta.ver');
    Route::post('/pagina', [PaginaController::class, 'store'])->middleware('permission:pagina.crear|producto.crear');
    Route::put('/pagina/{pagina}', [PaginaController::class, 'update'])->middleware('permission:pagina.editar');
    Route::delete('/pagina/{pagina}', [PaginaController::class, 'destroy'])->middleware('permission:pagina.borrar');
    Route::get('/pagina/export/pdf', [PaginaController::class, 'exportPdf'])
    ->middleware('permission:pagina.reporte');
    Route::get('/pagina/export/excel', [PaginaController::class, 'exportExcel'])
    ->middleware('permission:pagina.reporte');
});

/* RUTAS PARA BANCOS */
Route::middleware(['auth', 'force.password'])->group(function () {
    Route::get('/banco', [BancoController::class, 'index'])->middleware('permission:banco.ver|venta.ver');
    Route::post('/banco', [BancoController::class, 'store'])->middleware('permission:banco.crear|venta.crear');
    Route::put('/banco/{banco}', [BancoController::class, 'update'])->middleware('permission:banco.editar');
    Route::delete('/banco/{banco}', [BancoController::class, 'destroy'])->middleware('permission:banco.borrar');
    Route::get('/banco/export/pdf', [BancoController::class, 'exportPdf'])
    ->middleware('permission:banco.reporte');
    Route::get('/banco/export/excel', [BancoController::class, 'exportExcel'])
    ->middleware('permission:banco.reporte');
});

/* RUTAS PARA CLIENTES */
Route::middleware(['auth', 'force.password'])->group(function () {
    Route::get('/cliente', [ClienteController::class, 'index'])->middleware('permission:cliente.ver|venta.ver');
    Route::get('/departamentos', [UbicacionController::class, 'departamentos']);
    Route::get('/municipios/{departamento}', [UbicacionController::class, 'municipios']);
    Route::post('/cliente', [ClienteController::class, 'store'])->middleware('permission:cliente.crear|venta.crear');
    Route::get('/cliente/{cliente}', [ClienteController::class, 'show'])->middleware('permission:cliente.ver');
    Route::put('/cliente/{cliente}', [ClienteController::class, 'update'])->middleware('permission:cliente.editar');
    Route::delete('/cliente/{cliente}', [ClienteController::class, 'destroy'])->middleware('permission:cliente.borrar');
    Route::get('/cliente/export/pdf', [ClienteController::class, 'exportPdf'])
    ->middleware('permission:cliente.reporte');
    Route::get('/cliente/export/excel', [ClienteController::class, 'exportExcel'])
    ->middleware('permission:cliente.reporte');
});

/* RUTAS PARA PRODUCTOS */
Route::middleware(['auth', 'force.password'])->group(function () {
    Route::get('/producto', [ProductosController::class, 'index'])->middleware('permission:producto.ver|venta.ver');
    Route::get('/producto/paginas', [ProductosController::class, 'getPaginas'])->middleware('permission:producto.ver');
    Route::get('/producto/{producto}', [ProductosController::class, 'show'])->middleware('permission:producto.editar');
    Route::post('/producto', [ProductosController::class, 'store'])->middleware('permission:producto.crear|venta.crear');
    Route::put('/producto/{producto}', [ProductosController::class, 'update'])->middleware('permission:producto.editar');
    Route::delete('/producto/{producto}', [ProductosController::class, 'destroy'])->middleware('permission:producto.borrar');
    Route::get('/producto/export/pdf', [ProductosController::class, 'exportPdf'])
    ->middleware('permission:producto.reporte');
    Route::get('/producto/export/excel', [ProductosController::class, 'exportExcel'])
    ->middleware('permission:producto.reporte');
});

/* RUTAS PARA VENTAS */
Route::middleware(['auth', 'force.password'])->group(function () {
    Route::get('/venta', [VentaController::class, 'index'])->middleware('permission:venta.ver');
    Route::post('/venta', [VentaController::class, 'store'])->middleware('permission:venta.crear');
    Route::get('/venta/{venta}', [VentaController::class, 'show'])->middleware('permission:venta.ver');
    Route::delete('/venta/{venta}', [VentaController::class, 'destroy'])->middleware('permission:venta.borrar');
    Route::get('/venta/{venta}/imprimir', [VentaController::class, 'imprimir'])->middleware('permission:venta.reporte');
    Route::get('/venta/export/pdf', [VentaController::class, 'exportPdf'])->middleware('permission:venta.reporte');
    Route::get('/venta/export/excel', [VentaController::class, 'exportExcel'])->middleware('permission:venta.reporte');

    //RUTAS COMPLEMENTARIAS PARA VENTAS
    Route::get('/listar/paginas', [VentaController::class, 'getPaginas'])->middleware('permission:venta.crear');
    Route::get('/client/search', [ClienteController::class, 'search'])
        ->middleware('permission:venta.crear');
    Route::post('/product/search', [ProductosController::class, 'search'])
        ->middleware('permission:venta.crear');
});


require __DIR__.'/auth.php';
