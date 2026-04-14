<?php

namespace App\Events;

use App\Models\SupportMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class SupportMessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $payload;

    public function __construct(public SupportMessage $supportMessage)
    {
        $this->payload = [
            'id' => $supportMessage->id,
            'message' => $supportMessage->message,
            'from_customer' => (bool) $supportMessage->from_customer,
            'image_path' => $supportMessage->image_path ? Storage::disk('public')->url($supportMessage->image_path) : null,
            'created_at' => $supportMessage->created_at->toIso8601String(),
        ];
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('support.'.$this->supportMessage->user_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'SupportMessageSent';
    }

    public function broadcastWith(): array
    {
        return $this->payload;
    }
}
