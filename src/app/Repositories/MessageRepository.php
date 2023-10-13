<?php

namespace App\Repositories;

use App\Classes\ChatGPT\ChatGPTEmbedding;
use App\Exceptions\Repositories\MessageRepositoryCouldNotStoreANewMessage;
use App\Models\Chat;
use App\Models\Message;
use App\Models\OpenaiTokensEmbedding;

class MessageRepository
{
    /**
     * Store a message including an active chat
     *
     * @param int $lead_id
     * @param int $project_id
     * @param string $content
     * @param string $source
     *
     * @return Message
     *
     * @throws MessageRepositoryCouldNotStoreANewMessage
     */
    public function storeMessage(int $lead_id, int $project_id, string $content, string $source): Message
    {
        try {
            $chat = Chat::where('lead_id', $lead_id)
                ->where('project_id', $project_id)
                ->where('active', true)
                ->first();

            if (!$chat) {
                $chat = Chat::create([
                    'lead_id' => $lead_id,
                    'project_id' => $project_id,
                    'active' => true
                ]);
            }

            return Message::create([
                'lead_id' => $lead_id,
                'project_id' => $project_id,
                'chat_id' => $chat->id,
                'content' => $content,
                'source' => $source
            ]);

        } catch (\Throwable $e) {

            throw new MessageRepositoryCouldNotStoreANewMessage($e->getMessage());
        }
    }

    /**
     * Store extra embedding data
     *
     * @param int $message_id
     * @param ChatGPTEmbedding $embedding
     *
     * @return void
     */
    public function storeChatGPTEmbeddingData(int $message_id, ChatGPTEmbedding $embedding): void
    {
        OpenaiTokensEmbedding::create([
            'message_id'    => $message_id,
            'prompt_tokens' => $embedding->getPromptTokens(),
            'total_tokens'  => $embedding->getTotalTokens()
        ]);
    }
}
