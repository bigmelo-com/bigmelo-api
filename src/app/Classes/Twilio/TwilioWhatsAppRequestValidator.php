<?php

namespace App\Classes\Twilio;

use App\Exceptions\Twilio\TwilioClientCouldNotSendAMessageToWhatsappException;
use Illuminate\Http\Request;
use Twilio\Security\RequestValidator;

class TwilioWhatsAppRequestValidator
{
    /**
     * @var string
     */
    private string $token;

    /**
     * @var string
     */
    private string $signature;

    /**
     * @var string
     */
    private string $url;

    /**
     * @var array
     */
    private $content;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $headers = $request->header();

        $this->token = config('bigmelo.twilio.token');

        $this->signature = $headers['x-twilio-signature'][0];

        $this->url = $request->fullUrl();

        $raw_content = $request->getContent();
        parse_str($raw_content, $this->content);
    }

    /**
     * Validate the request from WhatsApp Twilio
     *
     * @return bool
     */
    public function isValidRequest(): bool
    {
        $validator = new RequestValidator($this->token);

        return $validator->validate($this->signature, $this->url, $this->content);
    }

}
