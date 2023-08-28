<?php

namespace App\Events\Message;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class WhatsAppMessageStored
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var Message
     */
    public Message $message;

    /**
     * Create a new event instance
     *
     * @param Message $message
     *
     * @return void
     */
    public function __construct(Message $message)
    {
        $this->message = $message;

        Log::info(
            "Event: WhatsApp Message stored, " .
            "message_id: " . $message->id
        );
    }
}
