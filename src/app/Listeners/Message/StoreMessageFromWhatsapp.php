<?php

namespace App\Listeners\Message;

use App\Events\Message\UserMessageStored;
use App\Events\Webhook\WhatsappMessageReceived;
use App\Models\Lead;
use App\Models\Message;
use App\Models\Project;
use App\Models\WhatsappMessage;
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

        try {
            parse_str($raw_content, $content);

            $message_text = $content['Body'];
            $from_number = str_replace('whatsapp:', '', $content['From']);
            $lead = Lead::where('full_phone_number', $from_number)->first();

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

            $project = Project::where(
                'phone_number',
                str_replace('whatsapp:', '', $whatsapp_data['to'])
            )->first();

            if (!$lead) {
                $message = Message::create([
                    'lead_id'    => 0,
                    'project_id' => $project->id,
                    'content'    => $message_text,
                    'source'     => 'WhatsApp'
                ]);

                $whatsapp_data['message_id'] = $message->id;
                WhatsappMessage::create($whatsapp_data);

                Log::info(
                    'Message from a unknown whatsapp number stored, ' .
                    'message_id: ' . $message->id . ', ' .
                    'phone_number: ' . $from_number
                );
            }

            $message = Message::create([
                'lead_id'    => $lead->id,
                'project_id' => $project->id,
                'content'    => $message_text,
                'source'     => 'WhatsApp'
            ]);

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
}
