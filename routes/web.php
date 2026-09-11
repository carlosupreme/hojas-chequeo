<?php

use App\Http\Controllers\DatabaseBackupController;
use App\Http\Controllers\LoginSelectionController;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Support\Facades\Route;

Route::get('/', [LoginSelectionController::class, 'index'])
    ->name('login.selection');
Route::post('/login/operador', [LoginSelectionController::class, 'login'])->name('login.operador');

Route::middleware(['web', Authenticate::class])->group(function () {
    Route::get('/admin/system-update/download-database', [DatabaseBackupController::class, 'download'])
        ->name('admin.system-update.download-db');
});
