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

// AI chat page and API
Route::view('/ai-chat', 'ai-chat')->name('ai.chat.page');
Route::post('/ai/chat', function (\Illuminate\Http\Request $request) {
    $validated = $request->validate(['message' => 'required|string|max:500']);
    $apiKey = env('OPENAI_API_KEY');
    if (!$apiKey) {
        return response()->json(['error' => 'AI is not configured. Set OPENAI_API_KEY in .env.'], 500);
    }
    
    // Detect which AI service to use based on API key format
    $isOpenAI = str_starts_with($apiKey, 'sk-');
    $isZai = strpos($apiKey, '.') !== false && !str_starts_with($apiKey, 'sk-');
    
    if ($isOpenAI) {
        // Standard OpenAI API
        $model = env('OPENAI_MODEL', 'gpt-4o-mini');
        $response = \Illuminate\Support\Facades\Http::withToken($apiKey)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful AI assistant for a municipal sewage issue reporting portal in Brunei Darussalam. Your role is to help citizens understand how to report sewage issues, check report status, and get emergency help.

IMPORTANT INFORMATION:
- Emergency Hotline: (673) 8377102 - Available 24/7
- Office Hours: Monday-Friday, 8:00 AM - 5:00 PM
- Response Times: Emergency issues (2-4 hours), Standard maintenance (24-48 hours)

WHAT TO REPORT:
- Sewage blockages (water backing up from drains/toilets)
- Sewage overflows (from manholes or drains onto streets)
- Strong sewage odors (persistent bad smells)
- Damaged sewer infrastructure (broken manhole covers, exposed pipes)
- Illegal dumping into sewers

HOW TO REPORT:
- Use the "Report a New Issue" button on the website
- Provide location (district, mukim, address)
- Include description and optional photos
- You will receive a report ID for tracking

HOW TO CHECK STATUS:
- Use "Check Report Status" with your report ID number

Answer questions briefly, clearly, and helpfully. Always prioritize safety - direct users to the emergency hotline for urgent health/environmental risks.'],
                ['role' => 'user', 'content' => $validated['message']],
            ],
            ]);
    } elseif ($isZai) {
        // Z.ai API
        $model = env('OPENAI_MODEL', 'glm-4.7');
        $zaiEndpoint = env('ZAI_API_ENDPOINT', 'https://api.z.ai/api/paas/v4/chat/completions');
        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->post($zaiEndpoint, [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful AI assistant for a municipal sewage issue reporting portal in Brunei Darussalam. Your role is to help citizens understand how to report sewage issues, check report status, and get emergency help.

IMPORTANT INFORMATION:
- Emergency Hotline: (555) 123-EMER (3637) - Available 24/7
- Office Hours: Monday-Friday, 8:00 AM - 5:00 PM
- Response Times: Emergency issues (2-4 hours), Standard maintenance (24-48 hours)

WHAT TO REPORT:
- Sewage blockages (water backing up from drains/toilets)
- Sewage overflows (from manholes or drains onto streets)
- Strong sewage odors (persistent bad smells)
- Damaged sewer infrastructure (broken manhole covers, exposed pipes)
- Illegal dumping into sewers

HOW TO REPORT:
- Use the "Report a New Issue" button on the website
- Provide location (district, mukim, address)
- Include description and optional photos
- You will receive a report ID for tracking

HOW TO CHECK STATUS:
- Use "Check Report Status" with your report ID number

Answer questions briefly, clearly, and helpfully. Always prioritize safety - direct users to the emergency hotline for urgent health/environmental risks.'],
                ['role' => 'user', 'content' => $validated['message']],
            ],
            'temperature' => 1,
            'stream' => false,
        ]);
    } else {
        return response()->json(['error' => 'Unrecognized API key format. Please use OpenAI (starts with sk-) or Z.ai format.'], 500);
    }
    
    if ($response->failed()) {
        $status = $response->status();
        $body = $response->body();
        $errorData = $response->json();
        $serviceName = $isOpenAI ? 'OpenAI' : 'Z.ai';
        \Log::error($serviceName . ' API Error', ['status' => $status, 'body' => $body, 'error' => $errorData]);
        
        // Provide user-friendly error messages
        if ($status === 429) {
            $errorMsg = 'Rate limit exceeded. Please wait a moment and try again.';
            if (isset($errorData['error'])) {
                $errorCode = $errorData['error']['code'] ?? '';
                $apiMessage = $errorData['error']['message'] ?? '';
                
                if ($errorCode === 'insufficient_quota' || strpos($apiMessage, 'quota') !== false || strpos($apiMessage, 'billing') !== false) {
                    $errorMsg = 'You have exceeded your OpenAI account quota. Please add payment method and credits at https://platform.openai.com/account/billing to continue using the AI service.';
                } elseif (strpos($apiMessage, 'rate limit') !== false || strpos($apiMessage, 'requests per') !== false) {
                    $errorMsg = 'Too many requests. Please wait a few seconds before trying again.';
                } else {
                    $errorMsg = 'Rate limit exceeded: ' . $apiMessage;
                }
            }
            return response()->json(['error' => $errorMsg], 429);
        } elseif ($status === 401) {
            return response()->json([
                'error' => 'Invalid API key. Please check your OPENAI_API_KEY in .env file.'
            ], 401);
        } elseif ($status === 403) {
            return response()->json([
                'error' => 'Access forbidden. Please check your API key permissions.'
            ], 403);
        } else {
            $errorMsg = isset($errorData['error']['message']) ? $errorData['error']['message'] : 'Unknown error';
            return response()->json([
                'error' => 'Failed to contact the AI service. Status: ' . $status . '. ' . $errorMsg
            ], $status);
        }
    }
    
    $data = $response->json();
    if (!isset($data['choices'][0]['message']['content'])) {
        $serviceName = $isOpenAI ? 'OpenAI' : 'Z.ai';
        \Log::error($serviceName . ' API Unexpected Response', ['data' => $data]);
        return response()->json(['error' => 'Unexpected response format from AI service.'], 500);
    }
    
    $reply = $data['choices'][0]['message']['content'];
    return response()->json(['reply' => $reply]);
})->name('ai.chat');

Route::view('/workers', 'workers')
    ->name('workers');

Route::get('/workers/work-order/{id}', function ($id) {
    $workOrder = \App\Models\WorkOrder::find($id);

    // If no work order exists (e.g. dummy "View" links for presentation), show demo data
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
    } else {
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
