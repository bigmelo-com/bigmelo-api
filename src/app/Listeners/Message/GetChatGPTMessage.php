<?php

namespace App\Listeners\Message;

use App\Classes\ChatGPT\ChatGPTChatHistoryParser;
use App\Classes\ChatGPT\ChatGPTClient;
use App\Classes\Message\ChatGPTMessage;
use App\Events\Message\BigmeloMessageStored;
use App\Events\Message\UserMessageStored;
use App\Models\Message;
use App\Models\ProjectEmbedding;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Pgvector\Laravel\Vector;

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

            if ($user_message->source == 'WhatsApp' && $user_message->whatsapp_message->media_content_type != null) {
                $message = Message::create([
                    'user_id' => $user_message->user_id,
                    'content' => config('bigmelo.message.wrong_media_type'),
                    'source'  => 'Admin'
                ]);

                event(new BigmeloMessageStored($message));

                Log::info(
                    "Listener: Get ChatGPT Message, " .
                    "issue: Wrong media type sent from WhatsApp, " .
                    "message_id: " . $message->id
                );
            }

            $chat = new ChatGPTClient();

            // History of messages, context
            $old_messages = ($user->messages()->orderBy('id', 'desc')->limit(5)->get())->toArray();

            // New message wrote by the "user"
            $new_message = $user_message->content;

            // ---------------------------------------------------------------------------------------------------
            // Code to get similarities by embedding

            $new_message_vector = new Vector($chat->getEmbedding($new_message));
            $possible_text_source = ProjectEmbedding::orderByRaw('embedding <-> ?', [$new_message_vector])->take(5)->get();
            $text_source = [];

            $system_content = "You are an official of the Superintendency of Industry and Commerce who seeks to advise
            citizens on their consumer concerns. You obtain your knowledge about consumer rights and duties from the
            following information delimited between three ticks";

            $system_content .= "\n\n```";

            foreach ($possible_text_source as $source) {
                $system_content .= "\n" . $source->text;
            }

            $system_content .= "\n```\n\nThe user will ask you about things about he as a consumer and
            you should reply in a concise way. Always reply in Spanish language. If you consider that the answer is not
            in the previous text, or is not a issue as consumer, you have to answer with
            'Estoy aqui solo para resolver tus dudas como consumidor'";

            // ---------------------------------------------------------------------------------------------------

            // Props to get new ChatGPT message
            $chat_history_parser = new ChatGPTChatHistoryParser($old_messages, $new_message, $system_content);

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
