<?php

namespace App\Listeners\Message;

use App\Classes\ChatGPT\ChatGPTChatPromptBuilder;
use App\Classes\ChatGPT\ChatGPTClient;
use App\Classes\Message\ChatGPTMessage;
use App\Events\Message\BigmeloMessageStored;
use App\Events\Message\UserMessageStored;
use App\Models\Message;
use App\Repositories\MessageRepository;
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
        $lead_message = $event->message;
        $lead = $lead_message->lead;
        $project = $lead_message->project;
        $message_repository = new MessageRepository();

        try {

            if ($lead_message->source == 'WhatsApp' && $lead_message->whatsapp_message->media_content_type != null) {
                $message = $message_repository->storeMessage(
                    lead_id: $lead_message->lead_id,
                    project_id: $lead_message->project_id,
                    content: config('bigmelo.message.wrong_media_type'),
                    source: 'Admin'
                );

                event(new BigmeloMessageStored($message));

                Log::info(
                    "Listener: Get ChatGPT Message, " .
                    "issue: Wrong media type sent from WhatsApp, " .
                    "message_id: " . $message->id
                );
            }

            if($lead->remaining_messages == 0){
                $issue = "No hay mas mensajes disponibles";
                if($lead->user_id){
                    $message = $message_repository->storeMessage(
                        lead_id: $lead_message->lead_id,
                        project_id: $lead_message->project_id,
                        content: config('bigmelo.message.no_available_messages'),
                        source: 'Admin'
                    );
                    $issue = config('bigmelo.message.no_available_messages');
                } else {
                    $message = $message_repository->storeMessage(
                        lead_id: $lead_message->lead_id,
                        project_id: $lead_message->project_id,
                        content: config('bigmelo.message.no_available_messages_unregistered_user'),
                        source: 'Admin'
                    );
                    $issue = config('bigmelo.message.no_available_messages_unregistered_user');
                }

                event(new BigmeloMessageStored($message));

                Log::info(
                    "Listener: Get ChatGPT Message, " .
                    "issue:" . $issue . ', ' .
                    "message_id: " . $message->id
                );

               return;
            }

            $chat = new ChatGPTClient();

            // Prompt to get new ChatGPT message
            $chat_history_builder = new ChatGPTChatPromptBuilder($lead_message);

            // Get new chatGPT message
            $chatgpt_message_response = $chat->getMessage($chat_history_builder->getChatPrompt());

            // Save message as a ChatGPT message
            $chatgpt_message = new ChatGPTMessage($lead, $project, $chatgpt_message_response);
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
                'user_message_id: ' . $lead_message->id . ', ' .
                'error: ' . $e->getMessage()
            );
        }
    }
}
