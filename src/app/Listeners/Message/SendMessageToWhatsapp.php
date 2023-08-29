<?php

namespace App\Listeners\Message;

use App\Classes\Twilio\TwilioClient;
use App\Events\Message\BigmeloMessageStored;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendMessageToWhatsapp
{
    /**
     * Handle the event.
     */
    public function handle(BigmeloMessageStored $event): void
    {
        $bigmelo_message = $event->message;

        try {
            $user = $bigmelo_message->user;

            $twilio_client = new TwilioClient(env('TWILIO_PHONE_NUMBER'));

            $twilio_client->sendMessageToWhatsapp($user->full_phone_number, $bigmelo_message->content);

            Log::info(
                'Message sent to whatsapp, ' .
                'message_id: ' . $bigmelo_message->id
            );

        } catch (\Throwable $e) {
            Log::error(
                'SendMessageToWhatsapp: Internal error, ' .
                'admin_message_id: ' . $bigmelo_message->id . ', ' .
                'error: ' . $e->getMessage()
            );
        }
    }
}
