<?php

use App\Http\Controllers\LoginSelectionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LoginSelectionController::class, 'index'])
    ->name('login.selection');
Route::post('/login/operador', [LoginSelectionController::class, 'login'])->name('login.operador');
