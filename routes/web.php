<?php

use App\Http\Controllers\IncidentController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CrewController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('login');
})->name('login');


route::view('/testing', 'testing') // this is for login //
    ->name('testing'); // -> name is not really needed //

Route::view('/submitreport', 'submitreport') //operation section //
    ->name('submitreport');

Route::view('/checkreportstatus', 'checkreportstatus') // Operation section // 
    ->name('checkreportstatus');

Route::view('dashboard', 'dashboard') // not sure what this is //
    ->name('dashboard');

Route::view('/login', 'login') // this is for login //
    ->name('login'); // -> name is not really needed // 

Route::view('/AdminDash', 'AdminDashboard') // this is for login //
    ->name('Dash'); // -> name is not really needed // 


require __DIR__.'/settings.php';

// Customer routes
Route::get('/customer/home', function () {
    return view('customer.home');      
})->name('customer.home');

// Admin routes
Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
Route::get('/admin/crew-management', [CrewController::class, 'index'])->name('admin.crew-management');
Route::get('/admin/crew/create', [CrewController::class, 'create'])->name('admin.crew.create');
Route::post('/admin/crew', [CrewController::class, 'store'])->name('admin.crew.store');
Route::put('/admin/crew/{crew}', [CrewController::class, 'update'])->name('admin.crew.update');
Route::delete('/admin/crew/{crew}', [CrewController::class, 'destroy'])->name('admin.crew.destroy');

// Operations routes
Route::get('/Operations', function () {
    return view('operations.dashboard');
})->name('operations.dashboard');


//ai 
//Incidents dashboard
Route::get('/ai/upload', function () {
    return view('ai.upload');
})->name('AI');

Route::get('ai/incidents/dashboard', [IncidentController::class, 'dashboard'])->name('ai.incidents.dashboard');
Route::get('ai/incidents/resolved', [IncidentController::class, 'resolved'])->name('ai.incidents.resolved');


// Incident routes
Route::get('/incidents/create', [IncidentController::class, 'create'])->name('incidents.create');
Route::post('/incidents', [IncidentController::class, 'store'])->name('incidents.store');
Route::get('/incidents', [IncidentController::class, 'index'])->name('incidents.index');

// Admin review routes
Route::get('/admin/incidents/review', [IncidentController::class, 'review'])->name('admin.incidents.review');
Route::post('/admin/incidents/{incident}/review', [IncidentController::class, 'updateReview'])->name('admin.incidents.update-review');


// Serve incident images
Route::get('/incidents/{incident}/image', [IncidentController::class, 'showImage'])->name('incidents.image');

// Photo EXIF location map (Brunei)
Route::view('/photo-exif-map', 'photo-exif-map')->name('photo.exif.map');
