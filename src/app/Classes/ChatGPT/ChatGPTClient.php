<?php

namespace App\Classes\ChatGPT;

use App\Exceptions\ChatGPT\ChatGPTClientCouldNotGetANewMessageException;

class ChatGPTClient
{
    /**
     * @var \OpenAI\Client
     */
    private $client;

    /**
     * Init chat gpt client
     */
    public function __construct()
    {
        $api_key = config('bigmelo.chat_gpt.api_key');
        $this->client = \OpenAI::client($api_key);
    }

    /**
     * Get message from ChatGPT Client
     *
     * @param array $messages
     * @return string
     *
     * @throws ChatGPTClientCouldNotGetANewMessageException
     */
    public function getMessage(array $messages): string
    {
        try {
            $response = $this->client->chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => $messages,
            ]);

            if (isset($response->choices[0])) {
                return $response->choices[0]->message->content;
            }

            throw new ChatGPTClientCouldNotGetANewMessageException(
                'Error ChatGPT Client, ' .
                'error: Wrong format in the response object from chatGPT. ' .
                'response: ' . json_encode($response)
            );

        } catch (\Throwable $e) {
            throw new ChatGPTClientCouldNotGetANewMessageException(
                'Error ChatGPT Client, ' .
                'error: ' . $e->getMessage()
            );
        }
    }

}
