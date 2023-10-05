<?php

namespace App\Classes\ChatGPT;

class ChatGPTEmbedding
{
    /**
     * @var array
     */
    private array $embedding;

    /**
     * @var int|null
     */
    private ?int $prompt_tokens;

    /**
     * @var int|null
     */
    private ?int $total_tokens;

    /**
     * Constructor for embedding from chat gpt
     *
     * @param array $embedding
     * @param int|null $prompt_tokens
     * @param int|null $total_tokens
     */
    public function __construct(array $embedding, ?int $prompt_tokens = null, ?int $total_tokens = null)
    {
        $this->embedding = $embedding;
        $this->prompt_tokens = $prompt_tokens;
        $this->total_tokens = $total_tokens;
    }

    /**
     * Embedding array
     *
     * @return array
     */
    public function getEmbedding(): array
    {
        return $this->embedding;
    }

    /**
     * ChatGPT prompt tokens in the embedding
     *
     * @return int|null
     */
    public function getPromptTokens(): ?int
    {
        return $this->prompt_tokens;
    }

    /**
     * ChatGPT total tokens in the embedding
     *
     * @return int|null
     */
    public function getTotalTokens(): ?int
    {
        return $this->total_tokens;
    }
}
