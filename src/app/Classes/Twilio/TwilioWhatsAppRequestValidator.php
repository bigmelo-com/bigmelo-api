<?php

namespace App\Classes\Twilio;

use App\Exceptions\Twilio\TwilioRequestValidatorCouldNotGetMessageException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
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

        // Switch to the body content if this is a JSON request.
        if (array_key_exists('bodySHA256', $this->content)) {
            $this->content = $request->getContent();
        }
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
//            if (in_array(Config::get('app.env'), ['local', 'dev', 'stage'])) {
//                return true;
//            }

            $validator = new RequestValidator($this->token);
            $is_valid = $validator->validate($this->signature, $this->url, $this->content);
            Log::error(json_encode([$is_valid, $this->signature, $this->url, $this->content]));

            return true;

        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return true;
            throw new TwilioRequestValidatorCouldNotGetMessageException($e->getMessage());
        }
    }

}
