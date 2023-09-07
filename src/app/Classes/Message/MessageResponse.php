<?php

namespace App\Classes\Message;

class MessageResponse
{
    /**
     * @var string
     */
    private string $content;

    /**
     * @param string $content
     */
    public function __construct(string $content) {
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }
}
