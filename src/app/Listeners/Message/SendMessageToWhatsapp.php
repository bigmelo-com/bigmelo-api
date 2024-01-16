<?php

namespace App\Listeners\Message;

use App\Classes\Twilio\TwilioClient;
use App\Models\Lead;
use App\Events\Message\BigmeloMessageStored;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendMessageToWhatsapp implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The number of times the queued listener may be attempted.
     *
     * @var int
     */
    public int $tries = 5;

    /**
     * The number of seconds before the job should be retried.
     *
     * @var int
     */
    public int $retryAfter = 10;

    /**
     * Handle the event.
     */
    public function handle(BigmeloMessageStored $event): void
    {
        $bigmelo_message = $event->message;
        $lead = $bigmelo_message->lead;
        $project = $bigmelo_message->project;

        try {
            $twilio_client = new TwilioClient($project->phone_number);
            $twilio_client->sendMessageToWhatsapp($lead->full_phone_number, $bigmelo_message->content);

            $lead->remaining_messages = $lead->remaining_messages > 0 ? $lead->remaining_messages - 1 : $lead->remaining_messages;
            $lead->save();

            Log::info(
                'Message sent to whatsapp, ' .
                'from: ' . $project->phone_number . ', ' .
                'to: ' . $lead->full_phone_number . ', ' .
                'message_id: ' . $bigmelo_message->id . ', ' .
                'remaning_messages: ' . $lead->remaining_messages 
            );

        } catch (\Throwable $e) {
            Log::error(
                'SendMessageToWhatsapp: Internal error, ' .
                'from: ' . $project->phone_number . ', ' .
                'to: ' . $lead->full_phone_number . ', ' .
                'message_id: ' . $bigmelo_message->id . ', ' .
                'error: ' . $e->getMessage()
            );
        }
    }
}
