<?php

namespace App\Listeners\Message;

use App\Classes\Twilio\TwilioClient;
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
            $user = $admin_message->user;
            $phone_number = $user->country_code . $user->phone_number;

            $twilio_client = new TwilioClient(env('TWILIO_PHONE_NUMBER'));

            $twilio_client->sendMessageToWhatsapp($phone_number, $admin_message->content);

            Log::info(
                'Message sent to whatsapp, ' .
                'message_id: ' . $admin_message->id
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
