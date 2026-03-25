<?php

namespace App\Events\Chat;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ChatMessage $message,
        public int $conversationId,
        public int $recipientId
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.'.$this->recipientId),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'message' => [
                'id' => $this->message->id,
                'conversation_id' => $this->conversationId,
                'message' => $this->message->message,
                'sender_id' => $this->message->sender_id,
                'sender_name' => $this->message->sender->name,
                'is_read' => $this->message->is_read,
                'created_at' => $this->message->created_at->format('H:i'),
                'created_at_full' => $this->message->created_at->toIso8601String(),
            ],
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }
}
