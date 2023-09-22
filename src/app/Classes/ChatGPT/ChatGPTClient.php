<?php

namespace App\Classes\ChatGPT;

use App\Classes\Message\ChatGPTMessageResponse;
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
     * @return ChatGPTMessageResponse
     *
     * @throws ChatGPTClientCouldNotGetANewMessageException
     */
    public function getMessage(array $messages): ChatGPTMessageResponse
    {
        try {
            $response = $this->client->chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => $messages,
            ]);

            if (isset($response->choices[0])) {
                return new ChatGPTMessageResponse(
                    content: $response->choices[0]->message->content,
                    chatgpt_id: $response->id ?? null,
                    object_type: $response->object ?? null,
                    model: $response->model ?? null,
                    role: $response->choices[0]->message->role ?? null,
                    prompt_tokens: $response->usage->promptTokens ?? null,
                    completion_tokens: $response->usage->completionTokens ?? null,
                    total_tokens: $response->usage->totalTokens ?? null
                );
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

    /**
     * Get embeddings from OpenAI API
     *
     * @param string $text
     *
     * @return array
     *
     * @throws ChatGPTClientCouldNotGetANewMessageException
     */
    public function getEmbedding(string $text): array
    {
        try {
            $response = $this->client->embeddings()->create([
                'model' => 'text-embedding-ada-002',
                'input' => $text,
            ]);

            if (isset($response->embeddings[0])) {
                return $response->embeddings[0]->embedding;
            }

            throw new ChatGPTClientCouldNotGetANewMessageException(
                'Error ChatGPT Client: Getting embedding ' .
                'error: Wrong format in the response object from chatGPT. ' .
                'response: ' . json_encode($response)
            );

        } catch (\Throwable $e) {
            throw new ChatGPTClientCouldNotGetANewMessageException(
                'Error ChatGPT Client: Getting embedding, ' .
                'error: ' . $e->getMessage()
            );
        }
    }

}
