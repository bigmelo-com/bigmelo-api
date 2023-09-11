<?php

namespace App\Listeners\Message;

use App\Classes\ChatGPT\ChatGPTChatHistoryParser;
use App\Classes\ChatGPT\ChatGPTClient;
use App\Classes\Message\ChatGPTMessage;
use App\Events\Message\BigmeloMessageStored;
use App\Events\Message\UserMessageStored;
use App\Models\Message;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class GetChatGPTMessage implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The number of times the queued listener may be attempted.
     *
     * @var int
     */
    public int $tries = 3;

    /**
     * The number of seconds before the job should be retried.
     *
     * @var int
     */
    public int $retryAfter = 10;

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

            // History of messages, context
            $old_messages = ($user->messages()->orderBy('id', 'desc')->limit(20)->get())->toArray();

            // New message wrote by the "user"
            $new_message = $user_message->content;

            // Props to get new ChatGPT message
            $chat_history_parser = new ChatGPTChatHistoryParser($old_messages, $new_message);

            $chat = new ChatGPTClient();

            // Get new chatGPT message
            $chatgpt_message_response = $chat->getMessage($chat_history_parser->getChatHistory());

            // Save message as a ChatGPT message
            $chatgpt_message = new ChatGPTMessage($user->id, $chatgpt_message_response);
            $chatgpt_message->save();

            $stored_messages = $chatgpt_message->getMessages();

            foreach ($stored_messages as $message) {
                event(new BigmeloMessageStored($message));

                Log::info(
                    "Listener: Get ChatGPT Message, " .
                    "message_id: " . $message->id
                );
            }

        } catch (\Throwable $e) {
            Log::error(
                'GetChatGPTMessage: Internal error, ' .
                'user_message_id: ' . $user_message->id . ', ' .
                'error: ' . $e->getMessage()
            );
        }
    }
}
