<?php

namespace App\Listeners\Message;

use App\Classes\ChatGPT\ChatGPTClient;
use App\Events\Message\BigmeloMessageStored;
use App\Events\Message\UserMessageStored;
use App\Models\Message;
use Illuminate\Support\Facades\Log;

class GetChatGPTMessage
{
    /**
     * Handle the event.
     */
    public function handle(UserMessageStored $event): void
    {
        $user_message = $event->message;

        try {
            $chat = new ChatGPTClient();

            $chatpgt_message = $chat->getMessage($user_message->content . '. La respuesta maximo de 1000 caracteres.');

            $message = Message::create([
                'user_id' => $user_message->user_id,
                'content' => $chatpgt_message,
                'source'  => 'ChatGPT'
            ]);

            event(new BigmeloMessageStored($message));

            Log::info(
                "Listener: Get ChatGPT Message, " .
                "message_id: " . $message->id
            );

        } catch (\Throwable $e) {
            Log::error(
                'GetChatGPTMessage: Internal error, ' .
                'user_message_id: ' . $user_message->id . ', ' .
                'error: ' . $e->getMessage()
            );
        }
    }
}
