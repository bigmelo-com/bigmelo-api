<?php

namespace App\Classes\ChatGPT;

use App\Exceptions\ChatGPT\ChatGPTChatHistoryParserWrongMessageHistoryDataException;
use App\Exceptions\ChatGPT\ChatGPTClientCouldNotGetANewMessageException;
use App\Models\Message;
use App\Models\OpenaiTokensEmbedding;
use App\Models\ProjectEmbedding;
use App\Repositories\MessageRepository;
use Illuminate\Support\Facades\Log;
use Pgvector\Laravel\Vector;

class ChatGPTChatPromptBuilder
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
     * @var Message
     */
    private Message $message;

    /**
     * @var array
     */
    private array $messages = [];

    /**
     * Construct chatGPT messages parser
     *
     * @param Message $message
     *
     * @throws ChatGPTChatHistoryParserWrongMessageHistoryDataException
     * @throws ChatGPTClientCouldNotGetANewMessageException
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
        $this->buildPrompt();
    }

    /**
     * Build the chatgpt prompt
     *
     * @return void
     *
     * @throws ChatGPTChatHistoryParserWrongMessageHistoryDataException
     * @throws ChatGPTClientCouldNotGetANewMessageException
     */
    private function buildPrompt(): void
    {
        $lead = $this->message->lead;

        $context_messages = (
            $lead->messages()
                ->where('project_id', $this->message->project_id)
                ->where('chat_id', $this->message->chat_id)
                ->orderBy('id', 'desc')
                ->limit(6)
                ->get()
        )->toArray();

        $this->setMessageHistory($context_messages);
        $this->messages[] = ['role' => 'system', 'content' => $this->getSystemPrompt()];
        $this->messages[] = ['role' => 'user', 'content' => $this->message->content];
    }

    /**
     * Build the system prompt to answer from a specific text and context
     *
     * @return string
     *
     * @throws ChatGPTClientCouldNotGetANewMessageException
     */
    private function getSystemPrompt(): string
    {
        $project = $this->message->project;
        $new_message_text = $this->message->content;
        $content = $project->currentContent();
        $message_repository = new MessageRepository();

        $chat = new ChatGPTClient();

        $new_message_embedding = $chat->getEmbedding($new_message_text);
        $message_repository->storeChatGPTEmbeddingData($this->message->id, $new_message_embedding);

        $new_message_vector = new Vector($new_message_embedding->getEmbedding());

        $possible_text_source = ProjectEmbedding::where('project_content_id', $content ? $content->id : 0)
            ->orderByRaw('embedding <-> ?', [$new_message_vector])
            ->take(5)
            ->get();

        $current_date = new \DateTime("now", new \DateTimeZone('America/Bogota'));

        $system_content = "Current date and time with format 'Y-m-d H:i:s' is " . $current_date->format('Y-m-d H:i:s');
        $system_content .= ".\n";
        $system_content .= "You are " . $project->assistant_description . " ";
        $system_content .= "who tries " . $project->assistant_goal . ". ";

        if ($project->has_system_prompt && $possible_text_source->count() > 0) {
            $system_content .= "You obtain your knowledge about " . $project->assistant_knowledge_about . " from the
            following information delimited between three ticks";

            $system_content .= "\n\n```";

            foreach ($possible_text_source as $source) {
                $system_content .= "\n" . $source->text;
            }

            $system_content .= "\n```\n\n";

            $system_content .= "The user will ask things about he as " . $project->target_public . " ";
            $system_content .= "according to the previous text and you should reply in a concise way. If you consider
                            that the answer is not in the previous text, or it is about yourself, or about a different
                            topic, or is not a issue as " . $project->target_public .", ";
            $system_content .= "you have to answer with '" . $project->default_answer . "'. Never say you are ChatGPT
                            or something related to that.";
        }

        $system_content .= "Always reply in " . $project->language . " language.";

        return $system_content;
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
     * Return the chat prompt parsed.
     *
     * @return array
     */
    public function getChatPrompt(): array
    {
        return $this->messages;
    }
}
