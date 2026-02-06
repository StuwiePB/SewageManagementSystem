<?php

use App\Http\Controllers\IncidentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('publicpage');
})->name('home');

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

Route::view('/testing', 'testing') // this is for login //
    ->name('testing'); // -> name is not really needed // 

Route::view('/AI', 'AI') // this is for login //
    ->name('AI'); // -> name is not really needed // 


require __DIR__.'/settings.php';


// Operations routes
Route::get('/Operations', function () {
    return view('operations.dashboard');
})->name('operations.dashboard');

// Incident routes
Route::get('/incidents/create', [IncidentController::class, 'create'])->name('incidents.create');
Route::post('/incidents', [IncidentController::class, 'store'])->name('incidents.store');
Route::get('/incidents', [IncidentController::class, 'index'])->name('incidents.index');

// Admin review routes
Route::get('/admin/incidents/review', [IncidentController::class, 'review'])->name('admin.incidents.review');
Route::post('/admin/incidents/{incident}/review', [IncidentController::class, 'updateReview'])->name('admin.incidents.update-review');

// Incidents dashboard
Route::get('/incidents/dashboard', [IncidentController::class, 'dashboard'])->name('incidents.dashboard');
Route::get('/incidents/resolved', [IncidentController::class, 'resolved'])->name('incidents.resolved');

// Serve incident images
Route::get('/incidents/{incident}/image', [IncidentController::class, 'showImage'])->name('incidents.image');
