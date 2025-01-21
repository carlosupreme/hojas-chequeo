<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (!Auth::check() || (Auth::check() && Auth::user()->hasRole('Administrador'))) {
        return redirect('admin');
    }

    return redirect('operador');
});


Route::get('/documentacion', function () {
    return view('docs');
});
