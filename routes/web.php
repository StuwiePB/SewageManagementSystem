<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OperationsController;
use App\Http\Controllers\CrewController;
use App\Http\Controllers\WorkOrderController;

Route::get('/', function () {
    return view('publicpage');
})->name('home');

Route::view('/submitreport', 'submitreport')
    ->name('submitreport');

Route::view('/checkreportstatus', 'checkreportstatus')
    ->name('checkreportstatus');

Route::view('dashboard', 'dashboard')
    ->name('dashboard');

// Operations routes
Route::prefix('operations')->name('operations.')->group(function () {
    Route::get('/dashboard', [OperationsController::class, 'dashboard'])->name('dashboard');
    Route::get('/reports', [OperationsController::class, 'reports'])->name('reports');
    Route::get('/map', [OperationsController::class, 'map'])->name('map');
    
    // Crew Management
    Route::resource('crews', CrewController::class);
    
    // Work Orders
    Route::get('/work-orders', [WorkOrderController::class, 'index'])->name('work-orders.index');
    Route::get('/work-orders/create', [WorkOrderController::class, 'create'])->name('work-orders.create');
    Route::post('/work-orders', [WorkOrderController::class, 'store'])->name('work-orders.store');
    Route::get('/work-orders/{workOrder}', [WorkOrderController::class, 'show'])->name('work-orders.show');
    Route::patch('/work-orders/{workOrder}/status', [WorkOrderController::class, 'updateStatus'])->name('work-orders.update-status');
});
