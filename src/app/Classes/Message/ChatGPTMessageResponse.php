<?php

namespace App\Classes\Message;

class ChatGPTMessageResponse extends MessageResponse
{
    /**
     * @var string|null
     */
    private ?string $function_call_name;

    /**
     * @var string|null
     */
    private ?string $function_call_arguments;

    /**
     * @var string|null
     */
    private ?string $chatgpt_id;

    /**
     * @var string|null
     */
    private ?string $object_type;

    /**
     * @var string|null
     */
    private ?string $model;

    /**
     * @var string|null
     */
    private ?string $role;

    /**
     * @var int|null
     */
    private ?int $prompt_tokens;

    /**
     * @var int|null
     */
    private ?int $completion_tokens;

    /**
     * @var int|null
     */
    private ?int $total_tokens;

    /**
     * @param string $content
     * @param string|null $object_type
     */
    public function __construct(
        string $content,
        ?string $function_call_name = null,
        ?string $function_call_arguments = null,
        ?string $chatgpt_id = null,
        ?string $object_type = null,
        ?string $model = null,
        ?string $role = null,
        ?int $prompt_tokens = null,
        ?int $completion_tokens = null,
        ?int $total_tokens = null
    )
    {
        parent::__construct($content);

        $this->function_call_name = $function_call_name;
        $this->function_call_arguments = $function_call_arguments;
        $this->chatgpt_id = $chatgpt_id;
        $this->object_type = $object_type;
        $this->model = $model;
        $this->role = $role;
        $this->prompt_tokens = $prompt_tokens;
        $this->completion_tokens = $completion_tokens;
        $this->total_tokens = $total_tokens;
    }

    /**
     * ChatGPT function call name
     *
     * @return string|null
     */
    public function getFunctionCallName(): ?string
    {
        return $this->function_call_name;
    }

    /**
     * ChatGPT id
     *
     * @return string|null
     */
    public function getFunctionCallArguments(): ?string
    {
        return $this->function_call_arguments;
    }

    /**
     * ChatGPT id
     *
     * @return string|null
     */
    public function getChatgptID(): ?string
    {
        return $this->chatgpt_id;
    }

    /**
     * ChatGPT message object type
     *
     * @return string|null
     */
    public function getObjectType(): ?string
    {
        return $this->object_type;
    }

    /**
     * ChatGPT language model
     *
     * @return string|null
     */
    public function getModel(): ?string
    {
        return $this->model;
    }

    /**
     * ChatGPT role in the message
     *
     * @return string|null
     */
    public function getRole(): ?string
    {
        return $this->role;
    }

    /**
     * ChatGPT prompt tokens in the message
     *
     * @return int|null
     */
    public function getPromptTokens(): ?int
    {
        return $this->prompt_tokens;
    }

    /**
     * ChatGPT completion tokens in the message
     *
     * @return int|null
     */
    public function getCompletionTokens(): ?int
    {
        return $this->completion_tokens;
    }

    /**
     * ChatGPT total tokens in the message
     *
     * @return int|null
     */
    public function getTotalTokens(): ?int
    {
        return $this->total_tokens;
    }
}
