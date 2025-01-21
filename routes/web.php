<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (!Auth::check() || (Auth::check() && Auth::user()->hasRole('Administrador'))) {
        return redirect('admin');
    }

    return view('welcome');
});

Route::get('/chequeo', function () {
    return view('chequeo');
})->name('chequeo');

Route::get('/documentacion', function () {
    return view('docs');
});
