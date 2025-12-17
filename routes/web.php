<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginSelectionController;

Route::get('/', [LoginSelectionController::class, 'index'])->name('login.selection');
Route::post('/login/operador', [LoginSelectionController::class, 'login'])->name('login.operador');

Route::get('/documentacion', function () {
    return view('docs');
});
