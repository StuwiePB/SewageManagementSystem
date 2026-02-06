<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OperationsController;
use App\Http\Controllers\CrewController;
use App\Http\Controllers\WorkOrderController;
use App\Http\Controllers\PublicReportController;

Route::get('/', function () {
    return view('publicpage');
})->name('home');

Route::get('/submitreport', [PublicReportController::class, 'create'])->name('submitreport');
Route::post('/submitreport', [PublicReportController::class, 'store'])->name('reports.store');

Route::view('/checkreportstatus', 'checkreportstatus')
    ->name('checkreportstatus');

Route::view('dashboard', 'dashboard')
    ->name('dashboard');

// Redirect old operations URL to new one (so bookmarks still work)
Route::get('/operation.dashboard', function () {
    return redirect()->route('operations.dashboard', [], 301);
});

// Operations routes – use /operations/dashboard (with 's' and a slash)
Route::prefix('operations')->name('operations.')->group(function () {
    Route::get('/dashboard', [OperationsController::class, 'dashboard'])->name('dashboard');
    Route::get('/reports', [OperationsController::class, 'reports'])->name('reports');
    Route::get('/map', [OperationsController::class, 'map'])->name('map');
    
    // Crew Management
    Route::get('/crews/unassigned', [CrewController::class, 'unassigned'])->name('crews.unassigned');
    Route::post('/crews/assign-worker', [CrewController::class, 'assignWorker'])->name('crews.assign-worker');
    Route::resource('crews', CrewController::class);
    
    // Work Orders
    Route::get('/work-orders', [WorkOrderController::class, 'index'])->name('work-orders.index');
    Route::get('/work-orders/create', [WorkOrderController::class, 'create'])->name('work-orders.create');
    Route::post('/work-orders', [WorkOrderController::class, 'store'])->name('work-orders.store');
    Route::get('/work-orders/{workOrder}', [WorkOrderController::class, 'show'])->name('work-orders.show');
    Route::patch('/work-orders/{workOrder}/status', [WorkOrderController::class, 'updateStatus'])->name('work-orders.update-status');
});
