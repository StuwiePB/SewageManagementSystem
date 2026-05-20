<?php

namespace App\Http\Controllers\Admin;

use App\Events\SupportChatTerminated;
use App\Events\SupportMessageSent;
use App\Http\Controllers\Controller;
use App\Models\SupportMessage;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    /**
     * Admin UI for live customer support chat.
     */
    public function chat(): View
    {
        return view('r_admin.customerchat');
    }

    /**
     * List all support conversations (customers who have sent at least one message).
     */
    public function index(Request $request): JsonResponse
    {
        $userIds = SupportMessage::distinct()->pluck('user_id');
        $users = User::whereIn('id', $userIds)->get();

        $conversations = [];
        foreach ($users as $user) {
            $lastMessage = SupportMessage::where('user_id', $user->id)->latest()->first();
            $customerMsgCount = (int) SupportMessage::where('user_id', $user->id)->where('from_customer', true)->count();
            $conversations[] = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email ?? '',
                'message_count' => $customerMsgCount,
                'last_message_at' => $lastMessage ? $lastMessage->created_at->toIso8601String() : null,
                'last_preview' => $lastMessage ? \Illuminate\Support\Str::limit($lastMessage->message, 50) : null,
            ];
        }

        return response()->json(['conversations' => $conversations]);
    }

    /**
     * Get messages for a specific customer's support thread.
     */
    public function messages(Request $request, int $userId): JsonResponse
    {
        $messages = SupportMessage::where('user_id', $userId)
            ->orderBy('created_at')
            ->get()
            ->map(fn (SupportMessage $m) => [
                'id' => $m->id,
                'message' => $m->message,
                'from_customer' => $m->from_customer,
                'image_path' => $m->image_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($m->image_path) : null,
                'created_at' => $m->created_at->toIso8601String(),
            ]);

        $user = User::find($userId);

        return response()->json([
            'messages' => $messages,
            'customer' => $user ? ['id' => $user->id, 'name' => $user->name, 'email' => $user->email ?? ''] : null,
        ]);
    }

    /**
     * Send a reply to a customer.
     */
    public function reply(Request $request, int $userId): JsonResponse
    {
        $request->validate([
            'message' => ['required', 'string', 'max:4000'],
        ]);

        $user = User::find($userId);
        if (! $user) {
            return response()->json(['error' => 'Customer not found.'], 404);
        }

        $msg = SupportMessage::create([
            'user_id' => $userId,
            'message' => trim($request->input('message')),
            'from_customer' => false,
        ]);

        broadcast(new SupportMessageSent($msg))->toOthers();

        return response()->json([
            'message' => [
                'id' => $msg->id,
                'message' => $msg->message,
                'from_customer' => false,
                'image_path' => null,
                'created_at' => $msg->created_at->toIso8601String(),
            ],
        ], 201);
    }

    /**
     * Terminate a chat — delete all messages and notify the customer in real-time.
     */
    public function terminate(Request $request, int $userId): JsonResponse
    {
        $user = User::find($userId);
        if (! $user) {
            return response()->json(['error' => 'Customer not found.'], 404);
        }

        $imagePaths = SupportMessage::where('user_id', $userId)
            ->whereNotNull('image_path')
            ->pluck('image_path');

        foreach ($imagePaths as $path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
        }

        \Illuminate\Support\Facades\Storage::disk('public')->deleteDirectory('support/'.$userId);

        SupportMessage::where('user_id', $userId)->delete();

        broadcast(new SupportChatTerminated($userId));

        return response()->json(['terminated' => true]);
    }
}
