<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Support\BruneiPhone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    public function submit(Request $request): JsonResponse|RedirectResponse
    {
        $isGuest = ! $request->user();

        $request->validate([
            'problem_type' => ['required', 'string', 'max:255'],
            'reporter_name' => $isGuest ? ['required', 'string', 'max:255'] : ['nullable', 'string', 'max:255'],
            'phone' => [
                $isGuest ? 'required' : 'nullable',
                'string',
                'max:30',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $value = (string) $value;
                    if (! BruneiPhone::isValid($value)) {
                        $fail('Please enter a valid Brunei phone number (+673...).');
                    }
                },
            ],
            'severity' => ['nullable', 'string', 'in:urgent,nonurgent'],
            'description' => ['nullable', 'string', 'max:2000'],
            'address' => ['nullable', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'photo' => ['nullable', 'string'],
        ]);

        $user = $request->user();
        $photoPath = null;
        $storageSubdir = $user ? (string) $user->id : 'guest';

        if ($photo = $request->input('photo')) {
            if (preg_match('/^data:image\/(\w+);base64,(.+)$/', $photo, $m)) {
                $ext = $m[1] === 'jpeg' ? 'jpg' : $m[1];
                $data = base64_decode($m[2], true);
                if ($data !== false && strlen($data) < 5 * 1024 * 1024) {
                    $filename = 'reports/'.$storageSubdir.'/'.Str::ulid().'.'.$ext;
                    Storage::disk('public')->put($filename, $data);
                    $photoPath = $filename;
                }
            }
        }

        $report = Report::create([
            'reference_code' => Report::generateReferenceCode(),
            'user_id' => $user?->id,
            'reporter_name' => $request->filled('reporter_name') ? $request->reporter_name : null,
            'phone' => $request->filled('phone') ? BruneiPhone::normalize((string) $request->phone) : null,
            'problem_type' => $request->problem_type,
            'severity' => $request->filled('severity') ? $request->severity : null,
            'description' => $request->description,
            'address' => $request->address,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'photo_path' => $photoPath,
            'status' => Report::STATUS_PENDING,
        ]);

        if ($request->expectsJson()) {
            $senderName = $report->reporter_name
                ?? ($user?->name)
                ?? __('Guest User');

            return response()->json([
                'ok' => true,
                'reference_code' => $report->reference_code,
                'report_created_at' => $report->created_at->toIso8601String(),
                'report_time' => $report->created_at->format('H:i:s'),
                'sender_name' => $senderName,
                'redirect_url' => $isGuest
                    ? route('guest.explore')
                    : route('customer.dashboard', ['name' => $user->profileSlug()]),
            ]);
        }

        if ($isGuest) {
            return redirect()
                ->route('guest.explore')
                ->with('status', 'report-submitted');
        }

        return redirect()
            ->route('customer.dashboard', ['name' => $user->profileSlug()])
            ->with('status', 'report-submitted');
    }
}
