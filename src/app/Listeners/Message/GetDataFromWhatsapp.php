<?php

namespace App\Listeners\Message;

use App\Classes\ChatGPT\ChatGPTClient;
use App\Events\Message\UserMessageStored;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class GetDataFromWhatsapp implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(UserMessageStored $event): void
    {
        $lead_message = $event->message;
        $lead = $lead_message->lead;

        try {

            $chat = new ChatGPTClient();

            $chat_prompt = [
                ['role' => 'system', 'content' => "Don't make assumptions about what values to plug into functions. Ask for clarification if a user request is ambiguous."],
                ['role' => 'user', 'content' => $lead_message->content],
                ['role' => 'user', 'content' => 'Get my first name, last name and email']
            ];

            $functions = [
                    [
                        'name' => 'get_user_basic_information',
                        'description' => 'Get user basic information',
                        'parameters' => [
                            'type' => 'object',
                            'properties' => [
                                'first_name' => [
                                    'type' => 'string',
                                    'description' => 'User\'s first name',
                                ],
                                'last_name' => [
                                    'type' => 'string',
                                    'description' => 'User\'s last name',
                                ],
                                'email' => [
                                    'type' => 'string',
                                    'description' => 'User\'s email',
                                ],
                            ],
                        ],
                    ]
                ];

            // Get new chatGPT message
            $chatgpt_message_response = $chat->getMessage($chat_prompt, $functions);

            if ($chatgpt_message_response->getFunctionCallName() == 'get_user_basic_information') {
                $arguments =  json_decode($chatgpt_message_response->getFunctionCallArguments(), true);
                $lead->first_name =  $arguments['first_name'] ?? $lead->first_name;
                $lead->last_name =  $arguments['last_name'] ?? $lead->last_name;
                $lead->email =  $arguments['email'] ?? $lead->email;
                $lead->save();
            }

            Log::info(
                "Listener: Get Data from Whatsapp, " .
                "message_id: " . $chatgpt_message_response->getChatgptID()
            );

        } catch (\Throwable $e) {
            Log::error(
                'GetChatGPTMessage: Internal error, ' .
                'user_message_id: ' . $lead_message->id . ', ' .
                'error: ' . $e->getMessage()
            );
        }
    }
}
