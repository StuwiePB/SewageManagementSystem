<?php

namespace App\Http\Controllers\Customer;

use App\Events\SupportMessageSent;
use App\Http\Controllers\Controller;
use App\Models\SupportMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    /**
     * Get messages for the authenticated customer's support thread.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $messages = SupportMessage::where('user_id', $user->id)
            ->orderBy('created_at')
            ->get()
            ->map(fn (SupportMessage $m) => [
                'id' => $m->id,
                'message' => $m->message,
                'from_customer' => $m->from_customer,
                'image_path' => $m->image_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($m->image_path) : null,
                'created_at' => $m->created_at->toIso8601String(),
            ]);

        return response()->json(['messages' => $messages]);
    }

    /**
     * Store a new message from the customer.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'message' => ['nullable', 'string', 'max:4000'],
            'image' => ['nullable', 'string'],
        ]);

        $user = $request->user();
        $message = trim($request->input('message', ''));
        $imageData = $request->input('image');

        $imagePath = null;
        if (! empty($imageData) && preg_match('/^data:image\/(\w+);base64,(.+)$/', $imageData, $m)) {
            $decoded = base64_decode($m[2], true);
            if ($decoded !== false) {
                $filename = 'support/' . $user->id . '/' . uniqid() . '.' . $m[1];
                \Illuminate\Support\Facades\Storage::disk('public')->put($filename, $decoded);
                $imagePath = $filename;
            }
        }

        if (empty($message) && empty($imagePath)) {
            return response()->json(['error' => 'Message or image is required.'], 422);
        }

        $msg = SupportMessage::create([
            'user_id' => $user->id,
            'message' => $message ?: '[Image]',
            'from_customer' => true,
            'image_path' => $imagePath,
        ]);

        broadcast(new SupportMessageSent($msg))->toOthers();

        return response()->json([
            'message' => [
                'id' => $msg->id,
                'message' => $msg->message,
                'from_customer' => true,
                'image_path' => $imagePath ? \Illuminate\Support\Facades\Storage::disk('public')->url($imagePath) : null,
                'created_at' => $msg->created_at->toIso8601String(),
            ],
        ], 201);
    }
}
