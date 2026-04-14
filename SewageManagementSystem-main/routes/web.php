<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('publicpage');
})->name('home');

Route::view('/submitreport', 'submitreport')
    ->name('submitreport');

Route::view('/checkreportstatus', 'checkreportstatus')
    ->name('checkreportstatus');

Route::view('dashboard', 'dashboard')
    ->name('dashboard');

require __DIR__.'/settings.php';


// Operations routes
Route::get('/Operations', function () {
    return view('operations.dashboard');
})->name('operations.dashboard');
