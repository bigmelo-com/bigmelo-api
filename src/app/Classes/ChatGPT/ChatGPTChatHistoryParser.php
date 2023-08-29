<?php

namespace App\Classes\ChatGPT;

use App\Exceptions\ChatGPT\ChatGPTChatHistoryParserWrongMessageHistoryDataException;

class ChatGPTChatHistoryParser
{
    /**
     * @var array|string[]
     */
    private array $source = [
        'ChatGPT'   => 'assistant',
        'Admin'     => 'assistant',
        'API'       => 'user',
        'WhatsApp'  => 'user'
    ];

    /**
     * @var array
     */
    private array $messages = [];

    /**
     * Construct chatGPT messages parser
     *
     * @param array $message_history
     * @param string $new_message
     *
     * @throws ChatGPTChatHistoryParserWrongMessageHistoryDataException
     */
    public function __construct(array $message_history, string $new_message)
    {
        $this->setMessageHistory($message_history);
        $this->messages[] = ['role' => 'user', 'content' => $new_message];
    }

    /**
     * Set the chat history in the right format
     *
     * @param array $message_history
     *
     * @return void
     *
     * @throws ChatGPTChatHistoryParserWrongMessageHistoryDataException
     */
    private function setMessageHistory(array $message_history): void
    {
        try {
            for ($i = count($message_history) - 1; $i >= 0; $i--) {
                $role = $this->source[$message_history[$i]['source']];
                $content = (string)$message_history[$i]['content'];

                $this->messages[] = ['role' =>$role, 'content' => $content];
            }
        } catch (\Throwable $e) {
            throw new ChatGPTChatHistoryParserWrongMessageHistoryDataException($e->getMessage());
        }
    }

    /**
     * Return the chat history parsed.
     *
     * @return array
     */
    public function getChatHistory(): array
    {
        return $this->messages;
    }
}
