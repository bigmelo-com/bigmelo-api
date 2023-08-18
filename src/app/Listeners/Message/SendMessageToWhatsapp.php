<?php

namespace App\Listeners\Message;

use App\Events\Message\AdminMessageStored;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendMessageToWhatsapp
{
    /**
     * Handle the event.
     */
    public function handle(AdminMessageStored $event): void
    {
        $admin_message = $event->message;

        try {
            Log::info(
                'Message sent to whatsapp, ' .
                'message_id: ' //. $message->id
            );

        } catch (\Throwable $e) {
            Log::error(
                'SendMessageToWhatsapp: Internal error, ' .
                'admin_message_id: ' . $admin_message->id . ', ' .
                'error: ' . $e->getMessage()
            );
        }
    }
}
