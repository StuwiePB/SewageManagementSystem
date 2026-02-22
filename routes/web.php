<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OperationsController;
use App\Http\Controllers\CrewController;
use App\Http\Controllers\WorkOrderController;
use App\Http\Controllers\PublicReportController;
use App\Http\Controllers\AiChatController;

Route::get('/', function () {
    $districts = config('brunei.districts', []);
    $mukims = config('brunei.mukims', []);
    return view('publicpage', compact('districts', 'mukims'));
})->name('home');

Route::get('/submitreport', [PublicReportController::class, 'create'])->name('submitreport');
Route::post('/submitreport', [PublicReportController::class, 'store'])->name('reports.store');

Route::view('/checkreportstatus', 'checkreportstatus')
    ->name('checkreportstatus');

Route::view('dashboard', 'dashboard')
    ->name('dashboard');

// AI chat page and API (always returns reply - uses OpenAI from .env when available, fallback otherwise)
Route::view('/ai-chat', 'ai-chat')->name('ai.chat.page');
Route::post('/ai/chat', [AiChatController::class, 'chat'])->name('ai.chat');

Route::view('/workers', 'workers')
    ->name('workers');

$dummyWorkOrders = [
    1 => [
        'work_order_number' => 'WO-1042',
        'type' => 'Blockage',
        'priority' => 'high',
        'location_address' => 'Maple St',
        'district' => 'brunei-muara',
        'mukim' => null,
        'latitude' => 4.9012,
        'longitude' => 114.9089,
        'description' => 'Main line blockage; pump and clear.',
        'created_at' => now()->setTime(8, 39),
        'assigned_at' => now()->setTime(9, 15),
    ],
    2 => [
        'work_order_number' => 'WO-1041',
        'type' => 'Inspection',
        'priority' => 'medium',
        'location_address' => 'Oak Ave',
        'district' => 'brunei-muara',
        'mukim' => null,
        'latitude' => 4.9120,
        'longitude' => 114.9200,
        'description' => 'Routine inspection of sewer segment.',
        'created_at' => now()->setTime(7, 0),
        'assigned_at' => now()->setTime(8, 0),
    ],
    3 => [
        'work_order_number' => 'WO-1040',
        'type' => 'Repair',
        'priority' => 'low',
        'location_address' => 'Pine Rd',
        'district' => 'brunei-muara',
        'mukim' => null,
        'latitude' => 4.8950,
        'longitude' => 114.8950,
        'description' => 'Repair damaged manhole cover.',
        'created_at' => now()->subDay()->setTime(14, 30),
        'assigned_at' => now()->subDay()->setTime(15, 0),
    ],
];
Route::get('/workers/work-order/{id}', function ($id) use ($dummyWorkOrders) {
    $workOrder = \App\Models\WorkOrder::find($id);

    if (!$workOrder && isset($dummyWorkOrders[$id])) {
        $workOrder = \App\Models\WorkOrder::make($dummyWorkOrders[$id]);
    }
    if (!$workOrder) {
        $workOrder = \App\Models\WorkOrder::first()
            ?? \App\Models\WorkOrder::make([
                'work_order_number' => 'WO-1042',
                'type' => 'Emergency Overflow Response',
                'priority' => 'critical',
                'location_address' => 'Jalan Gadong, near commercial area',
                'district' => 'brunei-muara',
                'mukim' => 'gadong-a',
                'latitude' => 4.9012,
                'longitude' => 114.9089,
                'description' => 'Contain overflow, deploy pumps, and clear main line.',
                'created_at' => now()->setTime(8, 39),
                'assigned_at' => now()->setTime(6, 39),
            ]);
        if ($workOrder->exists) {
            $workOrder->load(['crew', 'report']);
        }
    } else if ($workOrder->exists) {
        $workOrder->load(['crew', 'report']);
    }

    return view('worker-work-order', compact('workOrder'));
})->name('workers.work-order.show');

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
