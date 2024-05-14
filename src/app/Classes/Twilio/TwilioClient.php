<?php

namespace App\Classes\Twilio;

use App\Exceptions\Twilio\TwilioClientCouldNotSendAMessageToWhatsappException;
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

    /**
     * Send a message template to Whatsapp
     *
     * @param string $phone_number
     * @param string $template_sid
     * @param array $variables
     *
     * @return void
     *
     * @throws TwilioClientCouldNotSendAMessageToWhatsappException
     */
    public function sendMessageTemplateToWhatsapp(string $phone_number, string $template_sid, array $variables = []): void
    {
        try {
            $this->client->messages->create(
                "whatsapp:$phone_number",
                [
                    'from' => "whatsapp:$this->twilio_phone_number",
                    'contentSid' => $template_sid,
                    "contentVariables" => json_encode($variables)
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
