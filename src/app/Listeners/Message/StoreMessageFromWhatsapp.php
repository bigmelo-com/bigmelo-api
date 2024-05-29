<?php

namespace App\Listeners\Message;

use App\Events\Message\UserMessageStored;
use App\Events\Webhook\WhatsappMessageReceived;
use App\Models\Lead;
use App\Models\Message;
use App\Models\Project;
use App\Models\WhatsappMessage;
use App\Repositories\MessageRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class StoreMessageFromWhatsapp
{
    /**
     * Handle the event.
     */
    public function handle(WhatsappMessageReceived $event): void
    {
        $raw_content = $event->content;
        $message_repository = new MessageRepository();

        try {
            parse_str($raw_content, $content);

            $whatsapp_data = [
                'message_id'            => 0,
                'media_content_type'    => $content['MediaContentType0'] ?? null,
                'sms_message_sid'       => $content['SmsMessageSid'] ?? null,
                'num_media'             => $content['NumMedia'] ?? null,
                'profile_name'          => $content['ProfileName'] ?? null,
                'sms_sid'               => $content['SmsSid'] ?? null,
                'wa_id'                 => $content['WaId'] ?? null,
                'sms_status'            => $content['SmsStatus'] ?? null,
                'to'                    => $content['To'] ?? null,
                'num_segments'          => $content['NumSegments'] ?? null,
                'referral_num_media'    => $content['ReferralNumMedia'] ?? null,
                'message_sid'           => $content['MessageSid'] ?? null,
                'account_sid'           => $content['AccountSid'] ?? null,
                'from'                  => $content['From'] ?? null,
                'media_url'             => $content['MediaUrl0'] ?? null,
                'api_version'           => $content['ApiVersion'] ?? null
            ];

            $message_text = $content['Body'];

            if ($this->isMessageInAudio($content)) {
                $message_text = $this->getMessageFromAudio($whatsapp_data['media_url'], $whatsapp_data['media_content_type']);
            }

            $project = Project::where(
                'phone_number',
                str_replace('whatsapp:', '', $whatsapp_data['to'])
            )->first();

            $from_number = str_replace('whatsapp:', '', $content['From']);

            $lead = $project->leads->where('full_phone_number', $from_number)->first();

            if (!$lead) {
                $lead = Lead::create([
                    'country_code'          => substr($from_number, 0, -10),
                    'phone_number'          => substr($from_number, -10),
                    'full_phone_number'     => $from_number,
                ]);

                $lead->projects()->attach($project);
                $lead->remaining_messages = config('not_registered_user_message_limit' ,5);
                $lead->save();
            }

            $message = $message_repository->storeMessage(
                lead_id: $lead->id,
                project_id: $project->id,
                content: $message_text,
                source: 'WhatsApp'
            );

            $whatsapp_data['message_id'] = $message->id;
            WhatsappMessage::create($whatsapp_data);

            event(new UserMessageStored($message));

            Log::info(
                'Message from whatsapp stored, ' .
                'message_id: ' . $message->id
            );

        } catch (\Throwable $e) {
            Log::error(
                'StoreMessageFromWhatsapp: Internal error, ' .
                'whatsapp_message: ' . $raw_content . ', ' .
                'error: ' . $e->getMessage()
            );
        }
    }

    /**
     * Check if the content has the message as audio
     *
     * @param array $content
     *
     * @return bool
     */
    private function isMessageInAudio(array $content): bool
    {
        if (!empty($content['Body'])) {
            return false;
        }

        $content_type = $content['MediaContentType0'] ?? '';

        if (strpos($content_type, 'audio/') === 0) {
            return true;
        }

        return false;
    }

    /**
     * Get text from an audio file
     *
     * @param string $audio_url
     * @param string $content_type
     *
     * @return string
     */
    private function getMessageFromAudio(string $audio_url, string $content_type): string
    {
        $audio_content = file_get_contents($audio_url);

        $filename = 'audio_' . time() . '.' . explode('/', $content_type)[1];

        Storage::disk('public')->put($filename, $audio_content);

        return 'ok';
    }

}
