<?php

namespace App\Classes\Twilio;

use App\Exceptions\Twilio\TwilioRequestValidatorCouldNotGetMessageException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
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
        $this->token = config('bigmelo.twilio.token');

        $this->signature = $request->header('X-Twilio-Signature');

        $this->url = $request->fullUrl();

        $this->content = $request->toArray();
    }

    /**
     * Validate the request from WhatsApp Twilio
     *
     * @return bool
     *
     * @throws TwilioRequestValidatorCouldNotGetMessageException
     */
    public function isValidRequest(): bool
    {
        try {
            if (in_array(Config::get('app.env'), ['local', 'dev', 'stage'])) {
                return true;
            }

            $validator = new RequestValidator($this->token);

            return $validator->validate($this->signature, $this->url, $this->content);

        } catch (\Throwable $e) {
            throw new TwilioRequestValidatorCouldNotGetMessageException($e->getMessage());
        }
    }

}
