<?php

namespace App\Classes\ChatGPT;

use App\Classes\Message\ChatGPTMessageResponse;
use App\Exceptions\ChatGPT\ChatGPTClientCouldNotGetANewMessageException;
use App\Exceptions\ChatGPT\ChatGPTClientCouldNotTranscribeMessageException;

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
     * @param array $functions
     * @return ChatGPTMessageResponse
     *
     * @throws ChatGPTClientCouldNotGetANewMessageException
     */
    public function getMessage(array $messages, array $functions = []): ChatGPTMessageResponse
    {
        try {

            $response = $this->client->chat()->create(
                [
                    'model' => 'gpt-3.5-turbo',
                    'messages' => $messages,
                ] + ($functions ?
                ['functions' => $functions] : [])
            );

            if (isset($response->choices[0])) {
                return new ChatGPTMessageResponse(
                    content: $response->choices[0]->message->content ?? '',
                    function_call_name: $response->choices[0]->message->functionCall->name ?? null,
                    function_call_arguments: $response->choices[0]->message->functionCall->arguments ?? null,
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
     * @return ChatGPTEmbedding
     *
     * @throws ChatGPTClientCouldNotGetANewMessageException
     */
    public function getEmbedding(string $text): ChatGPTEmbedding
    {
        try {
            $response = $this->client->embeddings()->create([
                'model' => 'text-embedding-ada-002',
                'input' => $text,
            ]);

            if (isset($response->embeddings[0])) {
                return new ChatGPTEmbedding(
                    embedding: $response->embeddings[0]->embedding,
                    prompt_tokens: $response->usage->promptTokens ?? null,
                    total_tokens: $response->usage->totalTokens ?? null
                );
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

    /**
     * Get message text from audio file
     *
     * @param string $file_path
     * @return mixed
     *
     * @throws ChatGPTClientCouldNotTranscribeMessageException
     */
    public function getTextFromAudioFile(string $file_path)
    {
        try {
            $response = $this->client->audio()->transcribe([
                'model' => 'whisper-1',
                'file' => fopen($file_path, 'r')
            ]);

            if (!empty($response['text'])) {
                return $response['text'];
            }

            throw new ChatGPTClientCouldNotTranscribeMessageException(
                'Error ChatGPT Client: Transcribing message, ' .
                'error: No message found.'
            );

        } catch (\Throwable $e) {
            throw new ChatGPTClientCouldNotTranscribeMessageException(
                'Error ChatGPT Client: Transcribing message, ' .
                'error: ' . $e->getMessage()
            );
        }
    }

}
