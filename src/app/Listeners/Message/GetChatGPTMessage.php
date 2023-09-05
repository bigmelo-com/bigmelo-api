<?php

namespace App\Listeners\Message;

use App\Classes\ChatGPT\ChatGPTChatHistoryParser;
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
        $user = $user_message->user;

        try {

            if (!$user->hasAvailableMessages()) {
                $message = Message::create([
                    'user_id' => $user_message->user_id,
                    'content' => config('bigmelo.message.no_available_messages'),
                    'source'  => 'Admin'
                ]);

                event(new BigmeloMessageStored($message));

                Log::info(
                    "Listener: Get ChatGPT Message, " .
                    "issue: Messages limit exceeded for the user, " .
                    "message_id: " . $message->id
                );

                return;
            }

            $old_messages = ($user->messages()->orderBy('id', 'desc')->limit(20)->get())->toArray();
            $new_message = $user_message->content;

            $chat_history_parser = new ChatGPTChatHistoryParser($old_messages, $new_message);
            $chat = new ChatGPTClient();

            $chatpgt_message = $chat->getMessage($chat_history_parser->getChatHistory());

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
