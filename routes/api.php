<?php

use App\Http\Controllers\Auth\ClientePasswordResetController;
use Illuminate\Support\Facades\Route;

Route::post('/cliente/password/email', [ClientePasswordResetController::class, 'sendResetLink']);
Route::get('/cliente/reset-password/{token}', [ClientePasswordResetController::class, 'create'])
    ->name('cliente.password.reset');
Route::post('/cliente/reset-password', [ClientePasswordResetController::class, 'reset'])
    ->name('cliente.password.store');
