<?php

namespace App\Classes\Twilio;

use App\Exceptions\Twilio\TwilioClientCouldNotSendAMessageToWhatsappException;
use App\Exceptions\Twilio\TwilioClientCouldNotSendASmsMessageException;
use Twilio\Rest\Client;

class TwilioClient
{
    /**
     * @var Client
     */
    private Client $client;

    /**
     * @var string
     */
    private string $twilio_phone_number;

    /**
     * @throws \Twilio\Exceptions\ConfigurationException
     */
    public function __construct(string $twilio_phone_number)
    {
        $sid = config('bigmelo.twilio.sid');
        $token = config('bigmelo.twilio.token');
        $this->client = new Client($sid, $token);

        $this->twilio_phone_number = $twilio_phone_number;
    }

    /**
     * Send a message to Whatsapp
     *
     * @param string $phone_number
     * @param string $message
     *
     * @return void
     *
     * @throws TwilioClientCouldNotSendAMessageToWhatsappException
     */
    public function sendMessageToWhatsapp(string $phone_number, string $message): void
    {
        try {
            $this->client->messages->create(
                "whatsapp:$phone_number",
                [
                    'from' => "whatsapp:$this->twilio_phone_number",
                    'body' => $message
                ]
            );

        } catch (\Throwable $e) {
            throw new TwilioClientCouldNotSendAMessageToWhatsappException(
                'Error Twilio Client, ' .
                'error: ' . $e->getMessage()
            );
        }
    }
}
