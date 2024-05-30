<?php

namespace App\Classes\Twilio;

use App\Exceptions\Twilio\TwilioClientCouldNotGetFileContentException;
use App\Exceptions\Twilio\TwilioClientCouldNotSendAMessageToWhatsappException;
use Illuminate\Support\Facades\Http;
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
    private string $sid;

    /**
     * @var string
     */
    private string $token;

    /**
     * @var string
     */
    private string $twilio_phone_number;

    /**
     *
     * @param string $twilio_phone_number
     *
     * @throws \Twilio\Exceptions\ConfigurationException
     */
    public function __construct(string $twilio_phone_number)
    {
        $this->sid = config('bigmelo.twilio.sid');
        $this->token = config('bigmelo.twilio.token');
        $this->client = new Client($this->sid, $this->token);

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

    /**
     * Get the content from a file in twilio
     *
     * Used mostly when an audio file is sent using Whatsapp.
     *
     * @param string $file_url
     * @return string
     *
     * @throws TwilioClientCouldNotGetFileContentException
     */
    public function getFileContent(string $file_url): string
    {
        try {
            $response = Http::withBasicAuth($this->sid, $this->token)->get($file_url);

            return $response->body();

        } catch (\Throwable $e) {
            throw new TwilioClientCouldNotGetFileContentException(
                'Error Twilio Client Getting File Content, ' .
                'error: ' . $e->getMessage()
            );
        }
    }
}
