<?php

namespace App\Exceptions\ChatGPT;

use Exception;

/**
 * Class ChatGPTClientCouldNotTranscribeMessageException
 *
 * Error trying to transcribe a message from an audio file using the OpenAi client
 */
class ChatGPTClientCouldNotTranscribeMessageException extends Exception {}
