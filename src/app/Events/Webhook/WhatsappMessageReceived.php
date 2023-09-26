<?php

namespace App\Events\Webhook;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class WhatsappMessageReceived
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Whatsapp message as it comes from whatsapp
     *
     * @var string
     */
    public string $content;

    /**
     * Create a new event instance.
     */
    public function __construct(string $content)
    {
        $this->content = $content;

        Log::info(
            "Event: Whatsapp message received."
        );
    }
}
