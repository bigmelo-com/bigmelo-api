<?php

namespace App\Classes\Message;

use App\Models\Lead;
use App\Models\Message;
use App\Models\Project;
use App\Models\User;

class ChatGPTMessage
{
    /**
     * @var ChatGPTMessageResponse
     */
    private ChatGPTMessageResponse $message_response;

    /**
     * @var Lead
     */
    private Lead $lead;

    /**
     * @var Project
     */
    private Project $project;

    /**
     * @var array
     */
    private array $messages;

    /**
     * @param Lead $lead
     * @param Project $project
     *
     * @param ChatGPTMessageResponse $message_response
     */
    public function __construct(Lead $lead, Project $project, ChatGPTMessageResponse $message_response)
    {
        $this->lead = $lead;
        $this->project = $project;
        $this->message_response = $message_response;
    }

    /**
     * Save ChatGPT message
     *
     * @return void
     */
    public function save(): void
    {
        $text = $this->message_response->getContent();

        // Split the text into 100-word fragments
        $fragments = preg_split('/((?:\S+\s*){1,100})/', $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        foreach ($fragments as $fragment) {
            $message = Message::create([
                'lead_id'    => $this->lead->id,
                'project_id' => $this->project->id,
                'content'    => $fragment,
                'source'     => 'ChatGPT'
            ]);

            \App\Models\ChatgptMessage::create([
                'message_id'        => $message->id,
                'chatgpt_id'        => $this->message_response->getChatgptID(),
                'object_type'       => $this->message_response->getObjectType(),
                'model'             => $this->message_response->getModel(),
                'role'              => $this->message_response->getRole(),
                'prompt_tokens'     => $this->message_response->getPromptTokens(),
                'completion_tokens' => $this->message_response->getCompletionTokens(),
                'total_tokens'      => $this->message_response->getTotalTokens()
            ]);

            $this->messages[] = $message;
        }
    }

    /**
     * Return the message stored
     *
     * @return array
     */
    public function getMessages(): array
    {
        return $this->messages;
    }
}
